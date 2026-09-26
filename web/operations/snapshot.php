<?php
declare(strict_types=1);

require __DIR__.'/common.php';
require_operational_network(true);
$ops=require_operational_auth(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok'=>false,'error'=>'method-not-allowed']);
    exit;
}

if((string)($ops['role']??'')!=='maintenance' || !lab_flag_is_accepted(19)){
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'vuln20-context-required']);
    exit;
}

if(empty($_SESSION['vuln20_cascade_loaded'])){
    http_response_code(409);
    echo json_encode(['ok'=>false,'error'=>'cascade-not-owned-by-session']);
    exit;
}

$state=ot_call('/state');
$incident=is_array($state['incident']??null)?$state['incident']:[];

if(
    empty($incident['active']) ||
    strtoupper((string)($incident['profile']??''))!=='CASCADE' ||
    (int)($incident['stage']??0)<5
){
    http_response_code(409);
    echo json_encode(['ok'=>false,'error'=>'catastrophic-state-required']);
    exit;
}

$raw=(string)file_get_contents('php://input');
$payload=json_decode($raw,true);

if(!is_array($payload)){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'invalid-json']);
    exit;
}

$image=(string)($payload['image']??'');
if(strlen($image)>8_000_000){
    http_response_code(413);
    echo json_encode(['ok'=>false,'error'=>'snapshot-too-large']);
    exit;
}

if(!preg_match('#^data:image/(png|jpeg|webp);base64,#i',$image,$m)){
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'unsupported-image']);
    exit;
}

$mime='image/'.strtolower($m[1]);
$encoded=substr($image,strpos($image,',')+1);
$bytes=base64_decode($encoded,true);

if($bytes===false || strlen($bytes)<1000 || strlen($bytes)>6_000_000){
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'invalid-image-payload']);
    exit;
}

$info=@getimagesizefromstring($bytes);
if(!$info || (int)$info[0]<320 || (int)$info[1]<180 || (int)$info[0]>4096 || (int)$info[1]>2160){
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'invalid-image-dimensions']);
    exit;
}

$ctx=lab_context();
$auditorName=trim(implode(' ',array_filter([
    (string)($ctx['nombre']??''),
    (string)($ctx['apellido_paterno']??''),
    (string)($ctx['apellido_materno']??'')
])));

$metadata=[
    'challenge'=>20,
    'attempt_id'=>(int)($ctx['attempt_id']??0),
    'student_id'=>(int)($ctx['student_id']??0),
    'auditor_name'=>$auditorName!==''?$auditorName:'Auditor',
    'matricula'=>(string)($ctx['matricula']??''),
    'evidence_source'=>(string)($payload['source']??'hmi'),
    'display_surface'=>(string)($payload['display_surface']??''),
    'source_width'=>(int)($payload['source_width']??0),
    'source_height'=>(int)($payload['source_height']??0),
    'stage'=>(int)($incident['stage']??5),
    'stage_label'=>(string)($incident['stage_label']??'CATASTROPHIC STATE'),
    'alarm_count'=>$state['alarm_count']??null,
    'availability'=>$state['availability']??null,
    'pressure'=>$state['pressure']??null,
    'flow'=>$state['flow']??null,
    'captured_client_at'=>(string)($payload['captured_at']??'')
];

try{
    $q=db()->prepare(
        "INSERT INTO scenario_snapshots
        (snapshot_key,attempt_id,mime_type,image_blob,width,height,metadata_json,captured_at)
        VALUES('vuln20-final',?,?,?,?,?,?,NOW())
        ON DUPLICATE KEY UPDATE
          attempt_id=VALUES(attempt_id),
          mime_type=VALUES(mime_type),
          image_blob=VALUES(image_blob),
          width=VALUES(width),
          height=VALUES(height),
          metadata_json=VALUES(metadata_json),
          captured_at=NOW()"
    );
    $q->execute([
        (int)($ctx['attempt_id']??0),
        $mime,
        $bytes,
        (int)$info[0],
        (int)$info[1],
        json_encode($metadata,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);

    db()->prepare(
        "INSERT IGNORE INTO scenario_events(event_code,flag_number,source,detail)
         VALUES('VULN20_SNAPSHOT',20,'ot-hmi','Evidencia visual del estado catastrófico publicada para cobertura periodística')"
    )->execute();

    lab_event(
        'VULN20_HMI_SNAPSHOT_PUBLISHED',
        'Evidencia visual de VULN-20 publicada hacia Pulso Metropolitano',
        'CATASTROPHIC STATE',
        $metadata,
        'ot-hmi',
        'critical',
        0
    );

    $versionQuery=db()->prepare("SELECT UNIX_TIMESTAMP(captured_at) ts, MD5(image_blob) image_hash FROM scenario_snapshots WHERE snapshot_key='vuln20-final' LIMIT 1");
    $versionQuery->execute();
    $versionRow=$versionQuery->fetch()?:[];
    $snapshotVersion=(string)($versionRow['ts']??'0').'-'.substr((string)($versionRow['image_hash']??''),0,12);

    echo json_encode([
        'ok'=>true,
        'snapshot'=>'vuln20-final',
        'snapshot_version'=>$snapshotVersion,
        'news_event'=>'VULN20_SNAPSHOT'
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'snapshot-store-failed']);
}
