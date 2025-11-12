<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
require_once __DIR__ . '/../includes/audit.php';

csrf_check_or_die($_GET['csrf'] ?? '');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../pages/users.php");
    exit;
}

/* Fetch target user's username */
$userRow = db_query("SELECT username FROM users WHERE id = ?", [$id], "i")->get_result()->fetch_assoc();
if (!$userRow) {
    $_SESSION['message'] = "User not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/users.php");
    exit;
}

$username = $userRow['username'];

/* Unblock the user */
db_query("UPDATE users SET is_blocked_until=NULL WHERE id=?", [$id], "i")->close();

/* Record audit with readable usernames */
$adminUser = $_SESSION['user']['username'];
record_audit(
    $adminUser,
    null,
    'user_unblock',
    "Admin '$adminUser' unblocked user '$username'"
);

$_SESSION['message'] = "User '$username' unblocked successfully.";
$_SESSION['message_type'] = "success";

header("Location: ../pages/users.php");
exit;
