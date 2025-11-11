<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_POST['csrf'] ?? '');

require_once __DIR__ . '/../includes/audit.php';

$user_id = (int)($_POST['user_id'] ?? 0);
$device_ids = $_POST['device_ids'] ?? [];

$can_wake      = isset($_POST['can_wake']) ? 1 : 0;
$can_shutdown  = isset($_POST['can_shutdown']) ? 1 : 0;
$can_console   = isset($_POST['can_console']) ? 1 : 0;

// Validate
if (!$user_id) {
    $_SESSION['message'] = "Invalid user selection.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . BASE_PATH . "/pages/assign.php");
    exit;
}

// Remove all previous assignments for the user
db_query("DELETE FROM user_devices WHERE user_id=?", [$user_id], "i")->close();

// Insert only new assignments
foreach ($device_ids as $did) {
    $did = (int)$did;

    db_query(
        "INSERT INTO user_devices (user_id, device_id, can_wake, can_shutdown, can_console)
         VALUES (?, ?, ?, ?, ?)",
        [$user_id, $did, $can_wake, $can_shutdown, $can_console],
        "iiiii"
    )->close();
}

//Audit logging
record_audit(
    $_SESSION['user']['id'],
    null,
    "assign_update",
    "Assigned devices: " . json_encode($device_ids) . " to user $user_id"
);

// Flash message
$_SESSION['message'] = "Permissions updated successfully.";
$_SESSION['message_type'] = "success";

header('Location: ' . BASE_PATH . '/pages/assign.php');
exit;
