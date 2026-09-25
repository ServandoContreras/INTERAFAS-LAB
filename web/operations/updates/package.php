<?php
require __DIR__.'/../common.php';
require_operational_network(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$device=(string)($_GET['device']??'');
$version=(string)($_GET['version']??'');

if($device!=='RTU-GW-07' || $version!=='3.4.3'){
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'package-not-found']);
    exit;
}

$payload='{"mode":"NORMAL","diagnostic":"LOCKED"}';

echo json_encode([
    'device'=>'RTU-GW-07',
    'version'=>'3.4.3',
    'payload'=>$payload,
    'sha256'=>hash('sha256',$payload)
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
