<?php
require_once __DIR__ . '/../middleware.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$cacheFile = sys_get_temp_dir() . "/wol_status_cache.json";
$cacheTTL  = 10; // seconds

//Use cached data if fresh (to reduce ping load)
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

$user_id = $_SESSION['user']['id'];

if (is_admin()) {
    $devices = db_query("SELECT id, ip, ssh_user, ssh_pass FROM devices ORDER BY id")->get_result();
} else {
    $devices = db_query(
        "SELECT d.id, d.ip, d.ssh_user, d.ssh_pass
         FROM devices d
         JOIN user_devices ud ON ud.device_id = d.id
         WHERE ud.user_id = ?
         ORDER BY d.id",
        [$user_id], "i"
    )->get_result();
}

$data = [];
while ($d = $devices->fetch_assoc()) {

    // Use your optimized function
    $online = ping_host($d['ip'], 800);

    //Lightweight SSH port test (non-blocking)
    $sshOpen = false;
    $connection = @fsockopen($d['ip'], 22, $errno, $errstr, 0.5);
    if ($connection) {
        $sshOpen = true;
        fclose($connection);
    }

    $data[] = [
        'id'      => $d['id'],
        'online'  => $online,
        'ssh'     => $sshOpen
    ];
}

//Save to cache to prevent repeated pings for next 10s
file_put_contents($cacheFile, json_encode($data));

echo json_encode($data);
