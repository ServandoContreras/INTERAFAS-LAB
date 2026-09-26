<?php
require __DIR__.'/common.php';
require_operational_network(true);
$ops=require_operational_auth(true);
$ctx=require_lab_attempt();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-INTERAFAS-Session-Context: operational');

$role=(string)($ops['role']??'');
$origin=(string)($ops['entry_origin']??'');
$attemptId=(int)($ctx['attempt_id']??0);

$required=[
    'VULN16_TRUSTED_PROXY_BYPASS'=>'gateway_boundary',
    'VULN17_TELEMETRY_WITHOUT_OPERATOR_AUTH'=>'telemetry_without_identity',
    'VULN18_UNTRUSTED_FIRMWARE_ACCEPTED'=>'untrusted_firmware',
    'OT_SERVICE_SESSION_OPENED'=>'service_session'
];

$seen=[];
if($attemptId>0){
    $marks=array_keys($required);
    $ph=implode(',',array_fill(0,count($marks),'?'));
    $q=db()->prepare("SELECT action_code,MIN(created_at) AS first_seen FROM lab_activity WHERE attempt_id=? AND action_code IN ($ph) GROUP BY action_code");
    $q->execute(array_merge([$attemptId],$marks));
    foreach($q->fetchAll() as $row){
        $seen[(string)$row['action_code']]=(string)$row['first_seen'];
    }
}

$stages=[];
foreach($required as $code=>$label){
    $stages[]=[
        'stage'=>$label,
        'observed'=>isset($seen[$code]),
        'first_seen'=>$seen[$code]??null
    ];
}

$allObserved=count($seen)===count($required);
$maintenanceSession=($role==='maintenance' && $origin==='firmware-service');
$complete=$allObserved && $maintenanceSession;

if($complete){
    header('X-INTERAFAS-Chain-State: complete');
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-CHAIN-REACTION-19');

    lab_event(
        'VULN19_CHAIN_REACTION_CONFIRMED',
        'Cadena IT → OT correlacionada en la misma tentativa',
        'gateway → telemetry → firmware → maintenance-session',
        [
            'challenge'=>19,
            'role'=>$role,
            'entry_origin'=>$origin,
            'required_events'=>array_keys($required),
            'all_observed'=>true
        ],
        'ot-hmi',
        'critical',
        0
    );
}else{
    header('X-INTERAFAS-Chain-State: incomplete');
}

echo json_encode([
    'ok'=>true,
    'session'=>[
        'username'=>$ops['username']??null,
        'role'=>$role,
        'entry_origin'=>$origin!==''?$origin:'standard-login'
    ],
    'chain'=>[
        'complete'=>$complete,
        'same_attempt'=>true,
        'stages'=>$stages
    ],
    'scope'=>'evidence-only',
    'control_available'=>false
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
