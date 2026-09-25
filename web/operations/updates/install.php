<?php
require __DIR__.'/../common.php';
require_operational_network(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$maintenanceUser=(string)($_SERVER['PHP_AUTH_USER']??'');
$maintenancePass=(string)($_SERVER['PHP_AUTH_PW']??'');
$maintenanceIdentity=null;

if($maintenanceUser!==''){
    $q=db()->prepare("SELECT id,username,display_name,role,password_hash,active FROM firmware_support_users WHERE username=? LIMIT 1");
    $q->execute([$maintenanceUser]);
    $maintenanceIdentity=$q->fetch();
}

if(!$maintenanceIdentity
   || (int)$maintenanceIdentity['active']!==1
   || !password_verify($maintenancePass,(string)$maintenanceIdentity['password_hash'])){
    header('WWW-Authenticate: Basic realm="INTERAFAS Firmware Support"');
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'maintenance-auth-required'],JSON_UNESCAPED_UNICODE);
    exit;
}

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok'=>false,'error'=>'method-not-allowed']);
    exit;
}

$raw=file_get_contents('php://input');
$pkg=json_decode((string)$raw,true);

if(!is_array($pkg)){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'invalid-json']);
    exit;
}

$device=(string)($pkg['device']??'');
$version=trim((string)($pkg['version']??''));
$payload=(string)($pkg['payload']??'');
$providedHash=strtolower(trim((string)($pkg['sha256']??'')));

if($device!=='RTU-GW-07' || $version==='' || $payload==='' || $providedHash===''){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'invalid-package']);
    exit;
}

$calculatedHash=hash('sha256',$payload);

if(!hash_equals($calculatedHash,$providedHash)){
    http_response_code(422);
    echo json_encode([
        'ok'=>false,
        'error'=>'integrity-check-failed',
        'verification'=>[
            'algorithm'=>'SHA-256',
            'calculated'=>$calculatedHash
        ]
    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

$config=json_decode($payload,true);
if(!is_array($config)){
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'invalid-payload']);
    exit;
}

$mode=substr((string)($config['mode']??'NORMAL'),0,32);
$diagnostic=substr((string)($config['diagnostic']??'LOCKED'),0,32);

$result=ot_call('/firmware','POST',[
    'package'=>[
        'device'=>'RTU-GW-07',
        'version'=>$version,
        'mode'=>$mode,
        'diagnostic'=>$diagnostic
    ]
]);

if(empty($result['ok'])){
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'firmware-apply-failed'],JSON_UNESCAPED_UNICODE);
    exit;
}

$approvedPayload='{"mode":"NORMAL","diagnostic":"LOCKED"}';
$approvedHash='dd367fa693b755bd804458adb3fac84e4bda650aae00d1614f706bafeb3d788d';
$nonApprovedPackage=!(
    $version==='3.4.3' &&
    hash_equals($approvedHash,$providedHash) &&
    hash_equals($approvedPayload,$payload)
);

header('X-INTERAFAS-Firmware-Verification: checksum-only');

if($nonApprovedPackage){
    header('X-INTERAFAS-Signature-Verification: not-performed');
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-TRUST-THE-PACKAGE-18');

    lab_event(
        'VULN18_UNTRUSTED_FIRMWARE_ACCEPTED',
        'Paquete de firmware no aprobado aceptado con checksum válido',
        'RTU-GW-07',
        [
            'challenge'=>18,
            'version'=>$version,
            'provided_hash'=>$providedHash,
            'signature_verified'=>false,
            'mode'=>$mode,
            'diagnostic'=>$diagnostic,
            'maintenance_identity'=>$maintenanceIdentity['username']??'',
            'maintenance_role'=>$maintenanceIdentity['role']??''
        ],
        'ot-sim',
        'critical',
        0
    );
}

$response=[
    'ok'=>true,
    'device'=>'RTU-GW-07',
    'version'=>$version,
    'verification'=>[
        'integrity'=>'passed',
        'algorithm'=>'SHA-256',
        'authenticity'=>'not-verified'
    ],
    'firmware'=>$result['firmware']??null,
    'reboot'=>$result['reboot']??null
];

if(strtoupper((string)($result['firmware']['diagnostic']??''))==='SERVICE'){
    $response['maintenance']=[
        'state'=>'available',
        'role'=>'maintenance',
        'console'=>'/operations/service-entry.php'
    ];
}

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
);
