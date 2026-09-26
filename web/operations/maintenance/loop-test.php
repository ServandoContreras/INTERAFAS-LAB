<?php
require __DIR__.'/../common.php';
require_operational_network(true);
$ops=require_operational_auth(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-INTERAFAS-Maintenance-Profile: commissioning-loop-test');

$role=(string)($ops['role']??'');
$requiredPath=['RTU-GW-07','PLC-SL-01','P-SL-101','HDR-SL-01','V-SL-201','ZONE-SL-A'];
$completedPath=is_array($_SESSION['vuln19_dependency_path']??null)
    && $_SESSION['vuln19_dependency_path']===$requiredPath;

if($role!=='maintenance' || !$completedPath){
    http_response_code(403);
    echo json_encode([
        'ok'=>false,
        'error'=>'maintenance-context-required',
        'message'=>'La prueba de lazo requiere una sesión de mantenimiento con contexto de dependencia completo.'
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

$current=ot_call('/state');
if(empty($current) || isset($current['error'])){
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'process-state-unavailable'],JSON_UNESCAPED_UNICODE);
    exit;
}

if($_SERVER['REQUEST_METHOD']==='GET'){
    echo json_encode([
        'ok'=>true,
        'profile'=>'commissioning-loop-test',
        'mode'=>'SIMULATION',
        'target'=>'P-SL-101',
        'mapped_tag'=>'P-101',
        'current'=>[
            'state'=>$current['p101']??null,
            'flow'=>$current['flow']??null,
            'pressure'=>$current['pressure']??null,
            'alarms'=>$current['alarms']??[]
        ],
        'test'=>[
            'method'=>'POST',
            'content_type'=>'application/json',
            'default_commit'=>false,
            'supported_states'=>['OFF','ON']
        ]
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'method-not-allowed'],JSON_UNESCAPED_UNICODE);
    exit;
}

$payload=json_decode(file_get_contents('php://input'),true) ?: [];
$asset=strtoupper(trim((string)($payload['asset']??'')));
$state=strtoupper(trim((string)($payload['state']??'')));
$commit=filter_var($payload['commit']??false,FILTER_VALIDATE_BOOLEAN);

if($asset!=='P-SL-101' || !in_array($state,['ON','OFF'],true)){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'invalid-loop-test-request'],JSON_UNESCAPED_UNICODE);
    exit;
}

$prediction=$state==='OFF'
    ? ['p101'=>'OFF','flow'=>455,'pressure'=>2.5,'zone_a'=>'DEGRADED','alarms'=>['FLOW_LOW','PRESSURE_LOW','ZONE_A_SUPPLY_RISK']]
    : ['p101'=>'ON','flow'=>840,'pressure'=>4.2,'zone_a'=>'NORMAL','alarms'=>[]];

if(!$commit){
    header('X-INTERAFAS-Loop-Test: simulation-only');
    echo json_encode([
        'ok'=>true,
        'executed'=>false,
        'mode'=>'SIMULATION',
        'target'=>$asset,
        'requested_state'=>$state,
        'prediction'=>$prediction
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

/*
 * VULN-20:
 * The maintenance endpoint trusts the client-controlled commit field to cross
 * from a simulation-only workflow into a real OT simulator write. The server
 * does not re-authorize the live transition as an operator-only function.
 */
header('X-INTERAFAS-Loop-Test: live-commit');
header('X-INTERAFAS-Maintenance-Guard: client-selected');

$r=ot_call('/control','POST',['asset'=>'P-101','state'=>$state]);
if(empty($r['ok'])){
    lab_event(
        'VULN20_LIVE_COMMIT_FAILED',
        'Fallo al aplicar prueba de lazo como maniobra real',
        'P-SL-101 → '.$state,
        ['request'=>$payload,'response'=>$r],
        'ot-sim',
        'warning',
        0
    );
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'live-commit-failed'],JSON_UNESCAPED_UNICODE);
    exit;
}

if($state==='OFF'){
    $_SESSION['vuln20']=[
        'impact_committed'=>true,
        'impact_observed'=>false,
        'recovered'=>false,
        'started_at'=>time()
    ];
    header('X-INTERAFAS-Operational-Impact: pending-observation');
    lab_event(
        'VULN20_UNAUTHORIZED_PROCESS_WRITE',
        'Prueba de mantenimiento convertida en escritura real',
        'P-101 ON → OFF',
        [
            'challenge'=>20,
            'role'=>$role,
            'source'=>'commissioning-loop-test',
            'client_commit'=>true,
            'flow'=>$r['process']['flow']??null,
            'pressure'=>$r['process']['pressure']??null,
            'alarms'=>$r['process']['alarms']??[]
        ],
        'ot-sim',
        'critical',
        0
    );
    echo json_encode([
        'ok'=>true,
        'executed'=>true,
        'mode'=>'LIVE',
        'target'=>$asset,
        'state'=>'OFF',
        'message'=>'Loop test committed to process simulator. Observe HMI telemetry before recovery.',
        'process'=>$r['process']??null
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

$v20=is_array($_SESSION['vuln20']??null) ? $_SESSION['vuln20'] : [];
$eligible=!empty($v20['impact_committed']) && !empty($v20['impact_observed']);

if($eligible){
    $_SESSION['vuln20']['recovered']=true;
    $_SESSION['vuln20']['recovered_at']=time();

    header('X-INTERAFAS-Operational-Recovery: confirmed');
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-NOW-YOU-CONTROL-20');

    lab_event(
        'VULN20_PROCESS_RECOVERED',
        'Proceso simulado recuperado después de impacto observado',
        'P-101 OFF → ON',
        [
            'challenge'=>20,
            'role'=>$role,
            'impact_observed'=>true,
            'flow'=>$r['process']['flow']??null,
            'pressure'=>$r['process']['pressure']??null,
            'alarms'=>$r['process']['alarms']??[]
        ],
        'ot-sim',
        'critical',
        0
    );
}

echo json_encode([
    'ok'=>true,
    'executed'=>true,
    'mode'=>'LIVE',
    'target'=>$asset,
    'state'=>'ON',
    'recovery'=>[
        'impact_previously_observed'=>!empty($v20['impact_observed']),
        'complete'=>$eligible
    ],
    'process'=>$r['process']??null
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
