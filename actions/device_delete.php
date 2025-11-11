<?php
require_once __DIR__ . '/../middleware.php';
ensure_admin();
csrf_check_or_die($_GET['csrf'] ?? '');

$id = (int)($_GET['id'] ?? 0);

if ($id) {

    //Get device name before deletion
    $device = db_query(
        "SELECT name FROM devices WHERE id=?",
        [$id],
        "i"
    )->get_result()->fetch_assoc();

    $deviceName = $device['name'] ?? "Unknown Device";

    //og audit BEFORE deletion (correct parameter order)
    record_audit(
        action: "device_delete",
        device_id: $id,
        details: "Deleted device: {$deviceName}"
    );

    //Delete the device
    db_query("DELETE FROM devices WHERE id=?", [$id], "i")->close();
}

header("Location: " . BASE_PATH . "/pages/devices.php");
exit;
