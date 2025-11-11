<?php
require_once __DIR__ . '/../middleware.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check_or_die($_POST['csrf']??'');
  $action=$_POST['action']??'';
  if($action==='console_run'){
    $device_id=(int)($_POST['device_id']??0);
    $cmd=$_POST['cmd']??'';
    if (!$cmd) { http_response_code(400); echo "No command"; exit; }
    $uid=$_SESSION['user']['id'];
    if(is_admin()){
      $s=db_query("SELECT * FROM devices WHERE id=?",[$device_id],'i');
    }else{
      $s=db_query("SELECT d.* FROM devices d JOIN user_devices ud ON ud.device_id=d.id WHERE d.id=? AND ud.user_id=? AND ud.can_console=1",[$device_id,$uid],'ii');
    }
    $d=$s->get_result()->fetch_assoc(); $s->close();
    if(!$d){ http_response_code(403); echo "Not allowed"; exit; }
    [$ok,$out]=ssh_console_run($d['ip'],$d['ssh_user'],$d['ssh_pass'],$cmd);
    record_audit($uid,$device_id,'console_exec', "cmd=$cmd status=".($ok?'OK':'FAIL'));
    header('Content-Type: text/plain'); echo $out; exit;
  }
}
http_response_code(400); echo "Bad request";
