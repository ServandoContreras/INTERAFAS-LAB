<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../includes/scenario.php';
$s=scenario_state();
$eventId=(int)(db()->query("SELECT COALESCE(MAX(id),0) FROM scenario_events")->fetchColumn());
echo json_encode(['phase'=>(int)$s['phase'],'phase_name'=>$s['phase_name'],'updated_at'=>$s['updated_at'],'event_id'=>$eventId,'news_visible'=>visible_news_count(),'social_visible'=>visible_social_count()], JSON_UNESCAPED_UNICODE);
