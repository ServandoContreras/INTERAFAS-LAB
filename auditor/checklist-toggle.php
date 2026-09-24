<?php
require_once __DIR__.'/includes/config.php';
require_auth();
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false]);exit;}
verify_csrf();
if(!attempt_is_active()){http_response_code(409);echo json_encode(['ok'=>false,'message'=>'El intento ya fue finalizado.']);exit;}
$flag=max(1,min(20,(int)($_POST['flag_number']??0)));
$item=(int)($_POST['item_index']??-1);
$checked=((string)($_POST['checked']??'0'))==='1' ? 1 : 0;
if($item<0 || $item>10){http_response_code(400);echo json_encode(['ok'=>false]);exit;}
$earned=db()->prepare("SELECT 1 FROM flag_submissions WHERE attempt_id=? AND flag_number=? AND status='accepted' LIMIT 1");
$earned->execute([current_attempt_id(),$flag]);
if($earned->fetchColumn()){$checked=1;}
$q=db()->prepare("INSERT INTO lab_checklist_state(attempt_id,student_id,flag_number,item_index,is_checked,updated_at) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE is_checked=VALUES(is_checked),updated_at=NOW()");
$q->execute([current_attempt_id(),current_student_id(),$flag,$item,$checked]);
log_activity('CHECKLIST_UPDATED','Checklist actualizado','Reto '.sprintf('%02d',$flag).' · comprobación '.($item+1).' · '.($checked?'marcada':'desmarcada'),['flag_number'=>$flag,'item_index'=>$item,'checked'=>(bool)$checked],'auditor','info');
echo json_encode(['ok'=>true,'checked'=>(bool)$checked],JSON_UNESCAPED_UNICODE);
