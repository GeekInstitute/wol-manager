<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=audit_logs.csv");

$out = fopen("php://output", "w");

fputcsv($out, ["ID", "User", "Device", "Action", "Details", "IP", "Timestamp"]);

$q = db_query("
    SELECT a.id, u.username, d.name, a.action, a.details, a.ip_address, a.created_at
    FROM audit_logs a
    LEFT JOIN users u   ON u.id = a.user_id
    LEFT JOIN devices d ON d.id = a.device_id
    ORDER BY a.id DESC
")->get_result();

while ($r = $q->fetch_row()) {
    fputcsv($out, $r);
}
exit;
