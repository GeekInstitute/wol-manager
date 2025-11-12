<?php
require_once __DIR__ . '/../middleware.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

//Cache setup (to reduce ping load)
$cacheFile = sys_get_temp_dir() . "/wol_status_cache.json";
$cacheTTL  = 10; // seconds

// Serve cached data if still fresh
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

$user_id = $_SESSION['user']['id'];

//Fetch devices with type
if (is_admin()) {
    $devices = db_query(
        "SELECT id, ip, ssh_user, ssh_pass, type FROM devices ORDER BY id"
    )->get_result();
} else {
    $devices = db_query(
        "SELECT d.id, d.ip, d.ssh_user, d.ssh_pass, d.type
         FROM devices d
         JOIN user_devices ud ON ud.device_id = d.id
         WHERE ud.user_id = ?
         ORDER BY d.id",
        [$user_id],
        "i"
    )->get_result();
}

$data = [];

while ($d = $devices->fetch_assoc()) {
    $ip = $d['ip'];

    //Ping check (optimized)
    $online = ping_host($ip, 800);

    //SSH quick check (non-blocking)
    $sshOpen = false;
    if ($online) {
        $conn = @fsockopen($ip, 22, $errno, $errstr, 0.5);
        if ($conn) {
            $sshOpen = true;
            fclose($conn);
        }
    }

    //Include type for dashboard filters
    $data[] = [
        'id'     => (int)$d['id'],
        'ip'     => $ip,
        'type'   => $d['type'],
        'online' => $online,
        'ssh'    => $sshOpen
    ];
}

//Cache the results
file_put_contents($cacheFile, json_encode($data));

echo json_encode($data);
