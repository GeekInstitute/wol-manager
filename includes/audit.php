<?php

require_once __DIR__ . '/../config.php';

function record_audit($user_id = null, $device_id = null, $action = "", $details = null)
{
  
    if (empty($user_id) || !is_numeric($user_id) || $user_id <= 0) {
        $user_id = NULL;
    }

    db_query(
        "INSERT INTO audit_logs (user_id, device_id, action, details, ip_address)
         VALUES (?, ?, ?, ?, ?)",
        [
            $user_id,
            $device_id ?: NULL,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? "unknown",
        ],
        "iisss"
    );
}
