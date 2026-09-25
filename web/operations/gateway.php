<?php
require __DIR__.'/common.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-INTERAFAS-Gateway: operations-bridge');
header('X-INTERAFAS-Access-Policy: internal-network');

$source=operational_source();
$remote=$source['remote'];
$forwarded=$source['forwarded'];
$claimed=$source['claimed'];

$internal=operational_source_is_internal();

if(!$internal){
    lab_event(
        'OPERATIONS_GATEWAY_PROBE',
        'Consulta al gateway operacional restringido',
        '/operations/gateway.php',
        [
            'challenge'=>16,
            'gateway'=>'operations-bridge',
            'result'=>'restricted',
            'remote_addr'=>$remote,
            'claimed_source'=>$claimed,
            'source_header'=>$source['source']
        ],
        'interafas-web',
        'notice',
        8
    );

    http_response_code(403);
    echo json_encode([
        'ok'=>false,
        'error'=>'restricted-gateway',
        'message'=>'El gateway operacional requiere autorización adicional.',
        'policy'=>[
            'required_zone'=>'internal',
            'source_validation'=>'proxy-client-address'
        ]
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

$health=ot_call('/health');

if(empty($health) || isset($health['error'])){
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'operational-upstream-unavailable'],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    exit;
}

header('X-INTERAFAS-Boundary: crossed');
header('X-INTERAFAS-Validation: UPSLP_CNOIV-BEYOND-THE-WEB-16');

lab_event(
    'VULN16_TRUSTED_PROXY_BYPASS',
    'Gateway operacional aceptó origen declarado por el cliente',
    '/operations/gateway.php',
    [
        'challenge'=>16,
        'remote_addr'=>$remote,
        'claimed_source'=>$claimed,
        'trusted_header'=>'X-Forwarded-For',
        'upstream'=>'ot-sim',
        'target'=>'RTU-GW-07'
    ],
    'interafas-web',
    'warning',
    0
);

echo json_encode([
    'ok'=>true,
    'gateway'=>'operations-bridge',
    'zone'=>'operational',
    'access'=>'read-only',
    'source'=>[
        'observed_remote'=>$remote,
        'trusted_client'=>$claimed
    ],
    'upstream_status'=>'reachable',
    'links'=>[
        'operator_entry'=>'/operations/login.php'
    ]
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
