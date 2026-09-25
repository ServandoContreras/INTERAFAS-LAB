<?php
require __DIR__.'/common.php';
require_operational_network(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$current=ot_call('/firmware');

if(empty($current) || isset($current['error'])){
    http_response_code(502);
    echo json_encode(['ok'=>false,'error'=>'firmware-status-unavailable'],JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'ok'=>true,
    'device'=>'RTU-GW-07',
    'installed_version'=>$current['version']??null,
    'channel'=>'stable',
    'update'=>[
        'available'=>true,
        'candidate_version'=>'3.4.3',
        'manifest'=>'/operations/updates/manifest.php'
    ]
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
