<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

$user = (int)($_GET['user'] ?? 0);
$deviceList = $_GET['devices'] ?? "";

if (!$user || !$deviceList) {
    echo json_encode(['can_wake' => 0, 'can_shutdown' => 0, 'can_console' => 0]);
    exit;
}

$ids = implode(",", array_map("intval", explode(",", $deviceList)));

$res = db_query(
    "SELECT MAX(can_wake) AS can_wake,
            MAX(can_shutdown) AS can_shutdown,
            MAX(can_console) AS can_console
     FROM user_devices WHERE user_id=? AND device_id IN ($ids)",
    [$user],
    "i"
)->get_result();

echo json_encode($res->fetch_assoc());
