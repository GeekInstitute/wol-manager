<?php
/**
 *  reset_admin.php
 *  ------------------------------
 *  Use ONLY when you lose access to admin login.
 *  Reset admin password to a known temporary password.
 *  DELETE THIS FILE AFTER USE!
 */

require_once __DIR__ . '/config.php';


$newPassword = "admin";   // <-- Change this IF you want

$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

// Ensure admin user exists
$stmt = db_query("SELECT id FROM users WHERE role='admin' LIMIT 1");
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    // Create admin if missing
    db_query("INSERT INTO users (username, password_hash, role, can_manage_servers, can_manage_computers)
              VALUES ('admin', ?, 'admin', 1, 1)", [$hashed]);
    echo "<h3>Admin account CREATED successfully!</h3>";
} else {
    // Update existing admin password
    db_query("UPDATE users SET password_hash=? WHERE id=?", [$hashed, $admin['id']], "si");
    echo "<h3>Admin password RESET successfully!</h3>";
}

echo "<p>Login using:</p>
<pre>
Username: admin
Password: $newPassword
</pre>
<hr>
<p style='color:red'>⚠️ DELETE THIS FILE IMMEDIATELY AFTER USE!</p>";
