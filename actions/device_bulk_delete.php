<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

csrf_check_or_die($_POST['csrf'] ?? '');
$ids = $_POST['device_ids'] ?? [];

if (empty($ids)) {
    $_SESSION['message'] = "No devices selected for deletion.";
    $_SESSION['message_type'] = "warning";
    header("Location: ../pages/devices.php");
    exit;
}

$idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
$params = array_map('intval', $ids);

db_query("DELETE FROM devices WHERE id IN ($idPlaceholders)", $params, str_repeat('i', count($ids)));

$_SESSION['message'] = count($ids) . " device(s) deleted successfully.";
$_SESSION['message_type'] = "success";
header("Location: ../pages/devices.php");
exit;
