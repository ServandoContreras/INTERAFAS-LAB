<?php
require_once __DIR__.'/../includes/config.php';
function require_lab_attempt(): array {
    $ctx=lab_context();
    if(!$ctx){ http_response_code(403); exit('Este módulo requiere un intento activo del laboratorio. Inicia sesión primero en el Portal del Auditor.'); }
    return $ctx;
}
function ops_bridge_expected_token(): ?string {
    $labToken=(string)($_COOKIE['INTERAFAS_LAB_TOKEN']??'');
    if($labToken==='' || !lab_context()) return null;
    $secret=(string)(getenv('DB_PASS') ?: 'interafas_lab');
    return hash_hmac('sha256','operations-bridge:'.$labToken,$secret);
}
function establish_ops_bridge_context(): bool {
    $token=ops_bridge_expected_token();
    if($token===null) return false;
    setcookie('INTERAFAS_OPS_BRIDGE',$token,[
        'path'=>'/operations/',
        'httponly'=>true,
        'samesite'=>'Lax'
    ]);
    $_COOKIE['INTERAFAS_OPS_BRIDGE']=$token;
    return true;
}
function require_ops_bridge_context(): void {
    $expected=ops_bridge_expected_token();
    $present=(string)($_COOKIE['INTERAFAS_OPS_BRIDGE']??'');
    if($expected===null || $present==='' || !hash_equals($expected,$present)){
        lab_event(
            'OPS_ROUTE_HIDDEN',
            'Ruta operacional solicitada sin contexto de puente',
            (string)($_SERVER['REQUEST_URI']??'/operations/'),
            ['bridge_context'=>'missing'],
            'interafas-web',
            'notice',
            8
        );
        http_response_code(404);
        exit('404');
    }
}
function ot_call(string $path, string $method='GET', ?array $payload=null): array {
    $base=rtrim((string)(getenv('OT_BASE_URL') ?: 'http://ot-sim:8081'),'/');
    $opts=['http'=>['method'=>$method,'timeout'=>3,'ignore_errors'=>true,'header'=>"Content-Type: application/json\r\n"]];
    if($payload!==null) $opts['http']['content']=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $raw=@file_get_contents($base.$path,false,stream_context_create($opts));
    if($raw===false) return ['ok'=>false,'error'=>'ot_unavailable'];
    $data=json_decode($raw,true); return is_array($data)?$data:['ok'=>false,'error'=>'invalid_response'];
}
