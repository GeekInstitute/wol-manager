<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/audit.php";

function start_secure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_PATH ?: '/',
        'secure'   => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => SESSION_SAMESITE
    ]);

    session_start();
}

function get_client_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function is_blocked($username) {
    $stmt = db_query(
        "SELECT COUNT(*) c FROM login_attempts
         WHERE username=? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)",
        [$username]
    );
    $c = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    $stmt->close();

    if ($c >= 5) return true;

    $stmt = db_query("SELECT is_blocked_until FROM users WHERE username=?", [$username]);
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return ($row && $row['is_blocked_until'] && strtotime($row['is_blocked_until']) > time());
}

function add_failed_attempt($username) {
    db_query(
        "INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)",
        [$username, get_client_ip()]
    )->close();

    $stmt = db_query(
        "SELECT COUNT(*) c FROM login_attempts
         WHERE username=? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)",
        [$username]
    );
    $c = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    $stmt->close();

    if ($c >= 5) {
        db_query(
            "UPDATE users SET is_blocked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
             WHERE username=?",
            [$username]
        )->close();
    }
}

function login($username, $password) {
    if (is_blocked($username)) {
        record_audit("login_blocked", null, "username=$username");
        return [false, 'Temporarily blocked (30 min).'];
    }

    $stmt = db_query("SELECT * FROM users WHERE username=?", [$username]);
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password_hash'])) {
        db_query("DELETE FROM login_attempts WHERE username=?", [$username])->close();
        db_query("UPDATE users SET is_blocked_until=NULL WHERE id=?", [$user['id']], 'i')->close();

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'                  => (int)$user['id'],
            'username'            => $user['username'],
            'role'                => $user['role'],
            'can_manage_servers'  => (int)$user['can_manage_servers'],
            'can_manage_computers'=> (int)$user['can_manage_computers'],
        ];

        record_audit("login_success", null, "username=$username");
        return [true, null];
    }

    add_failed_attempt($username);
    record_audit("login_failed", null, "username=$username");
    return [false, 'Invalid credentials'];
}

function require_login() {
    start_secure_session();
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_PATH . '/login.php');
        exit;
    }
    return $_SESSION['user'];
}

function is_admin() {
    return !empty($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}
