<?php
declare(strict_types=1);

function news_lab_context(): ?array {
    static $ctx = false;
    if ($ctx !== false) return $ctx ?: null;
    $token = (string)($_COOKIE['INTERAFAS_LAB_TOKEN'] ?? '');
    if ($token === '' || strlen($token) < 32) { $ctx=[]; return null; }
    try {
        $q=db()->prepare("SELECT a.id attempt_id,a.student_id,a.status,s.matricula FROM lab_attempts a JOIN lab_students s ON s.id=a.student_id WHERE a.attempt_token=? ORDER BY a.id DESC LIMIT 1");
        $q->execute([$token]);
        $row=$q->fetch();
        if(!$row || ($row['status']??'')!=='active'){ $ctx=[]; return null; }
        $ctx=$row; return $ctx;
    } catch(Throwable $e){ $ctx=[]; return null; }
}

function news_lab_event(string $code,string $label,?string $detail=null,array $metadata=[],string $severity='info',int $dedupSeconds=8): void {
    $ctx=news_lab_context(); if(!$ctx) return;
    try {
        if($dedupSeconds>0){
            $q=db()->prepare("SELECT id FROM lab_activity WHERE attempt_id=? AND action_code=? AND source_system='pulso-news' AND COALESCE(detail,'')=COALESCE(?,'') AND created_at>=DATE_SUB(NOW(),INTERVAL ? SECOND) ORDER BY id DESC LIMIT 1");
            $q->execute([(int)$ctx['attempt_id'],$code,$detail,$dedupSeconds]);
            if($q->fetch()) return;
        }
        $q=db()->prepare("INSERT INTO lab_activity(attempt_id,student_id,action_code,action_label,source_system,severity,detail,metadata_json,ip_address,user_agent) VALUES(?,?,?,?,?,?,?,?,?,?)");
        $q->execute([(int)$ctx['attempt_id'],(int)$ctx['student_id'],$code,$label,'pulso-news',$severity,$detail,
            $metadata?json_encode($metadata,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null,
            substr((string)($_SERVER['REMOTE_ADDR']??''),0,64),substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255)]);
    } catch(Throwable $e) { /* telemetry must not affect newsroom */ }
}

function news_lab_page_view(string $label): void {
    $uri=(string)($_SERVER['REQUEST_URI']??'/');
    news_lab_event('PAGE_VIEW','Navegación Pulso Metropolitano: '.$label,$uri,[
        'method'=>$_SERVER['REQUEST_METHOD']??'GET',
        'query'=>$_SERVER['QUERY_STRING']??''
    ]);
}
