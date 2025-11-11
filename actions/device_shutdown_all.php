<?php
require_once __DIR__ . '/../middleware.php';
require_login();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/audit.php';

csrf_check_or_die($_GET['csrf'] ?? '');

$type = strtolower($_GET['type'] ?? '');
$allowed = ['server', 'computer'];

if (!in_array($type, $allowed)) {
    $_SESSION['message'] = "Invalid device type.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../pages/dashboard.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

/* Admin sees all, users see only assigned shutdown-enabled devices */
if (is_admin()) {
    $res = db_query(
        "SELECT id, ip, ssh_user, ssh_pass, os FROM devices WHERE type=? ORDER BY name ASC",
        [$type],
        "s"
    );
} else {
    $res = db_query(
        "SELECT d.id, d.ip, d.ssh_user, d.ssh_pass, d.os
         FROM devices d
         JOIN user_devices ud ON ud.device_id = d.id
         WHERE d.type=? AND ud.user_id=? AND ud.can_shutdown = 1",
        [$type, $user_id],
        "si"
    );
}

$devices = $res->get_result();

$onlineDevices = [];
$count = 0;

/* Filter: shutdown only online devices */
while ($d = $devices->fetch_assoc()) {


    if (ping_host($d['ip'])) {
        $onlineDevices[] = $d;
    }
}

/* Send shutdown command */
foreach ($onlineDevices as $d) {

    list($ok,) = ssh_shutdown($d['os'], $d['ip'], $d['ssh_user'], $d['ssh_pass']);

    record_audit(
        user_id: $user_id,
        device_id: $d['id'],
        action: "shutdown_all",
        details: $ok ? "Shutdown successful" : "Shutdown failed"
    );

    if ($ok) {
        $count++;
    }
}

$_SESSION['message'] = ($count > 0)
    ? "Shutdown command sent to $count online $type(s)."
    : "No online $type(s) available for shutdown.";

$_SESSION['message_type'] = ($count > 0) ? "info" : "warning";

header("Location: ../pages/{$type}s.php");
exit;
