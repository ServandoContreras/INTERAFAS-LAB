<?php
require __DIR__.'/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if(empty($_SESSION['user'])){
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'authentication-required'],JSON_UNESCAPED_UNICODE);
    exit;
}

$uid=(int)$_SESSION['user']['id'];
$q=db()->prepare('SELECT cuenta,contrato,estatus,fecha_lectura FROM cuentas_servicio WHERE usuario_id=? ORDER BY id LIMIT 1');
$q->execute([$uid]);
$account=$q->fetch();

if(!$account){
    echo json_encode([
        'ok'=>true,
        'account'=>null,
        'status'=>'unlinked',
        'links'=>[]
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}

lab_event(
    'API_ACCOUNT_CONTEXT',
    'Consulta de contexto de cuenta',
    (string)$account['cuenta'],
    ['challenge'=>11,'endpoint'=>'/api/account-context.php'],
    'interafas-api',
    'info',
    8
);

echo json_encode([
    'ok'=>true,
    'account'=>(string)$account['cuenta'],
    'contract'=>(string)$account['contrato'],
    'status'=>(string)$account['estatus'],
    'last_reading_date'=>$account['fecha_lectura'],
    'links'=>[
        'self'=>'/api/account-context.php',
        'history'=>'/api/service-history.php?account='.rawurlencode((string)$account['cuenta'])
    ]
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
