<?php
require __DIR__.'/common.php';
require_operational_network();

$state=ot_call('/state');
$fw=$state['firmware']??[];
$ctx=lab_context();

if($ctx){
    header('X-INTERAFAS-Monitoring-Authorization: network-only');
    header('X-INTERAFAS-Operational-Session: missing');
    header('X-INTERAFAS-Validation: UPSLP_CNOIV-EYES-ON-THE-PLANT-17');
    lab_event(
        'VULN17_MONITOR_WITHOUT_OPERATOR_AUTH',
        'Telemetría operacional consultada sin sesión de operador',
        '/operations/monitor.php',
        [
            'challenge'=>17,
            'network_check'=>true,
            'operational_identity_checked'=>false,
            'plant'=>'Planta Metropolitana Norte'
        ],
        'ot-hmi',
        'warning',
        5
    );
}
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Monitor operacional · INTERAFAS</title>
<style>
body{font-family:system-ui;background:#07111f;color:#dce8f5;margin:0}.wrap{max-width:1100px;margin:auto;padding:30px}
small,.muted{color:#8ea7bf}.metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:22px}
.card{background:#0d1b2d;border:1px solid #183754;border-radius:16px;padding:20px}.card span{display:block;color:#8ea7bf}.card strong{display:block;font-size:28px;margin-top:7px}
.panel{margin-top:18px;background:#0d1b2d;border:1px solid #183754;border-radius:16px;padding:20px}.ok{color:#75e0a7}
@media(max-width:760px){.metrics{grid-template-columns:1fr 1fr}}@media(max-width:480px){.metrics{grid-template-columns:1fr}}
</style></head><body><div class="wrap">
<small>INTERAFAS · Supervisión operacional</small>
<h1>Monitor de proceso</h1>
<p class="muted">Planta Metropolitana Norte · RTU-GW-07 · Vista de sólo lectura</p>
<div class="metrics">
<div class="card"><span>Nivel TK-01</span><strong><?=htmlspecialchars((string)($state['tank']??'—'))?>%</strong></div>
<div class="card"><span>Caudal FLOW-01</span><strong><?=htmlspecialchars((string)($state['flow']??'—'))?> L/s</strong></div>
<div class="card"><span>Presión</span><strong><?=htmlspecialchars((string)($state['pressure']??'—'))?> bar</strong></div>
<div class="card"><span>Calidad</span><strong><?=htmlspecialchars((string)($state['quality']??'—'))?></strong></div>
</div>
<div class="panel"><b>Estado de equipos</b><p>P-101: <?=htmlspecialchars((string)($state['p101']??'—'))?> · P-102: <?=htmlspecialchars((string)($state['p102']??'—'))?> · V-201: <?=htmlspecialchars((string)($state['v201']??'—'))?></p>
<p>Firmware RTU-GW-07: <?=htmlspecialchars((string)($fw['version']??'—'))?></p>
<p><?=empty($state['alarms'])?'<span class="ok">Sin alarmas activas</span>':htmlspecialchars(implode(', ',$state['alarms']))?></p></div>
</div></body></html>
