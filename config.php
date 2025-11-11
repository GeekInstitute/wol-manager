<?php
// =====================
//  CONFIGURATION
// =====================
define('DB_HOST', 'localhost');
define('DB_USER', 'wol_user');
define('DB_PASS', 'password');
define('DB_NAME', 'wol_manager');

define('APP_NAME', 'Wake-on-LAN Manager');
define('BASE_PATH', '/wol-manager');   // Change to "" if project is in /var/www/html/

// Session settings
define('SESSION_NAME', 'wolmgr_sess');
define('SESSION_SECURE', false); // true only if HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Strict');

date_default_timezone_set('Asia/Kolkata');

// =====================
//  DATABASE CONNECTION
// =====================

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_errno) {
    die("DB Connection Error: " . $mysqli->connect_error);
}

// =====================
//  DB QUERY HELPER
// =====================
//  Usage: db_query("SELECT * FROM table WHERE id=?", [$id], "i");
function db_query($sql, $params = [], $types = "")
{
    global $mysqli;

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("SQL Prepare Error: " . $mysqli->error . " | SQL: " . $sql);
    }

    if (!empty($params)) {
        if ($types === "") {
            $types = str_repeat("s", count($params)); // default all params to string
        }
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt;
}

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => SESSION_SAMESITE
    ]);

    session_name(SESSION_NAME);
    session_start();
}

function db() {
    global $mysqli;
    return $mysqli;
}