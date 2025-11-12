<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_GET['csrf'] ?? '');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../pages/users.php");
    exit;
}

/* Fetch username of target user */
$userRow = db_query("SELECT username FROM users WHERE id = ?", [$id], "i")->get_result()->fetch_assoc();
if (!$userRow) {
    $_SESSION['message'] = "User not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/users.php");
    exit;
}

$username = $userRow['username'];
$until = date("Y-m-d H:i:s", strtotime("+7 days")); // Block for 7 days

/* Update block time */
db_query("UPDATE users SET is_blocked_until=? WHERE id=?", [$until, $id], "si")->close();

/* Record audit with username instead of ID */
$adminUser = $_SESSION['user']['username'];
record_audit($adminUser, null, 'user_block', "Blocked user '$username' until $until by admin '$adminUser'");

$_SESSION['message'] = "User '$username' blocked successfully until $until.";
$_SESSION['message_type'] = "warning";

header("Location: ../pages/users.php");
exit;
