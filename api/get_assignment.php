<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

$user = (int)($_GET['user'] ?? 0);
$device = (int)($_GET['device'] ?? 0);

$res = db_query(
    "SELECT can_wake, can_shutdown, can_console
     FROM user_devices
     WHERE user_id=? AND device_id=?",
    [$user, $device],
    "ii"
)->get_result();

echo json_encode($res->fetch_assoc() ?: [
    'can_wake' => 0,
    'can_shutdown' => 0,
    'can_console' => 0
]);
