<?php
declare(strict_types=1);

function lab_context(): ?array {
    static $ctx = false;
    if ($ctx !== false) return $ctx ?: null;
    $token = (string)($_COOKIE['INTERAFAS_LAB_TOKEN'] ?? '');
    if ($token === '' || strlen($token) < 32) { $ctx = []; return null; }
    try {
        $q = db()->prepare("SELECT a.id attempt_id,a.student_id,a.status,a.started_at,s.nombre,s.apellido_paterno,s.apellido_materno,s.matricula FROM lab_attempts a JOIN lab_students s ON s.id=a.student_id WHERE a.attempt_token=? ORDER BY a.id DESC LIMIT 1");
        $q->execute([$token]);
        $row = $q->fetch();
        if (!$row || ($row['status'] ?? '') !== 'active') { $ctx = []; return null; }
        $ctx = $row;
        return $ctx;
    } catch (Throwable $e) { $ctx = []; return null; }
}

function lab_flag_is_accepted(int $flagNumber): bool {
    $ctx=lab_context();
    if(!$ctx || $flagNumber<1) return false;

    try {
        $q=db()->prepare("SELECT 1 FROM flag_submissions WHERE attempt_id=? AND flag_number=? AND status='accepted' LIMIT 1");
        $q->execute([(int)$ctx['attempt_id'],$flagNumber]);
        return (bool)$q->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}

function lab_event(string $code, string $label, ?string $detail=null, array $metadata=[], string $source='interafas-web', string $severity='info', int $dedupSeconds=3): void {
    $ctx = lab_context();
    if (!$ctx) return;
    try {
        if ($dedupSeconds > 0) {
            $q = db()->prepare("SELECT id FROM lab_activity WHERE attempt_id=? AND action_code=? AND COALESCE(detail,'')=COALESCE(?,'') AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND) ORDER BY id DESC LIMIT 1");
            $q->execute([(int)$ctx['attempt_id'],$code,$detail,$dedupSeconds]);
            if ($q->fetch()) return;
        }
        $q = db()->prepare("INSERT INTO lab_activity(attempt_id,student_id,action_code,action_label,source_system,severity,detail,metadata_json,ip_address,user_agent) VALUES(?,?,?,?,?,?,?,?,?,?)");
        $q->execute([
            (int)$ctx['attempt_id'],(int)$ctx['student_id'],$code,$label,$source,$severity,$detail,
            $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
            substr((string)($_SERVER['REMOTE_ADDR'] ?? ''),0,64),substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,255)
        ]);
    } catch (Throwable $e) { /* telemetry must never break the lab */ }
}

function lab_sensitive_page(string $page, string $label, string $severity='info'): void {
    lab_event('SENSITIVE_PAGE_VIEW','Acceso a módulo sensible: '.$label,$page,[
        'method'=>$_SERVER['REQUEST_METHOD'] ?? 'GET',
        'query'=>$_SERVER['QUERY_STRING'] ?? ''
    ],'interafas-web',$severity,8);
}


function lab_page_view(string $page, string $label): void {
    $uri = (string)($_SERVER['REQUEST_URI'] ?? $page);
    lab_event('PAGE_VIEW','Navegación INTERAFAS: '.$label,$uri,[
        'page'=>$page,
        'method'=>$_SERVER['REQUEST_METHOD'] ?? 'GET',
        'query'=>$_SERVER['QUERY_STRING'] ?? ''
    ],'interafas-web','info',8);
}
