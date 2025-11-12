<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_POST['csrf'] ?? '');

$device_ids = $_POST['device_ids'] ?? [];

if (empty($device_ids)) {
    $_SESSION['message'] = "No devices selected.";
    $_SESSION['message_type'] = "warning";
    header('Location: ' . BASE_PATH . '/pages/devices.php');
    exit;
}

// Delete selected devices
$placeholders = implode(',', array_fill(0, count($device_ids), '?'));
$types = str_repeat('i', count($device_ids));

db_query("DELETE FROM devices WHERE id IN ($placeholders)", $device_ids, $types)->close();

record_audit($_SESSION['user']['id'], null, 'device_delete_bulk', 'Deleted devices: ' . implode(',', $device_ids));

$_SESSION['message'] = "Selected devices deleted successfully.";
$_SESSION['message_type'] = "success";

header('Location: ' . BASE_PATH . '/pages/devices.php');
exit;
