<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

header("Content-Type: application/json");

$user_id = $_GET['user_id'] ?? 0;
if (!$user_id) exit(json_encode([]));

$res = db_query("
    SELECT device_id, can_wake, can_shutdown, can_console
    FROM user_devices WHERE user_id = ?", [$user_id], "i")->get_result();

$data = [];
while ($r = $res->fetch_assoc()) {
    $data[$r["device_id"]] = [
        "wake"     => (int)$r["can_wake"],
        "shutdown" => (int)$r["can_shutdown"],
        "console"  => (int)$r["can_console"],
    ];
}

echo json_encode($data);
