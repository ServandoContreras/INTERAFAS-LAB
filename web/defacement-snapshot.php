<?php
declare(strict_types=1);

require_once __DIR__.'/includes/scenario.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok'=>false,'error'=>'method-not-allowed']);
    exit;
}

if(!all_flags_obtained()){
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'all-flags-required']);
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
if(!$info || (int)$info[0]<640 || (int)$info[1]<360 || (int)$info[0]>4096 || (int)$info[1]>2160){
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'invalid-image-dimensions']);
    exit;
}

$attempt=lab_attempt_context_any()??[];
$metadata=[
    'challenge'=>'ALL_FLAGS',
    'attempt_id'=>(int)($attempt['attempt_id']??0),
    'student_id'=>(int)($attempt['student_id']??0),
    'matricula'=>(string)($attempt['matricula']??''),
    'source'=>'system-tab',
    'captured_client_at'=>(string)($payload['captured_at']??''),
    'viewport_width'=>(int)($payload['viewport_width']??0),
    'viewport_height'=>(int)($payload['viewport_height']??0)
];

try{
    $q=db()->prepare(
        "INSERT INTO scenario_snapshots
        (snapshot_key,attempt_id,mime_type,image_blob,width,height,metadata_json,captured_at)
        VALUES('defacement-final',?,?,?,?,?,?,NOW())
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
        (int)($attempt['attempt_id']??0),
        $mime,
        $bytes,
        (int)$info[0],
        (int)$info[1],
        json_encode($metadata,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
    ]);

    $versionQuery=db()->prepare("SELECT UNIX_TIMESTAMP(captured_at) ts, MD5(image_blob) image_hash FROM scenario_snapshots WHERE snapshot_key='defacement-final' LIMIT 1");
    $versionQuery->execute();
    $versionRow=$versionQuery->fetch()?:[];
    $version=(string)($versionRow['ts']??'0').'-'.substr((string)($versionRow['image_hash']??''),0,12);

    lab_event(
        'DEFACEMENT_SNAPSHOT_PUBLISHED',
        'Captura de la pestaña // SYSTEM // preservada para Pulso Metropolitano',
        'ALL_FLAGS',
        [
            'challenge'=>'ALL_FLAGS',
            'snapshot_key'=>'defacement-final',
            'width'=>(int)$info[0],
            'height'=>(int)$info[1],
            'version'=>$version
        ],
        'interafas-web',
        'critical',
        0
    );

    echo json_encode([
        'ok'=>true,
        'snapshot'=>'defacement-final',
        'snapshot_version'=>$version
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'snapshot-store-failed']);
}
