<?php
require __DIR__.'/../common.php';
require_operational_network(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$current=ot_call('/firmware');

echo json_encode([
    'service'=>'RTU firmware update channel',
    'channel'=>'stable',
    'device'=>'RTU-GW-07',
    'current_version'=>$current['version']??null,
    'candidate_version'=>'3.4.3',
    'verification'=>[
        'algorithm'=>'SHA-256',
        'field'=>'sha256',
        'scope'=>'payload'
    ],
    'links'=>[
        'package'=>'/operations/updates/package.php?device=RTU-GW-07&version=3.4.3',
        'install'=>'/operations/updates/install.php'
    ]
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
