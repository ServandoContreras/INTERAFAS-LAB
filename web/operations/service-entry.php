<?php
require __DIR__.'/common.php';
require_operational_network(true);

$state=ot_call('/state');
$firmware=is_array($state['firmware']??null) ? $state['firmware'] : [];
$diagnostic=strtoupper(trim((string)($firmware['diagnostic']??'')));

if($diagnostic!=='SERVICE'){
    http_response_code(409);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'=>false,
        'error'=>'service-mode-required',
        'message'=>'La consola de mantenimiento sólo está disponible cuando RTU-GW-07 se encuentra en diagnóstico SERVICE.'
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

session_regenerate_id(true);
$_SESSION['ops_user']=[
    'id'=>1901,
    'username'=>'service.maintenance',
    'display_name'=>'Sesión de Mantenimiento',
    'role'=>'maintenance'
];

lab_event(
    'OT_SERVICE_SESSION_OPENED',
    'Sesión operacional de mantenimiento habilitada por estado SERVICE',
    'RTU-GW-07',
    [
        'firmware_version'=>$firmware['version']??null,
        'diagnostic'=>$diagnostic,
        'role'=>'maintenance',
        'entry'=>'/operations/service-entry.php'
    ],
    'ot-hmi',
    'notice',
    0
);

header('Location: /operations/');
exit;
