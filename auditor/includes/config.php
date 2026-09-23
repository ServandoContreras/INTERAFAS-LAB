<?php
declare(strict_types=1);
session_start();

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $host = getenv('DB_HOST') ?: 'db';
    $name = getenv('DB_NAME') ?: 'interafas';
    $user = getenv('DB_USER') ?: 'interafas';
    $pass = getenv('DB_PASS') ?: 'interafas_lab';
    $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function is_auth(): bool { return !empty($_SESSION['student_id']) && !empty($_SESSION['attempt_id']); }
function require_auth(): void { if (!is_auth()) { header('Location: login.php'); exit; } }
function current_student_id(): int { return (int)($_SESSION['student_id'] ?? 0); }
function current_attempt_id(): int { return (int)($_SESSION['attempt_id'] ?? 0); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(400); exit('Solicitud inválida.');
    }
}
function current_student(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    if (!current_student_id()) return [];
    $q=db()->prepare("SELECT * FROM lab_students WHERE id=?"); $q->execute([current_student_id()]);
    return $cache=$q->fetch() ?: [];
}
function current_attempt(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    if (!current_attempt_id()) return [];
    $q=db()->prepare("SELECT * FROM lab_attempts WHERE id=? AND student_id=?"); $q->execute([current_attempt_id(), current_student_id()]);
    return $cache=$q->fetch() ?: [];
}
function attempt_is_active(): bool { $a=current_attempt(); return ($a['status'] ?? '') === 'active'; }
function require_active_attempt(): void {
    require_auth();
    if (!attempt_is_active()) { header('Location: index.php?finished=1'); exit; }
}
function student_full_name(array $s): string {
    return trim(($s['nombre']??'').' '.($s['apellido_paterno']??'').' '.($s['apellido_materno']??''));
}
function normalize_matricula(string $m): string {
    $m=strtoupper(trim($m));
    return preg_replace('/[^A-Z0-9_-]/', '', $m) ?? '';
}
function set_lab_cookie(string $token): void {
    setcookie('INTERAFAS_LAB_TOKEN', $token, [
        'expires'=>time()+60*60*24*14, 'path'=>'/', 'httponly'=>true, 'samesite'=>'Lax'
    ]);
}
function ensure_attempt_token(int $attemptId): string {
    $q=db()->prepare("SELECT attempt_token FROM lab_attempts WHERE id=?"); $q->execute([$attemptId]);
    $token=(string)($q->fetchColumn() ?: '');
    if($token===''){
        $token=bin2hex(random_bytes(32));
        $u=db()->prepare("UPDATE lab_attempts SET attempt_token=? WHERE id=?"); $u->execute([$token,$attemptId]);
    }
    return $token;
}
function log_activity(string $code, string $label, ?string $detail=null, array $metadata=[], string $source='auditor', string $severity='info'): void {
    if (!is_auth()) return;
    $q=db()->prepare("INSERT INTO lab_activity(attempt_id,student_id,action_code,action_label,source_system,severity,detail,metadata_json,ip_address,user_agent) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $q->execute([
        current_attempt_id(), current_student_id(), $code, $label, $source, $severity, $detail,
        $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null,
        substr((string)($_SERVER['REMOTE_ADDR']??''),0,64), substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255)
    ]);
}
function log_page_view(string $page, string $label): void { log_activity('PAGE_VIEW', 'Vista de página: '.$label, $page); }
function scenario_state(): array {
    $row = db()->query("SELECT *, TIMESTAMPDIFF(SECOND, phase_started_at, NOW()) AS elapsed_seconds FROM scenario_state WHERE id=1")->fetch();
    return $row ?: ['phase'=>0,'phase_name'=>'Operación normal','elapsed_seconds'=>0,'last_event_code'=>null];
}
function phase_label(int $p): string {
    return [0=>'Operación normal',1=>'Afectaciones iniciales',2=>'Incidente tecnológico confirmado',3=>'Compromiso operacional bajo investigación',4=>'Impacto operacional mayor',5=>'Recuperación y seguimiento'][$p] ?? 'Desconocida';
}
function elapsed_label(int $seconds): string {
    if ($seconds < 60) return $seconds.' s';
    if ($seconds < 3600) return floor($seconds/60).' min';
    return floor($seconds/3600).' h '.floor(($seconds%3600)/60).' min';
}
function attempt_stats(int $attemptId): array {
    $q=db()->prepare("SELECT COUNT(*) found,COALESCE(SUM(c.weight),0) score FROM flag_submissions s JOIN flag_catalog c ON c.flag_number=s.flag_number WHERE s.attempt_id=? AND s.status='accepted'");
    $q->execute([$attemptId]); $r=$q->fetch() ?: ['found'=>0,'score'=>0];
    $r['total']=(int)db()->query("SELECT COUNT(*) FROM flag_catalog")->fetchColumn();
    $r['max_score']=(int)db()->query("SELECT COALESCE(SUM(weight),0) FROM flag_catalog")->fetchColumn();
    return $r;
}
function build_log_csv(int $attemptId): string {
    $s=current_student();
    $q=db()->prepare("SELECT * FROM lab_attempts WHERE id=?"); $q->execute([$attemptId]); $a=$q->fetch() ?: [];
    $flagsQ=db()->prepare("SELECT s.created_at,c.flag_number,c.flag_value,c.code_name,c.title,c.category,c.difficulty,c.weight,s.note FROM flag_submissions s JOIN flag_catalog c ON c.flag_number=s.flag_number WHERE s.attempt_id=? ORDER BY s.created_at,s.id");
    $flagsQ->execute([$attemptId]); $flags=$flagsQ->fetchAll();
    $actQ=db()->prepare("SELECT created_at,action_code,action_label,source_system,severity,detail,metadata_json,ip_address,user_agent FROM lab_activity WHERE attempt_id=? ORDER BY created_at,id");
    $actQ->execute([$attemptId]); $activities=$actQ->fetchAll();
    $fp=fopen('php://temp','r+');
    fputcsv($fp,['INTERAFAS - LOG DE LABORATORIO']);
    fputcsv($fp,['Nombre',student_full_name($s)]);
    fputcsv($fp,['Matrícula',$s['matricula']??'']);
    fputcsv($fp,['Intento',$attemptId]);
    fputcsv($fp,['Inicio',$a['started_at']??'']);
    fputcsv($fp,['Finalización',$a['finalized_at']??'']);
    fputcsv($fp,[]);
    fputcsv($fp,['BANDERAS OBTENIDAS']);
    fputcsv($fp,['Fecha','#','Bandera','Código','Hallazgo','Categoría','Dificultad','Peso','Nota']);
    foreach($flags as $r) fputcsv($fp,[$r['created_at'],$r['flag_number'],$r['flag_value'],$r['code_name'],$r['title'],$r['category'],$r['difficulty'],$r['weight'],$r['note']]);
    fputcsv($fp,[]);
    fputcsv($fp,['BITÁCORA DE ACTIVIDAD']);
    fputcsv($fp,['Fecha','Código','Actividad','Sistema','Severidad','Detalle','Metadatos','IP','User-Agent']);
    foreach($activities as $r) fputcsv($fp,[$r['created_at'],$r['action_code'],$r['action_label'],$r['source_system'],$r['severity'],$r['detail'],$r['metadata_json'],$r['ip_address'],$r['user_agent']]);
    rewind($fp); $csv=stream_get_contents($fp); fclose($fp); return (string)$csv;
}
function telegram_send_log(int $attemptId): array {
    $token=trim((string)(getenv('TELEGRAM_BOT_TOKEN') ?: ''));
    $chat=trim((string)(getenv('TELEGRAM_CHAT_ID') ?: ''));
    if ($token==='' || $chat==='') return ['ok'=>false,'status'=>'not_configured','detail'=>'TELEGRAM_BOT_TOKEN o TELEGRAM_CHAT_ID no configurados.'];
    if (!function_exists('curl_init')) return ['ok'=>false,'status'=>'curl_unavailable','detail'=>'Extensión cURL no disponible.'];
    $s=current_student(); $stats=attempt_stats($attemptId); $csv=build_log_csv($attemptId);
    $safe=preg_replace('/[^A-Za-z0-9_-]/','_',($s['matricula']??'alumno')) ?: 'alumno';
    $tmp=tempnam(sys_get_temp_dir(),'interafas_'); file_put_contents($tmp,$csv);
    $caption="INTERAFAS · Laboratorio finalizado\n".student_full_name($s)."\nMatrícula: ".($s['matricula']??'')."\nBanderas: {$stats['found']}/{$stats['total']} · Puntaje técnico: {$stats['score']}/{$stats['max_score']}";
    $ch=curl_init("https://api.telegram.org/bot{$token}/sendDocument");
    $post=['chat_id'=>$chat,'caption'=>$caption,'document'=>new CURLFile($tmp,'text/csv',"INTERAFAS_{$safe}_intento_{$attemptId}.csv")];
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$post,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>20]);
    $body=curl_exec($ch); $err=curl_error($ch); $http=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch); @unlink($tmp);
    $data=$body ? json_decode((string)$body,true) : null; $ok=$http>=200 && $http<300 && is_array($data) && !empty($data['ok']);
    return ['ok'=>$ok,'status'=>$ok?'sent':'error','detail'=>$ok?'Log enviado a Telegram.':('HTTP '.$http.' '.$err.' '.substr((string)$body,0,260))];
}
