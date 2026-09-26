<?php
require_once __DIR__.'/../includes/config.php';

function require_lab_attempt(): array {
    $ctx=lab_context();
    if(!$ctx){ http_response_code(403); exit('Este módulo requiere un intento activo del laboratorio. Inicia sesión primero en el Portal del Auditor.'); }
    return $ctx;
}

function operational_source(): array {
    $remote=(string)($_SERVER['REMOTE_ADDR']??'');
    $forwarded=trim((string)($_SERVER['HTTP_X_FORWARDED_FOR']??''));
    $claimed=$forwarded!=='' ? trim(explode(',',$forwarded)[0]) : $remote;
    return [
        'remote'=>$remote,
        'forwarded'=>$forwarded,
        'claimed'=>$claimed,
        'source'=>$forwarded!==''?'X-Forwarded-For':'REMOTE_ADDR'
    ];
}

function ipv4_in_cidr(string $ip, string $network, int $prefix): bool {
    $ipLong=ip2long($ip);
    $networkLong=ip2long($network);
    if($ipLong===false || $networkLong===false || $prefix<0 || $prefix>32) return false;
    $mask=$prefix===0 ? 0 : (-1 << (32-$prefix));
    return (($ipLong & $mask) === ($networkLong & $mask));
}

function operational_source_is_internal(): bool {
    $src=operational_source();
    return ipv4_in_cidr($src['claimed'],'10.40.20.0',24);
}

function require_operational_network(bool $hide=false): void {
    if(operational_source_is_internal()) return;

    /*
     * Persistencia académica del progreso:
     * una vez acreditada VULN-16 para el intento activo, el alumno no necesita
     * repetir el bypass de frontera de red después de perder sesión o
     * reconstruir contenedores.
     */
    if(lab_flag_is_accepted(16)){
        lab_event(
            'OT_NETWORK_ACCESS_RESTORED',
            'Acceso operacional restaurado desde progreso acreditado',
            'FLAG_16',
            ['flag_number'=>16,'source'=>'accepted-progress'],
            'ot-hmi',
            'notice',
            30
        );
        return;
    }

    if($hide){
        http_response_code(404);
        exit('404');
    }
    http_response_code(403);
    exit('Acceso permitido únicamente desde la red operacional.');
}

function operational_user(): array {
    if(isset($_SESSION['ops_user']) && is_array($_SESSION['ops_user']) && !empty($_SESSION['ops_user']['id'])){
        return $_SESSION['ops_user'];
    }

    /*
     * Persistencia académica del acceso operacional:
     * una vez acreditada VULN-18, la capacidad maintenance ya forma parte
     * del progreso del intento y no debe perderse por expiración/reinicio
     * de la sesión PHP.
     */
    if(empty($_SESSION['ops_restore_suppressed']) && lab_flag_is_accepted(18)){
        $_SESSION['ops_user']=[
            'id'=>1901,
            'username'=>'service.maintenance',
            'display_name'=>'Sesión de Mantenimiento',
            'role'=>'maintenance',
            'entry_origin'=>'restored-from-flag-18'
        ];

        lab_event(
            'OT_MAINTENANCE_SESSION_RESTORED',
            'Sesión maintenance reconstruida desde progreso acreditado',
            'FLAG_18',
            ['flag_number'=>18,'source'=>'accepted-progress'],
            'ot-hmi',
            'notice',
            30
        );

        return $_SESSION['ops_user'];
    }

    return [];
}

function operational_is_auth(): bool {
    return !empty(operational_user()['id']);
}

function require_operational_auth(bool $json=false): array {
    if(operational_is_auth()) return operational_user();
    if($json){
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['ok'=>false,'error'=>'operational-auth-required'],JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Location: /operations/login.php');
    exit;
}

function ot_call(string $path, string $method='GET', ?array $payload=null): array {
    $base=rtrim((string)(getenv('OT_BASE_URL') ?: 'http://ot-sim:8081'),'/');
    $opts=['http'=>['method'=>$method,'timeout'=>3,'ignore_errors'=>true,'header'=>"Content-Type: application/json\r\n"]];
    if($payload!==null) $opts['http']['content']=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $raw=@file_get_contents($base.$path,false,stream_context_create($opts));
    if($raw===false) return ['ok'=>false,'error'=>'ot_unavailable'];
    $data=json_decode($raw,true); return is_array($data)?$data:['ok'=>false,'error'=>'invalid_response'];
}
