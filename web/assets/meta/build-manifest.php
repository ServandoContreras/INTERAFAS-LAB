<?php
declare(strict_types=1);

require_once __DIR__.'/../../includes/config.php';
require_once __DIR__.'/../../includes/telemetry.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

lab_event(
    'VULN02_CLIENT_MANIFEST_VIEW',
    'Consulta de manifiesto de compilación del cliente',
    '/assets/meta/build-manifest.php',
    [
        'challenge' => 2,
        'resource' => 'build-manifest',
        'release' => '2026.09.18-r4'
    ],
    'interafas-web',
    'notice',
    8
);

echo json_encode([
    'application' => 'interafas-portal',
    'release' => '2026.09.18-r4',
    'channel' => 'production',
    'generated_at' => '2026-09-18T22:14:31Z',
    'assets' => [
        'css' => '/assets/style.css',
        'js' => '/assets/app.js'
    ],
    'quality_assurance' => [
        'smoke_test' => 'passed',
        'release_validation_token' => 'UPSLP_CNOIV-LOOK-CLOSER-02'
    ],
    'support' => [
        'deployment_profile' => '/assets/meta/deployment-profile.php',
        'purpose' => 'diagnostico de despliegue'
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
