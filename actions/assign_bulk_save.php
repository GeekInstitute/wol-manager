<?php
require_once __DIR__ . '/../middleware.php';
require_login();
require_once __DIR__ . '/../includes/audit.php';  

csrf_check_or_die($_POST['csrf'] ?? '');

$user_id = intval($_POST['user_id'] ?? 0);
$devices = $_POST['devices'] ?? [];

// safety check
if (!$user_id) {
    $_SESSION['message'] = "Invalid user selection.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/quick_assign.php");
    exit;
}

// Remove previous assignments
db_query("DELETE FROM user_devices WHERE user_id=?", [$user_id], "i")->close();

// Apply new assignments
foreach ($devices as $device_id) {
    db_query(
        "INSERT INTO user_devices (user_id, device_id, can_wake, can_shutdown, can_console)
         VALUES (?, ?, 1, 1, 1)",
        [$user_id, intval($device_id)],
        "ii"
    )->close();
}

// Audit logging
record_audit(
    $_SESSION['user']['id'],    // Admin who performed this action
    null,                       // Not a single device action
    "bulk_assign",
    "Assigned devices: " . implode(",", $devices) . " to user=$user_id"
);

// Success message
$_SESSION['message'] = "Device permissions updated successfully.";
$_SESSION['message_type'] = "success";

header("Location: ../pages/quick_assign.php");
exit;
?>
