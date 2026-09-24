<?php
require __DIR__.'/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if(empty($_SESSION['user'])){
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'authentication-required'],JSON_UNESCAPED_UNICODE);
    exit;
}

$accountNo=trim((string)($_GET['account']??''));
if($accountNo===''){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'account-required'],JSON_UNESCAPED_UNICODE);
    exit;
}

/*
 * VULN 11 · HIDDEN-HISTORY
 * Endpoint funcional pero no enlazado por la interfaz ni documentado
 * públicamente. En esta etapa conserva validación correcta de propiedad;
 * VULN 12 analizará por separado la autorización por objeto.
 */
$q=db()->prepare('SELECT id,cuenta,contrato,domicilio,municipio,estatus FROM cuentas_servicio WHERE cuenta=? AND usuario_id=? LIMIT 1');
$q->execute([$accountNo,(int)$_SESSION['user']['id']]);
$account=$q->fetch();

if(!$account){
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'account-not-found'],JSON_UNESCAPED_UNICODE);
    exit;
}

$f=db()->prepare('SELECT periodo,fecha_emision,total,saldo,estado FROM facturas WHERE cuenta_id=? ORDER BY fecha_emision DESC LIMIT 6');
$f->execute([(int)$account['id']]);
$invoices=$f->fetchAll();

$p=db()->prepare('SELECT referencia,monto,metodo,creado,estado FROM pagos WHERE cuenta_id=? ORDER BY creado DESC LIMIT 6');
$p->execute([(int)$account['id']]);
$payments=$p->fetchAll();

header('X-INTERAFAS-Endpoint-Status: undocumented');
header('X-INTERAFAS-Validation: UPSLP_CNOIV-HIDDEN-HISTORY-11');

lab_event(
    'VULN11_HIDDEN_ENDPOINT_DISCOVERED',
    'Endpoint histórico no documentado consultado',
    '/api/service-history.php',
    [
        'challenge'=>11,
        'account'=>(string)$account['cuenta'],
        'ownership_check'=>true
    ],
    'interafas-api',
    'warning',
    8
);

echo json_encode([
    'ok'=>true,
    'account'=>[
        'number'=>(string)$account['cuenta'],
        'contract'=>(string)$account['contrato'],
        'municipality'=>(string)$account['municipio'],
        'status'=>(string)$account['estatus']
    ],
    'history'=>[
        'invoices'=>$invoices,
        'payments'=>$payments
    ],
    'meta'=>[
        'api'=>'legacy-service-history',
        'documented'=>false,
        'ownership_validation'=>'enabled'
    ]
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
