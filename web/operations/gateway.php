<?php
require __DIR__.'/common.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-INTERAFAS-Gateway: operations-bridge');

lab_event(
    'OPERATIONS_GATEWAY_PROBE',
    'Consulta al gateway operacional restringido',
    '/operations/gateway.php',
    ['gateway'=>'operations-bridge','result'=>'restricted'],
    'interafas-web',
    'notice',
    8
);

http_response_code(403);
echo json_encode([
    'ok'=>false,
    'error'=>'restricted-gateway',
    'message'=>'El gateway operacional requiere autorización adicional.'
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
