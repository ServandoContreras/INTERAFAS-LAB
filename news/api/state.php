<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once __DIR__.'/../includes/config.php';
$s=scenario_state();
$sql="SELECT COUNT(*) FROM news_articles WHERE ".article_visibility_sql();
$q=db()->prepare($sql);$q->execute([(int)$s['phase'],(int)$s['phase'],(int)$s['elapsed_seconds']]);
$ev=db()->query("SELECT COALESCE(MAX(id),0) FROM scenario_events")->fetchColumn();
echo json_encode(['phase'=>(int)$s['phase'],'name'=>$s['phase_name'],'count'=>(int)$q->fetchColumn(),'event_id'=>(int)$ev,'last_event'=>$s['last_event_code']??null,'updated_at'=>$s['updated_at']],JSON_UNESCAPED_UNICODE);
