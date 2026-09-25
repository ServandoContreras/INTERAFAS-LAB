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
    'release'=>[
        'version'=>'3.4.3',
        'build'=>'rtu07-20260918.343',
        'published'=>'2026-09-18T22:40:00Z',
        'author'=>'Mesa de Soporte OT <soporte.ot@interafas.local>',
        'compatibility'=>'RTU-GW-07'
    ],
    'support'=>[
        'profile'=>'legacy-maintenance',
        'contact'=>'soporte.ot@interafas.local',
        'auth_scheme'=>'basic',
        'scope'=>'firmware-install',
        'verification_ref'=>'sha1:6500c2c7de15391e46f0fb090f198867b902a2c1'
    ],
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
