<?php
declare(strict_types=1);

require_once __DIR__.'/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$state='available';
$checkedAt=gmdate('c');

header('X-INTERAFAS-Diagnostic-Scope: infrastructure-status');
header('X-INTERAFAS-Validation: UPSLP_CNOIV-BEHIND-THE-DESK-15');

lab_event(
    'VULN15_INFRA_STATUS_DISCLOSURE',
    'Metadatos internos expuestos por estado de infraestructura',
    '/api/infrastructure-status.php',
    [
        'challenge'=>15,
        'component'=>'operations-bridge',
        'application_route'=>'/operations/',
        'upstream_service'=>'ot-sim'
    ],
    'interafas-web',
    'warning',
    8
);

echo json_encode([
    'ok'=>true,
    'status'=>[
        'telemetry'=>'available',
        'operations_center'=>'online',
        'last_check'=>$checkedAt
    ],
    'diagnostics'=>[
        'component'=>'operations-bridge',
        'trust_boundary'=>'web-to-operations',
        'application_route'=>'/operations/',
        'upstream_service'=>'ot-sim',
        'transport'=>'internal-http',
        'target'=>'RTU-GW-07'
    ]
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
