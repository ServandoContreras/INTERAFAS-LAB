<?php
require_once __DIR__.'/includes/config.php'; require_active_attempt();
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:index.php');exit;} verify_csrf();
$action=(string)($_POST['action']??'');
if($action==='recover'){
  $aid=current_attempt_id();
  $q=db()->prepare("INSERT IGNORE INTO scenario_events(attempt_id,event_code,source,detail) VALUES(?,'RECOVERY_STARTED','auditor-portal','Recuperación iniciada desde Portal del Auditor')"); $q->execute([$aid]);
  log_activity('RECOVERY_STARTED','Recuperación iniciada','Se activó la fase de recuperación. Las banderas y la bitácora se conservaron íntegramente.');
}
header('Location:index.php');
