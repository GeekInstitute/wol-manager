<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();

//CSRF check
csrf_check_or_die($_GET['csrf'] ?? '');

$user_id = (int)($_GET['id'] ?? 0);

if (!$user_id) {
    $_SESSION['message'] = "Invalid user ID.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . BASE_PATH . "/pages/users.php");
    exit;
}

// Don't allow deleting the currently logged-in admin
if ($user_id === $_SESSION['user']['id']) {
    $_SESSION['message'] = "You cannot delete your own account.";
    $_SESSION['message_type'] = "warning";
    header("Location: " . BASE_PATH . "/pages/users.php");
    exit;
}

// Fetch username before delete (so we can log it)
$stmt = db_query("SELECT username FROM users WHERE id=?", [$user_id], "i");
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['message'] = "User does not exist.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . BASE_PATH . "/pages/users.php");
    exit;
}

// Delete user
db_query("DELETE FROM users WHERE id=?", [$user_id], "i")->close();

// Log audit (NO user_id in the DB; use current admin ID as the actor)
record_audit(
    $_SESSION['user']['id'],     // who performed delete
    null,                        // no device involved
    "user_delete",
    "Deleted user: {$user['username']} (ID: $user_id)"
);

$_SESSION['message'] = "User <strong>{$user['username']}</strong> deleted successfully.";
$_SESSION['message_type'] = "success";

header("Location: " . BASE_PATH . "/pages/users.php");
exit;
