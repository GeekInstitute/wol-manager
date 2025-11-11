<?php
// Core WOL / SSH / status functions
function wol_send_magic_packet($mac, $broadcast='255.255.255.255', $port=9) {
  $mac = preg_replace('/[^A-Fa-f0-9]/', '', $mac);
  if (strlen($mac) != 12) return false;
  $bin_mac = pack('H12', $mac);
  $packet = str_repeat(chr(0xFF), 6) . str_repeat($bin_mac, 16);
  $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  if ($sock === false) return false;
  socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1);
  $res = @socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, $port);
  socket_close($sock);
  return $res !== false;
}

function ping_host($ip, $timeout_ms=800) {
  $timeout = max(1, (int)ceil($timeout_ms/1000));
  $cmd = sprintf('ping -c 1 -W %d %s 2>&1', $timeout, escapeshellarg($ip));
  exec($cmd, $out, $code);
  return $code === 0;
}

function tcp_port_open($ip, $port=22, $timeout=1.0) {
  $errno = 0; $errstr = '';
  $f = @fsockopen($ip, $port, $errno, $errstr, $timeout);
  if ($f) { fclose($f); return true; }
  return false;
}

function device_status($ip) {
  $reachable = ping_host($ip);
  $ssh = $reachable ? tcp_port_open($ip, 22, 0.6) : false;
  return ['online' => $reachable, 'ssh' => $ssh];
}

function ssh_shutdown($os, $ip, $user, $pass) {
  $user = escapeshellarg($user);
  $pass = escapeshellarg($pass);
  $ip   = escapeshellarg($ip);
  if ($os === 'linux') {
    $remote_cmd = "'sudo shutdown -h now'";
  } else { // windows
    $remote_cmd = "'shutdown /s /t 0'";
  }
  $cmd = "sshpass -p $pass ssh -o StrictHostKeyChecking=no $user@$ip $remote_cmd 2>&1";
  exec($cmd, $out, $code);
  return [$code === 0, implode("\n", $out)];
}

function ssh_console_run($ip, $user, $pass, $cmdline) {
  $user = escapeshellarg($user);
  $pass = escapeshellarg($pass);
  $ip   = escapeshellarg($ip);
  $remote = escapeshellarg($cmdline);
  $cmd = "sshpass -p $pass ssh -o StrictHostKeyChecking=no $user@$ip $remote 2>&1";
  exec($cmd, $out, $code);
  return [$code === 0, implode("\n", $out)];
}
function pingDevice($ip) {
    $ping = shell_exec("ping -c 1 -W 1 $ip 2>/dev/null");
    return (strpos($ping, '1 received') !== false);
}

function checkSSH($ip) {
    $connection = @fsockopen($ip, 22, $errno, $errstr, 1);
    if ($connection) {
        fclose($connection);
        return true;
    }
    return false;
}