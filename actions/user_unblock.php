<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_GET['csrf'] ?? '');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../pages/users.php");
    exit;
}

db_query("UPDATE users SET is_blocked_until=NULL WHERE id=?", [$id], "i")->close();

record_audit($_SESSION['user']['id'], null, 'user_unblock', "Unblocked user_id=$id");

$_SESSION['message'] = "User unblocked successfully.";
$_SESSION['message_type'] = "success";

header("Location: ../pages/users.php");
exit;
