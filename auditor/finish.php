<?php
require_once __DIR__.'/includes/config.php'; require_auth();
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;} verify_csrf();
if(!attempt_is_active()){header('Location:index.php?finished=1');exit;}
$aid=current_attempt_id();
$q=db()->prepare("UPDATE lab_attempts SET status='finalized',finalized_at=NOW(),final_reason='student_finished' WHERE id=? AND status='active'"); $q->execute([$aid]);
log_activity('LAB_FINALIZED','Laboratorio finalizado','El estudiante confirmó el cierre definitivo del intento.');
$result=telegram_send_log($aid);
$q=db()->prepare("UPDATE lab_attempts SET telegram_status=?,telegram_detail=? WHERE id=?"); $q->execute([$result['status'],substr($result['detail'],0,500),$aid]);
log_activity('TELEGRAM_LOG','Resultado del envío automático a Telegram',$result['detail'],['status'=>$result['status']]);
header('Location:index.php?finalized=1&telegram='.urlencode($result['status']));
