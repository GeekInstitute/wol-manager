<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_POST['csrf'] ?? '');

$id       = (int)($_POST['id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$role     = $_POST['role'] ?? 'user';
$canServers   = isset($_POST['can_manage_servers']) ? 1 : 0;
$canComputers = isset($_POST['can_manage_computers']) ? 1 : 0;

// Basic validation
if ($username === '') {
    $_SESSION['message'] = "Username cannot be empty.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/users.php");
    exit;
}

/* ==========================================
   INSERT (new user)
========================================== */
if ($id === 0) {

    $password = $_POST['password'] ?? '';
    if ($password === '') {
        $_SESSION['message'] = "Password required for new user.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../pages/user_form.php");
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    db_query(
        "INSERT INTO users (username, password_hash, role, can_manage_servers, can_manage_computers)
         VALUES (?, ?, ?, ?, ?)",
        [$username, $hash, $role, $canServers, $canComputers],
        "sssii"
    );

    $newId = db()->insert_id;
    record_audit($_SESSION['user']['id'], null, "user_create", "Created user: $username");

    $_SESSION['message'] = "User created successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: ../pages/users.php");
    exit;
}


/* ==========================================
   UPDATE (existing user)
========================================== */

$updateFields = "UPDATE users SET username=?, role=?, can_manage_servers=?, can_manage_computers=?";
$params = [$username, $role, $canServers, $canComputers];
$types  = "ssii";

// If password field was filled → update password too
if (!empty($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $updateFields .= ", password_hash=?";
    $params[] = $hash;
    $types .= "s";
}

$updateFields .= " WHERE id=?";
$params[] = $id;
$types .= "i";

db_query($updateFields, $params, $types);

record_audit($_SESSION['user']['id'], null, "user_edit", "Updated user: $username");

$_SESSION['message'] = "User updated successfully!";
$_SESSION['message_type'] = "success";
header("Location: ../pages/users.php");
exit;
