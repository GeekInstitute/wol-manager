<?php
require_once __DIR__ . '/../middleware.php';
require_login();

csrf_check_or_die($_POST['csrf'] ?? '');

$username = trim($_POST['username']);
$password = trim($_POST['password']);
$user_id  = $_SESSION['user']['id'];

//Update only username if password is empty
if ($password === "") {
    db_query("UPDATE users SET username=? WHERE id=?", [$username, $user_id], "si")->close();

    $_SESSION['message'] = "Profile updated successfully.";
    $_SESSION['message_type'] = "success";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    db_query("UPDATE users SET username=?, password_hash=? WHERE id=?", [$username, $hash, $user_id], "ssi")->close();

    $_SESSION['message'] = "Password updated successfully.";
    $_SESSION['message_type'] = "success";
}

// Update session username so navbar reflects change
$_SESSION['user']['username'] = $username;

header("Location: ../pages/profile.php");
exit;
