<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/includes/audit.php';

csrf_token();
$user = require_login();

// convenience helpers
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function ensure_admin() {
  if (!is_admin()) { http_response_code(403); exit('Not allowed'); }
}

function can_manage_type($type) {
  if (!isset($_SESSION['user'])) return false;
  if ($type === 'server') return (bool)$_SESSION['user']['can_manage_servers'];
  if ($type === 'computer') return (bool)$_SESSION['user']['can_manage_computers'];
  return false;
}
