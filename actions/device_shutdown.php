<?php
require_once __DIR__ . '/../middleware.php';
require_login();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit.php';     

//CSRF protection
csrf_check_or_die($_GET['csrf'] ?? '');

$device_id = (int)($_GET['id'] ?? 0);

//Validate device exists
$res = db_query("SELECT * FROM devices WHERE id=?", [$device_id], "i")->get_result();
$device = $res->fetch_assoc();

if (!$device) {
    $_SESSION['message'] = "Device not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/devices.php");
    exit;
}

/*Permission check for non-admin users */
if (!is_admin()) {
    $uid = $_SESSION['user']['id'];

    $perm = db_query(
        "SELECT can_shutdown FROM user_devices WHERE device_id=? AND user_id=?",
        [$device_id, $uid],
        "ii"
    )->get_result()->fetch_assoc();

    if (!$perm || empty($perm['can_shutdown'])) {
        $_SESSION['message'] = "You do not have permission to shutdown this device.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../pages/devices.php");
        exit;
    }
}

//Perform SSH shutdown
[$ok, $output] = ssh_shutdown(
    $device['os'],
    $device['ip'],
    $device['ssh_user'],
    $device['ssh_pass']
);

//Audit log
record_audit(
    user_id: $_SESSION['user']['id'],
    device_id: $device_id,
    action: "device_shutdown",
    details: $ok ? "Shutdown OK" : "Shutdown FAILED: $output"
);

//UI message
$_SESSION['message'] = $ok
    ? "Shutdown command sent to <strong>{$device['name']}</strong>"
    : "Shutdown FAILED for <strong>{$device['name']}</strong>";

$_SESSION['message_type'] = $ok ? "warning" : "danger";

//Redirect back to the proper page (computers.php or servers.php)
header("Location: ../pages/{$device['type']}s.php");
exit;
