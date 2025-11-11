<?php
require_once __DIR__ . '/../middleware.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit.php';

csrf_check_or_die($_POST['csrf'] ?? '');

$device_id = intval($_POST['device_id'] ?? 0);
$cmd       = trim($_POST['cmd'] ?? '');
$user_id   = $_SESSION['user']['id'];

/**Fetch auth & verify console permission */
$sql = is_admin()
    ? "SELECT * FROM devices WHERE id=?"
    : "SELECT d.*, ud.can_console
       FROM devices d
       JOIN user_devices ud ON ud.device_id = d.id
       WHERE d.id=? AND ud.user_id=? AND ud.can_console=1";

$stmt = is_admin()
    ? db_query($sql, [$device_id], "i")
    : db_query($sql, [$device_id, $user_id], "ii");

$device = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$device) { exit("Access denied."); }

/**Execute SSH command */
[$ok, $response] = ssh_console_run($device['ip'], $device['ssh_user'], $device['ssh_pass'], $cmd);

/**Log the action */
audit($user_id, $device_id, "console", "cmd=$cmd");

/**Output (visually formatted) */
echo $response ?: "Command executed, no output.";
