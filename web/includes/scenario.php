<?php
require_once __DIR__.'/config.php';
function scenario_state(): array {
    $s = db()->query("SELECT *, TIMESTAMPDIFF(SECOND,phase_started_at,NOW()) AS elapsed_seconds FROM scenario_state WHERE id=1")->fetch();
    return $s ?: ['phase'=>0,'phase_name'=>'Operación normal','elapsed_seconds'=>0,'updated_at'=>''];
}
function scenario_event(string $code, ?int $flag=null, string $source='web', ?string $detail=null): void {
    $q=db()->prepare("INSERT IGNORE INTO scenario_events (event_code,flag_number,source,detail) VALUES (?,?,?,?)");
    $q->execute([$code,$flag,$source,$detail]);
}
function visible_news_count(): int {
    $s=scenario_state();
    $q=db()->prepare("SELECT COUNT(*) FROM news_articles WHERE ((event_required IS NULL AND (phase_required < ? OR (phase_required=? AND delay_seconds<=?))) OR (event_required IS NOT NULL AND EXISTS (SELECT 1 FROM scenario_events se WHERE se.event_code=news_articles.event_required)))");
    $q->execute([(int)$s['phase'],(int)$s['phase'],(int)$s['elapsed_seconds']]);
    return (int)$q->fetchColumn();
}
function visible_social_count(): int {
    $s=scenario_state();
    $q=db()->prepare("SELECT COUNT(*) FROM scenario_social_posts WHERE ((event_required IS NULL AND (phase_required < ? OR (phase_required=? AND delay_seconds<=?))) OR (event_required IS NOT NULL AND EXISTS (SELECT 1 FROM scenario_events se WHERE se.event_code=scenario_social_posts.event_required)))");
    $q->execute([(int)$s['phase'],(int)$s['phase'],(int)$s['elapsed_seconds']]);
    return (int)$q->fetchColumn();
}


function lab_attempt_context_any(): ?array {
    $token=(string)($_COOKIE['INTERAFAS_LAB_TOKEN']??''); if($token===''||strlen($token)<32)return null;
    try{$q=db()->prepare("SELECT a.id attempt_id,a.student_id,a.status,s.matricula FROM lab_attempts a JOIN lab_students s ON s.id=a.student_id WHERE a.attempt_token=? ORDER BY a.id DESC LIMIT 1");$q->execute([$token]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function current_attempt_flag_count(): int {
    $ctx = lab_attempt_context_any();
    if (!$ctx) return 0;
    $q=db()->prepare("SELECT COUNT(DISTINCT flag_number) FROM flag_submissions WHERE attempt_id=? AND status='accepted'");
    $q->execute([(int)$ctx['attempt_id']]);
    return (int)$q->fetchColumn();
}
function all_flags_obtained(): bool { return current_attempt_flag_count() >= 20; }
function scenario_has_event(string $code): bool {
    $ctx = lab_attempt_context_any();
    if ($ctx) {
        $q=db()->prepare("SELECT 1 FROM scenario_events WHERE attempt_id=? AND event_code=? LIMIT 1");
        $q->execute([(int)$ctx['attempt_id'],$code]);
    } else {
        $q=db()->prepare("SELECT 1 FROM scenario_events WHERE event_code=? LIMIT 1");
        $q->execute([$code]);
    }
    return (bool)$q->fetchColumn();
}
