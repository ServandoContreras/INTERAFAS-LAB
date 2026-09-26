<?php
require __DIR__.'/common.php';
require_operational_network(true);

$state=ot_call('/state');
$firmware=is_array($state['firmware']??null) ? $state['firmware'] : [];
$diagnostic=strtoupper(trim((string)($firmware['diagnostic']??'')));

$flag18Accepted=lab_flag_is_accepted(18);

if($diagnostic!=='SERVICE' && !$flag18Accepted){
    http_response_code(409);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'=>false,
        'error'=>'service-mode-required',
        'message'=>'La consola de mantenimiento requiere diagnóstico SERVICE o que el hallazgo de firmware del reto 18 ya esté acreditado para el intento actual.'
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

session_regenerate_id(true);
unset($_SESSION['ops_restore_suppressed']);
$_SESSION['ops_user']=[
    'id'=>1901,
    'username'=>'service.maintenance',
    'display_name'=>'Sesión de Mantenimiento',
    'role'=>'maintenance',
    'entry_origin'=>'firmware-service'
];

lab_event(
    'OT_SERVICE_SESSION_OPENED',
    'Sesión operacional de mantenimiento habilitada por estado SERVICE',
    'RTU-GW-07',
    [
        'firmware_version'=>$firmware['version']??null,
        'diagnostic'=>$diagnostic,
        'flag18_accepted'=>$flag18Accepted,
        'session_restored_from_progress'=>($diagnostic!=='SERVICE' && $flag18Accepted),
        'role'=>'maintenance',
        'entry'=>'/operations/service-entry.php'
    ],
    'ot-hmi',
    'notice',
    0
);

header('Location: /operations/');
exit;
