<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

header('Content-Type: application/json');

// Join with users + devices to show username and device name
$r = db_query("
    SELECT a.id, a.action, a.details, a.ip_address, a.created_at,
           u.username AS user_name,
           d.name AS device_name
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN devices d ON a.device_id = d.id
    ORDER BY a.id DESC
    LIMIT 300
")->get_result();

$data = [];
while ($row = $r->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
