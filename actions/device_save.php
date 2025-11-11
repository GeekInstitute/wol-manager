<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../includes/audit.php';

ensure_admin();
csrf_check_or_die($_POST['csrf'] ?? '');

// sanitize input
$id        = (int)($_POST['id'] ?? 0);
$name      = trim($_POST['name'] ?? '');
$type      = trim($_POST['type'] ?? '');
$os        = trim($_POST['os'] ?? '');
$ip        = trim($_POST['ip'] ?? '');
$mac       = trim($_POST['mac'] ?? '');
$ssh_user  = trim($_POST['ssh_user'] ?? '');
$ssh_pass  = trim($_POST['ssh_pass'] ?? '');
$notes     = trim($_POST['notes'] ?? '');

if (!$name || !$ip || !$mac) {
    $_SESSION['message'] = "Required fields missing.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . BASE_PATH . "/pages/device_form.php?id=$id");
    exit;
}

if ($id > 0) {

    //UPDATE DEVICE
    db_query(
        "UPDATE devices SET name=?, type=?, os=?, ip=?, mac=?, ssh_user=?, ssh_pass=?, notes=? WHERE id=?",
        [$name, $type, $os, $ip, $mac, $ssh_user, $ssh_pass, $notes, $id],
        "ssssssssi"
    )->close();

    record_audit("device_update", $id, "Updated device: $name");

} else {

    //INSERT DEVICE
    db_query(
        "INSERT INTO devices (name, type, os, ip, mac, ssh_user, ssh_pass, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$name, $type, $os, $ip, $mac, $ssh_user, $ssh_pass, $notes],
        "ssssssss"
    )->close();

    $newId = db()->insert_id;

    record_audit("device_create", $newId, "Created device: $name");
}

$_SESSION['message'] = "Device saved successfully.";
$_SESSION['message_type'] = "success";

header("Location: " . BASE_PATH . "/pages/devices.php");
exit;
