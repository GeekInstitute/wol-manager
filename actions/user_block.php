<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_GET['csrf'] ?? '');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../pages/users.php");
    exit;
}

$until = date("Y-m-d H:i:s", strtotime("+7 days")); // Block for 7 days
db_query("UPDATE users SET is_blocked_until=? WHERE id=?", [$until, $id], "si")->close();

record_audit($_SESSION['user']['id'], null, 'user_block', "Blocked user_id=$id until $until");

$_SESSION['message'] = "User blocked successfully until $until.";
$_SESSION['message_type'] = "warning";

header("Location: ../pages/users.php");
exit;
