<?php
declare(strict_types=1);

require_once __DIR__.'/../../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-INTERAFAS-Profile: internal-deployment');
header('X-INTERAFAS-Validation: UPSLP_CNOIV-BEHIND-THE-DESK-15');

lab_event(
    'VULN15_DEPLOYMENT_PROFILE_VIEW',
    'Perfil interno de despliegue expuesto',
    '/assets/meta/deployment-profile.php',
    [
        'challenge'=>15,
        'profile'=>'deployment-support',
        'operational_upstream'=>'ot-sim:8081'
    ],
    'interafas-web',
    'warning',
    8
);

echo json_encode([
    'service'=>'interafas-web',
    'environment'=>'production',
    'profile'=>'deployment-support',
    'generated_for'=>'mesa-de-soporte',
    'network'=>[
        'scope'=>'internal-compose',
        'public_entry'=>'web:80',
        'data_store'=>'db:3306/interafas',
        'content_service'=>'news:80'
    ],
    'operational_connector'=>[
        'component'=>'operations-bridge',
        'application_path'=>'/operations/',
        'upstream'=>'http://ot-sim:8081',
        'protocol'=>'HTTP/JSON',
        'target'=>'RTU-GW-07'
    ],
    'notes'=>[
        'El conector operacional sólo debe ser accesible desde la capa de aplicación.',
        'Los nombres de servicio de la red interna no forman parte de la documentación pública.'
    ]
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
