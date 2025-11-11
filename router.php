<?php
$view = $_GET['view'] ?? 'dashboard.php';
$path = __DIR__ . '/pages/' . basename($view);
if (!is_file($path)) { http_response_code(404); exit('Not found'); }
require $path;
