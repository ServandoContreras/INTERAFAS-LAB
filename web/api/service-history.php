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
 * El endpoint continúa siendo funcional y no documentado.
 *
 * VULN 12 · SECOND-ACCOUNT
 * La consulta localiza el objeto únicamente por su identificador público
 * (cuenta) y NO restringe el resultado al usuario autenticado. La sesión
 * demuestra quién es el actor, pero el servidor no comprueba si ese actor
 * puede consultar este objeto concreto.
 */
$q=db()->prepare('SELECT id,usuario_id,cuenta,contrato,domicilio,municipio,estatus FROM cuentas_servicio WHERE cuenta=? LIMIT 1');
$q->execute([$accountNo]);
$account=$q->fetch();

if(!$account){
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'account-not-found'],JSON_UNESCAPED_UNICODE);
    exit;
}

$currentUserId=(int)$_SESSION['user']['id'];
$isOwner=(int)$account['usuario_id']===$currentUserId;

$f=db()->prepare('SELECT periodo,fecha_emision,total,saldo,estado FROM facturas WHERE cuenta_id=? ORDER BY fecha_emision DESC LIMIT 6');
$f->execute([(int)$account['id']]);
$invoices=$f->fetchAll();

$p=db()->prepare('SELECT referencia,monto,metodo,creado,estado FROM pagos WHERE cuenta_id=? ORDER BY creado DESC LIMIT 6');
$p->execute([(int)$account['id']]);
$payments=$p->fetchAll();

header('X-INTERAFAS-Endpoint-Status: undocumented');

if($isOwner){
    // Conserva intacta la acreditación de VULN 11.
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-HIDDEN-HISTORY-11');

    lab_event(
        'VULN11_HIDDEN_ENDPOINT_DISCOVERED',
        'Endpoint histórico no documentado consultado',
        '/api/service-history.php',
        [
            'challenge'=>11,
            'account'=>(string)$account['cuenta'],
            'ownership_check'=>'matched'
        ],
        'interafas-api',
        'warning',
        8
    );
} else {
    // La bandera 12 sólo aparece cuando una sesión accede a un objeto ajeno.
    header('X-INTERAFAS-Object-Authorization: missing');
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-SECOND-ACCOUNT-12');

    lab_event(
        'VULN12_BOLA_CONFIRMED',
        'Acceso a objeto de otra cuenta mediante API',
        (string)$account['cuenta'],
        [
            'challenge'=>12,
            'authenticated_user_id'=>$currentUserId,
            'object_owner_user_id'=>(int)$account['usuario_id'],
            'requested_account'=>(string)$account['cuenta']
        ],
        'interafas-api',
        'warning',
        8
    );
}

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
        'object_authorization'=>$isOwner?'owner-match':'not-enforced'
    ]
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
