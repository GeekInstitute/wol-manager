<?php
require_once __DIR__ . '/../middleware.php';
require_login();

// Required includes (order matters)
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit.php';

csrf_check_or_die($_GET['csrf'] ?? '');

$type = strtolower($_GET['type'] ?? '');
$allowed = ['server', 'computer'];

if (!in_array($type, $allowed)) {
    $_SESSION['message'] = "Invalid device type.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/dashboard.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

/* Load devices based on permissions */
if (is_admin()) {
    $res = db_query(
        "SELECT id, mac, ip FROM devices WHERE type=? ORDER BY name ASC",
        [$type],
        "s"
    );
} else {
    $res = db_query(
        "SELECT d.id, d.mac, d.ip
         FROM devices d
         JOIN user_devices ud ON ud.device_id = d.id
         WHERE d.type=? AND ud.user_id=? AND ud.can_wake = 1",
        [$type, $user_id],
        "si"
    );
}

$devices = $res->get_result();

$offlineDevices = [];
$count = 0;

/* Filter: Only wake offline devices */
while ($d = $devices->fetch_assoc()) {

 
    if (!ping_host($d['ip'])) {
        $offlineDevices[] = $d;
    }
}

/* Send WOL packet */
foreach ($offlineDevices as $d) {

    if (wol_send_magic_packet($d['mac'])) {
        $count++;

        record_audit(
            user_id: $user_id,
            device_id: $d['id'],
            action: "wake_all",
            details: "Wake-All executed"
        );
    }
}

$_SESSION['message'] = ($count > 0)
    ? "Wake signal sent to $count offline $type(s)."
    : "No offline $type(s) found.";

$_SESSION['message_type'] = ($count > 0) ? "success" : "warning";

header("Location: ../pages/{$type}s.php");
exit;
