<?php
require_once __DIR__ . '/../middleware.php';
require_login();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit.php';

//CSRF check
csrf_check_or_die($_GET['csrf'] ?? '');

$device_id = (int)($_GET['id'] ?? 0);

//Validate device
$res = db_query("SELECT * FROM devices WHERE id=?", [$device_id], "i")->get_result();
$device = $res->fetch_assoc();

if (!$device) {
    $_SESSION['message'] = "Device not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/devices.php");
    exit;
}

//Check permission (non-admin users can only control assigned devices)
if (!is_admin()) {
    $uid = $_SESSION['user']['id'];

    $perm = db_query(
        "SELECT can_wake FROM user_devices WHERE device_id=? AND user_id=?",
        [$device_id, $uid],
        "ii"
    )->get_result()->fetch_assoc();

    if (!$perm || empty($perm['can_wake'])) {
        $_SESSION['message'] = "You do not have permission to wake this device.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../pages/devices.php");
        exit;
    }
}

//Perform Wake-on-LAN
$ok = wol_send_magic_packet($device['mac']);

//Audit log
record_audit(
    user_id: $_SESSION['user']['id'],
    device_id: $device_id,
    action: "device_wake",
    details: $ok ? "Wake OK" : "Wake FAILED"
);

//UI message
$_SESSION['message'] = $ok
    ? "Wake signal sent to <strong>{$device['name']}</strong>"
    : "Wake failed for <strong>{$device['name']}</strong>";

$_SESSION['message_type'] = $ok ? "success" : "warning";

//Redirect back where user came from
header("Location: ../pages/" . $device['type'] . "s.php");
exit;
