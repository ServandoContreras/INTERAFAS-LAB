<?php
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $host = getenv('DB_HOST') ?: 'db';
    $name = getenv('DB_NAME') ?: 'interafas';
    $user = getenv('DB_USER') ?: 'interafas';
    $pass = getenv('DB_PASS') ?: 'interafas_lab';
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__.'/telemetry.php';

/*
 * VULN 10 · WHO-ARE-YOU
 * Si una sesión autenticada conserva exactamente el identificador previo al
 * login y ese mismo identificador reaparece desde un cliente con otro
 * User-Agent, el laboratorio considera demostrada la reutilización.
 */
if(!empty($_SESSION['user']) && !empty($_SESSION['vuln10_session_probe']) && is_array($_SESSION['vuln10_session_probe'])){
    $probe=$_SESSION['vuln10_session_probe'];
    $preAuthSid=(string)($probe['preauth_sid'] ?? '');
    $authenticatedSid=(string)($probe['authenticated_sid'] ?? '');
    $loginUa=(string)($probe['login_user_agent'] ?? '');
    $currentUa=substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,255);
    $currentSid=session_id();

    $sameUnrotatedSession=
        $preAuthSid!=='' &&
        hash_equals($preAuthSid,$authenticatedSid) &&
        hash_equals($authenticatedSid,$currentSid);

    $differentClient=$loginUa!=='' && $currentUa!=='' && !hash_equals($loginUa,$currentUa);

    if($sameUnrotatedSession && $differentClient){
        header('X-INTERAFAS-Session-State: replay-accepted');
        header('X-INTERAFAS-Validation: UPSLP_CNOIV-WHO-ARE-YOU-10');

        lab_event(
            'VULN10_SESSION_REPLAY_CONFIRMED',
            'Reutilización de sesión autenticada desde un segundo cliente',
            (string)($_SESSION['user']['usuario'] ?? 'authenticated-user'),
            [
                'challenge'=>10,
                'preauth_equals_authenticated'=>true,
                'client_changed'=>true
            ],
            'interafas-web',
            'warning',
            8
        );
    }
}
