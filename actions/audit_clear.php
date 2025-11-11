<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

csrf_check_or_die($_GET['csrf'] ?? "");

db_query("TRUNCATE TABLE audit_logs");

$_SESSION['message'] = "Log cleared successfully";
$_SESSION['message_type'] = "success";

header("Location: ../pages/audit.php");
exit;
