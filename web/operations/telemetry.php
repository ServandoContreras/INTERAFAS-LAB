<?php
require __DIR__.'/common.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_operational_network(true);

$sessionPresent=operational_is_auth();
$state=ot_call('/state');
$firmware=is_array($state['firmware']??null) ? $state['firmware'] : [];
$firmware['update_status']='available';

if(empty($state) || isset($state['error'])){
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'telemetry-unavailable'],JSON_UNESCAPED_UNICODE);
    exit;
}

if($sessionPresent){
    $opsSession=is_array($_SESSION['ops_user']??null) ? $_SESSION['ops_user'] : [];
    $v20=is_array($_SESSION['vuln20']??null) ? $_SESSION['vuln20'] : [];
    $maintenance=(string)($opsSession['role']??'')==='maintenance';

    if(
        $maintenance
        && !empty($v20['impact_committed'])
        && empty($v20['impact_observed'])
        && strtoupper((string)($state['p101']??''))==='OFF'
        && in_array('FLOW_LOW',$state['alarms']??[],true)
        && in_array('PRESSURE_LOW',$state['alarms']??[],true)
    ){
        $_SESSION['vuln20']['impact_observed']=true;
        $_SESSION['vuln20']['observed_at']=time();
        header('X-INTERAFAS-Operational-Impact: observed');

        lab_event(
            'VULN20_PROCESS_IMPACT_OBSERVED',
            'HMI observó degradación operacional provocada por la prueba de lazo',
            'P-101 OFF · FLOW_LOW · PRESSURE_LOW',
            [
                'challenge'=>20,
                'role'=>'maintenance',
                'flow'=>$state['flow']??null,
                'pressure'=>$state['pressure']??null,
                'zone_a'=>$state['zone_a']??null,
                'alarms'=>$state['alarms']??[]
            ],
            'ot-hmi',
            'critical',
            0
        );
    }
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
    'process_state'=>$state['process_state']??'NORMAL',
    'zone_a'=>$state['zone_a']??'NORMAL',
    'firmware'=>$firmware
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
