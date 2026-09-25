<?php
require __DIR__.'/common.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_operational_network(true);

$sessionPresent=operational_is_auth();
$state=ot_call('/state');
$firmware=is_array($state['firmware']??null) ? $state['firmware'] : [];
$firmware['update_channel']='stable';
$firmware['update_feed']='/operations/updates/manifest.php';

if(empty($state) || isset($state['error'])){
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'telemetry-unavailable'],JSON_UNESCAPED_UNICODE);
    exit;
}

if(!$sessionPresent){
    header('X-INTERAFAS-Monitoring-Authorization: network-only');
    header('X-INTERAFAS-Operational-Session: missing');
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-EYES-ON-THE-PLANT-17');
    lab_event(
        'VULN17_TELEMETRY_WITHOUT_OPERATOR_AUTH',
        'Consulta de telemetría sin sesión operacional',
        '/operations/telemetry.php',
        ['challenge'=>17,'network_check'=>true,'session_present'=>false],
        'ot-hmi',
        'warning',
        5
    );
}

echo json_encode([
    'ok'=>true,
    'plant'=>$state['plant']??null,
    'tank'=>$state['tank']??null,
    'flow'=>$state['flow']??null,
    'pressure'=>$state['pressure']??null,
    'quality'=>$state['quality']??null,
    'p101'=>$state['p101']??null,
    'p102'=>$state['p102']??null,
    'v201'=>$state['v201']??null,
    'alarms'=>$state['alarms']??[],
    'firmware'=>$firmware
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
