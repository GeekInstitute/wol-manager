<?php require_once __DIR__ . '/../middleware.php'; require_once __DIR__ . '/../includes/functions.php';
csrf_check_or_die($_GET['csrf']??'');
$mode=$_GET['all']??'';
$uid=$_SESSION['user']['id'];
if($mode==='on'){
  $res = is_admin()
    ? db_query("SELECT id,mac FROM devices")->get_result()
    : db_query("SELECT d.id,d.mac FROM devices d JOIN user_devices ud ON ud.device_id=d.id WHERE ud.user_id=? AND ud.can_wake=1",[$uid],'i')->get_result();
  while($r=$res->fetch_assoc()){ wol_send_magic_packet($r['mac']); record_audit($uid,$r['id'],'wake_all','via toggle'); }
}
if($mode==='off'){
  $res = is_admin()
    ? db_query("SELECT id,os,ip,ssh_user,ssh_pass FROM devices")->get_result()
    : db_query("SELECT d.id,d.os,d.ip,d.ssh_user,d.ssh_pass FROM devices d JOIN user_devices ud ON ud.device_id=d.id WHERE ud.user_id=? AND ud.can_shutdown=1",[$uid],'i')->get_result();
  while($r=$res->fetch_assoc()){ [$ok,$out]=ssh_shutdown($r['os'],$r['ip'],$r['ssh_user'],$r['ssh_pass']); record_audit($uid,$r['id'],'shutdown_all',$ok?'OK':'FAIL: '.$out); }
}
header('Location: '.BASE_PATH.'/pages/dashboard.php');
