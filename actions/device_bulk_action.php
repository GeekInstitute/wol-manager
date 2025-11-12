<?php
require_once __DIR__ . '/../middleware.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit.php';

csrf_check_or_die($_POST['csrf'] ?? '');

$action = $_POST['action'] ?? '';
$device_ids = $_POST['device_ids'] ?? [];
$user_id = $_SESSION['user']['id'];

// Determine where user came from (safe redirect)
$redirect = '../pages/dashboard.php';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    // Only allow redirects within your app
    if (strpos($ref, BASE_PATH) !== false || str_contains($ref, '/pages/')) {
        $redirect = $ref;
    }
}

if (empty($device_ids)) {
    $_SESSION['message'] = "No devices selected.";
    $_SESSION['message_type'] = "warning";
    header("Location: $redirect");
    exit;
}

$count = 0;
$woke = 0;
$shut = 0;

foreach ($device_ids as $id) {
    $stmt = is_admin()
        ? db_query("SELECT * FROM devices WHERE id=?", [$id], "i")
        : db_query("
            SELECT d.* FROM devices d
            JOIN user_devices ud ON ud.device_id = d.id
            WHERE d.id=? AND ud.user_id=?", [$id, $user_id], "ii");

    $device = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$device) continue;

    $ip = $device['ip'];
    $is_online = ping_host($ip);

    // SMART MODE → auto decide based on status
    if ($action === "smart") {
        if ($is_online) {
            // Shutdown online
            list($ok,) = ssh_shutdown($device['os'], $ip, $device['ssh_user'], $device['ssh_pass']);
            if ($ok) {
                $shut++;
                record_audit($_SESSION['user']['username'], $id, "smart_shutdown", "Smart: shutdown executed");
            }
        } else {
            // Wake offline
            if (wol_send_magic_packet($device['mac'])) {
                $woke++;
                record_audit($_SESSION['user']['username'], $id, "smart_wake", "Smart: wake executed");
            }
        }
    }

    // Regular modes
    elseif ($action === "wake" && !$is_online) {
        if (wol_send_magic_packet($device['mac'])) {
            $woke++;
            record_audit($_SESSION['user']['username'], $id, "bulk_wake", "Wake via bulk");
        }
    } elseif ($action === "shutdown" && $is_online) {
        list($ok,) = ssh_shutdown($device['os'], $ip, $device['ssh_user'], $device['ssh_pass']);
        if ($ok) {
            $shut++;
            record_audit($_SESSION['user']['username'], $id, "bulk_shutdown", "Shutdown via bulk");
        }
    }
}

// Prepare feedback
if ($action === "smart") {
    $_SESSION['message'] = "Smart Power executed: $woke wake(s), $shut shutdown(s).";
} else {
    $_SESSION['message'] = ucfirst($action) . " command sent to " . ($woke + $shut) . " devices.";
}

$_SESSION['message_type'] = ($woke + $shut) > 0 ? "success" : "warning";

//Redirect user back to where they came from
header("Location: $redirect");
exit;
