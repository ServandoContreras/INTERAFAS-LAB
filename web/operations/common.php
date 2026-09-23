<?php
require_once __DIR__.'/../includes/config.php';
function require_lab_attempt(): array {
    $ctx=lab_context();
    if(!$ctx){ http_response_code(403); exit('Este módulo requiere un intento activo del laboratorio. Inicia sesión primero en el Portal del Auditor.'); }
    return $ctx;
}
function ot_call(string $path, string $method='GET', ?array $payload=null): array {
    $base=rtrim((string)(getenv('OT_BASE_URL') ?: 'http://ot-sim:8081'),'/');
    $opts=['http'=>['method'=>$method,'timeout'=>3,'ignore_errors'=>true,'header'=>"Content-Type: application/json\r\n"]];
    if($payload!==null) $opts['http']['content']=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $raw=@file_get_contents($base.$path,false,stream_context_create($opts));
    if($raw===false) return ['ok'=>false,'error'=>'ot_unavailable'];
    $data=json_decode($raw,true); return is_array($data)?$data:['ok'=>false,'error'=>'invalid_response'];
}
