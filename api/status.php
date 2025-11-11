<?php
// DO NOT display errors to JSON consumers
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');


$stmt = db_query("SELECT id, ip FROM devices");


$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$response = [];


foreach ($devices as $row) {
    $response[] = [
        "id"     => $row["id"],
        "online" => pingDevice($row["ip"]),
        "ssh"    => checkSSH($row["ip"]),
    ];
}

echo json_encode($response);
exit;
