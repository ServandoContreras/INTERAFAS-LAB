<?php
require __DIR__.'/../common.php';
require_operational_network(true);

header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: no-store');

echo "window.INTERAFAS_OPS = Object.freeze({\n";
echo "  telemetryEndpoint: '/operations/telemetry.php',\n";
echo "  refreshInterval: 5000\n";
echo "});\n";
