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

$incident=is_array($state['incident']??null) ? $state['incident'] : [
    'active'=>false,
    'profile'=>null,
    'stage'=>0,
    'stage_label'=>'NORMAL',
    'progress'=>0
];

if(!empty($incident['active'])){
    header('X-INTERAFAS-Cascade-Stage: '.(string)($incident['stage']??0));
    header('X-INTERAFAS-Cascade-State: '.rawurlencode((string)($incident['stage_label']??'ACTIVE')));
}

$finalFlag=null;

if($sessionPresent){
    $opsSession=is_array($_SESSION['ops_user']??null) ? $_SESSION['ops_user'] : [];
    $maintenance=(string)($opsSession['role']??'')==='maintenance';
    $cascadeOwned=!empty($_SESSION['vuln20_cascade_loaded']);
    $v19Complete=lab_flag_is_accepted(19);
    $stage=(int)($incident['stage']??0);

    if(
        $maintenance
        && $cascadeOwned
        && $v19Complete
        && !empty($incident['active'])
        && strtoupper((string)($incident['profile']??''))==='CASCADE'
        && $stage>=5
    ){
        $finalFlag='UPSLP_CNOIV-NOW-YOU-CONTROL-20';
        header('X-INTERAFAS-Operational-Impact: catastrophic');
        header('X-INTERAFAS-Validation: '.$finalFlag);

        if(empty($_SESSION['vuln20_flag_emitted'])){
            $_SESSION['vuln20_flag_emitted']=true;

            lab_event(
                'VULN20_CATASTROPHIC_STATE_REACHED',
                'Cascada operacional simulada alcanzó estado catastrófico',
                'RTU-GW-07 · CASCADE · stage 5',
                [
                    'challenge'=>20,
                    'role'=>'maintenance',
                    'alarm_count'=>$state['alarm_count']??null,
                    'availability'=>$state['availability']??null,
                    'stations_critical'=>$state['stations_critical']??null,
                    'flow'=>$state['flow']??null,
                    'pressure'=>$state['pressure']??null,
                    'quality'=>$state['quality']??null
                ],
                'ot-hmi',
                'critical',
                0
            );
        }
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

$labCtx=lab_context()??[];
$auditorName=trim(implode(' ',array_filter([
    (string)($labCtx['nombre']??''),
    (string)($labCtx['apellido_paterno']??''),
    (string)($labCtx['apellido_materno']??'')
])));

echo json_encode([
    'ok'=>true,
    'auditor'=>[
        'name'=>$auditorName!==''?$auditorName:'Auditor',
        'matricula'=>(string)($labCtx['matricula']??'')
    ],
    'plant'=>$state['plant']??null,
    'tank'=>$state['tank']??null,
    'flow'=>$state['flow']??null,
    'pressure'=>$state['pressure']??null,
    'quality'=>$state['quality']??null,
    'p101'=>$state['p101']??null,
    'p102'=>$state['p102']??null,
    'v201'=>$state['v201']??null,
    'alarms'=>$state['alarms']??[],
    'alarm_count'=>(int)($state['alarm_count']??count($state['alarms']??[])),
    'process_state'=>$state['process_state']??'NORMAL',
    'zone_a'=>$state['zone_a']??'NORMAL',
    'availability'=>$state['availability']??99.82,
    'stations_critical'=>(int)($state['stations_critical']??0),
    'incident'=>$incident,
    'final_flag'=>$finalFlag,
    'firmware'=>$firmware
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
