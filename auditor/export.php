<?php
require_once __DIR__.'/includes/config.php'; require_auth();
$format=(string)($_GET['format']??'csv'); $aid=current_attempt_id(); $s=current_student();
log_activity('LOG_EXPORTED','Exportación de bitácora','El estudiante exportó el log del laboratorio.',['format'=>$format]);
if($format==='json'){
  $q=db()->prepare("SELECT * FROM lab_activity WHERE attempt_id=? ORDER BY created_at,id"); $q->execute([$aid]); $activity=$q->fetchAll();
  $q=db()->prepare("SELECT s.*,c.code_name,c.title,c.category,c.difficulty,c.weight FROM flag_submissions s JOIN flag_catalog c ON c.flag_number=s.flag_number WHERE s.attempt_id=? ORDER BY s.created_at,s.id");$q->execute([$aid]);$flags=$q->fetchAll();
  $payload=['student'=>$s,'attempt'=>current_attempt(),'stats'=>attempt_stats($aid),'flags'=>$flags,'activity'=>$activity];
  header('Content-Type: application/json; charset=utf-8'); header('Content-Disposition: attachment; filename="INTERAFAS_'.normalize_matricula((string)$s['matricula']).'_log.json"'); echo json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit;
}
$csv=build_log_csv($aid); header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="INTERAFAS_'.normalize_matricula((string)$s['matricula']).'_log.csv"'); echo "\xEF\xBB\xBF".$csv;
