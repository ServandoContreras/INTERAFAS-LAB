<?php
require __DIR__.'/common.php';
require_operational_network(true);
$ops=require_operational_auth();

lab_event(
    'OT_HMI_ACCESS',
    'Acceso autenticado al HMI operacional',
    '/operations/',
    [
        'operator'=>$ops['username']??'',
        'role'=>$ops['role']??'',
        'plant'=>'Planta Metropolitana Norte'
    ],
    'ot-hmi',
    'notice',
    5
);

$state=ot_call('/state');
$fw=$state['firmware']??[];
$assets=[
    ['id'=>'PLC-01','type'=>'PLC','zone'=>'Control Cell 01','function'=>'Process control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'P-101 · P-102 · V-201'],
    ['id'=>'RTU-GW-07','type'=>'RTU / Gateway','zone'=>'OT Edge','function'=>'Remote interface','status'=>'ONLINE','vendor'=>'Remote terminal gateway','related'=>'PLC-01 · HMI-OPS-01'],
    ['id'=>'HMI-OPS-01','type'=>'HMI','zone'=>'Control Room','function'=>'Operations console','status'=>'ONLINE','vendor'=>'Operator workstation','related'=>'PLC-01 · HIST-01'],
    ['id'=>'HIST-01','type'=>'Historian','zone'=>'OT Services','function'=>'Process data services','status'=>'ONLINE','vendor'=>'Industrial historian','related'=>'HMI-OPS-01 · PLC-01'],
    ['id'=>'EWS-01','type'=>'Engineering Workstation','zone'=>'Engineering','function'=>'Control engineering','status'=>'ONLINE','vendor'=>'Engineering station','related'=>'PLC-01 · BRS-01'],
    ['id'=>'BRS-01','type'=>'Backup / Recovery','zone'=>'OT Services','function'=>'Configuration recovery','status'=>'ONLINE','vendor'=>'Recovery services','related'=>'EWS-01 · RTU-GW-07']
];
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Operations Management Interface · INTERAFAS</title>
<link rel="stylesheet" href="/operations/assets/hmi.css">
</head>
<body>
<div class="hmi-app">
<header class="hmi-topbar">
  <div class="hmi-brand">
    <div class="hmi-mark">IA</div>
    <div class="hmi-brand-text"><strong>INTERAFAS</strong><span>OPERATIONS MANAGEMENT INTERFACE</span></div>
  </div>
  <div class="hmi-top-status">
    <div class="status-cluster"><span class="status-dot"></span> OPS-NET-20</div>
    <div class="status-cluster"><span class="status-dot"></span> PLANT RUNNING</div>
    <div class="status-cluster"><span class="status-dot <?=strtoupper((string)($fw['diagnostic']??''))==='SERVICE'?'warn':''?>"></span> <?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></div>
  </div>
  <div class="hmi-operator">
    <div class="who"><strong><?=htmlspecialchars((string)($ops['display_name']??$ops['username']??'Operador'))?></strong><span><?=htmlspecialchars((string)($ops['role']??'operator'))?> · <b data-ops-clock>--:--:--</b></span></div>
    <a href="logout.php">Cerrar sesión</a>
  </div>
</header>

<aside class="hmi-sidebar">
  <button class="nav-item active" data-view-target="overview"><span class="nav-glyph">▣</span><span>Overview</span></button>
  <div class="nav-section">PROCESS</div>
  <button class="nav-item" data-view-target="process"><span class="nav-glyph">◉</span><span>Process View</span></button>
  <button class="nav-item" data-view-target="trends"><span class="nav-glyph">⌁</span><span>Trends</span></button>
  <button class="nav-item" data-view-target="alarms"><span class="nav-glyph">!</span><span>Alarms</span></button>
  <div class="nav-section">SYSTEM</div>
  <button class="nav-item" data-view-target="assets"><span class="nav-glyph">◫</span><span>Assets</span></button>
  <button class="nav-item" data-view-target="network"><span class="nav-glyph">⬡</span><span>Network</span></button>
  <button class="nav-item" data-view-target="automation"><span class="nav-glyph">⟳</span><span>Automation</span></button>
  <button class="nav-item" data-view-target="maintenance"><span class="nav-glyph">◇</span><span>Maintenance</span></button>
  <div class="nav-section">ENGINEERING</div>
  <button class="nav-item" data-view-target="configuration"><span class="nav-glyph">▤</span><span>Configuration</span></button>
  <a class="nav-item" href="firmware.php"><span class="nav-glyph">▱</span><span>Firmware</span></a>
  <div class="nav-section">ANALYSIS</div>
  <button class="nav-item" data-view-target="events"><span class="nav-glyph">≡</span><span>Events</span></button>
</aside>

<main class="hmi-main"><div class="workspace">

<section class="view active" data-view="overview">
  <div class="page-head"><div><div class="breadcrumb">Operations / Overview</div><h1>Planta Metropolitana Norte</h1><p>Vista operacional consolidada · Cell-01 · actualización cada 5 s</p></div><div class="page-tools"><span class="tool-chip">MODE AUTO</span><span class="tool-chip">0 ACTIVE ALARMS</span></div></div>

  <div class="metrics">
    <div class="metric"><label>TK-01 Level</label><strong id="tank"><?=htmlspecialchars((string)($state['tank']??'—'))?></strong><small>%</small><span class="sub">Process variable</span></div>
    <div class="metric"><label>FLOW-01</label><strong id="flow"><?=htmlspecialchars((string)($state['flow']??'—'))?></strong><small>L/s</small><span class="sub">Distribution flow</span></div>
    <div class="metric"><label>PRESSURE-01</label><strong id="pressure"><?=htmlspecialchars((string)($state['pressure']??'—'))?></strong><small>bar</small><span class="sub">Header pressure</span></div>
    <div class="metric"><label>Water Quality</label><strong id="quality"><?=htmlspecialchars((string)($state['quality']??'—'))?></strong><span class="sub">Quality state</span></div>
    <div class="metric"><label>Primary Pump</label><strong id="p101"><?=htmlspecialchars((string)($state['p101']??'—'))?></strong><span class="sub">P-101 · AUTO</span></div>
  </div>

  <div class="grid-main">
    <section class="panel">
      <div class="panel-head"><h2>Process Overview · Cell-01</h2><span>P&ID simplified operational view</span></div>
      <div class="process-canvas">
        <div class="pid">
          <div class="pid-line main flow"></div><div class="pid-line branch"></div><div class="pid-line secondary"></div>
          <button class="equip tank" data-asset="TK-01"><span class="tag">TK-01</span><div class="tank-level"><span></span></div><div class="state"><?=htmlspecialchars((string)($state['tank']??'—'))?>%</div></button>
          <button class="equip pump p101 active" data-asset="P-101"><span><span class="tag">P-101</span><span class="state" id="p101-pid"><?=htmlspecialchars((string)($state['p101']??'—'))?></span></span></button>
          <button class="equip pump p102" data-asset="P-102"><span><span class="tag">P-102</span><span class="state" id="p102"><?=htmlspecialchars((string)($state['p102']??'—'))?></span></span></button>
          <div class="instrument flow"><div><span class="tag">FLOW-01</span><strong><?=htmlspecialchars((string)($state['flow']??'—'))?></strong></div></div>
          <div class="instrument pressure"><div><span class="tag">PRESS-01</span><strong><?=htmlspecialchars((string)($state['pressure']??'—'))?></strong></div></div>
          <button class="equip valve" data-asset="V-201"><span><span class="tag">V-201</span><span class="state" id="v201"><?=htmlspecialchars((string)($state['v201']??'—'))?></span></span></button>
          <div class="destination">DISTRIBUTION HEADER</div>
        </div>
      </div>
    </section>

    <aside class="inspector">
      <section class="panel">
        <div class="panel-head"><h2>Asset Inspector</h2><span id="inspector-zone">Control Cell 01</span></div>
        <div class="panel-body" id="asset-inspector">
          <div class="kv"><span>Asset</span><strong id="inspector-id">P-101</strong></div>
          <div class="kv"><span>Type</span><strong id="inspector-type">Centrifugal Pump</strong></div>
          <div class="kv"><span>Controller</span><strong id="inspector-controller">PLC-01</strong></div>
          <div class="kv"><span>Mode</span><strong>AUTO</strong></div>
          <div class="kv"><span>Status</span><strong class="ok-text" id="inspector-state"><?=htmlspecialchars((string)($state['p101']??'—'))?></strong></div>
          <div><span class="muted" style="font-size:9px">PROCESS TAGS</span><div class="tag-list" id="inspector-tags"><span class="tag-pill">P101.RUN</span><span class="tag-pill">P101.CMD</span><span class="tag-pill">P101.CURRENT</span></div></div>
          <div class="actions"><button class="btn" data-state="ON">START</button><button class="btn danger" data-state="OFF">STOP</button></div>
        </div>
      </section>
      <section class="panel">
        <div class="panel-head"><h2>Active Alarms</h2><span>Cell-01</span></div>
        <div class="panel-body alarm-list" id="alarms"><?=empty($state['alarms'])?'<div class="alarm-row"><span>No active process alarms</span><strong class="ok-text">NORMAL</strong></div>':'<div class="alarm-row"><span>'.htmlspecialchars(implode(', ',$state['alarms'])).'</span><strong class="danger-text">ACTIVE</strong></div>'?></div>
      </section>
      <section class="panel">
        <div class="panel-head"><h2>RTU-GW-07</h2><span>OT Edge</span></div>
        <div class="panel-body"><pre class="fw-pre" id="fw">Firmware <?=htmlspecialchars((string)($fw['version']??'—'))?>
Mode <?=htmlspecialchars((string)($fw['mode']??'—'))?>
Diagnostic <?=htmlspecialchars((string)($fw['diagnostic']??'—'))?></pre><div id="fw-update" class="muted" style="font-size:9px">Consultando canal de actualización…</div></div>
      </section>
    </aside>
  </div>
</section>

<section class="view" data-view="process">
  <div class="page-head"><div><div class="breadcrumb">Process / Process View</div><h1>Cell-01 Process Detail</h1><p>Activos físicos, señales y estado de control asociado.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Control Loop Context</h2><span>PMN_WATER_CELL_01</span></div><div class="panel-body">
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Tag</th><th>Asset</th><th>Class</th><th>Value</th><th>Source</th></tr></thead><tbody>
      <tr><td class="asset-link">TK01.LEVEL</td><td>TK-01</td><td>Analog input</td><td><?=htmlspecialchars((string)($state['tank']??'—'))?> %</td><td>PLC-01</td></tr>
      <tr><td class="asset-link">FLOW01.PV</td><td>FLOW-01</td><td>Process value</td><td><?=htmlspecialchars((string)($state['flow']??'—'))?> L/s</td><td>PLC-01</td></tr>
      <tr><td class="asset-link">PRESSURE01.PV</td><td>PRESS-01</td><td>Process value</td><td><?=htmlspecialchars((string)($state['pressure']??'—'))?> bar</td><td>PLC-01</td></tr>
      <tr><td class="asset-link">P101.RUN</td><td>P-101</td><td>Discrete state</td><td><?=htmlspecialchars((string)($state['p101']??'—'))?></td><td>PLC-01</td></tr>
      <tr><td class="asset-link">V201.POSITION</td><td>V-201</td><td>Discrete state</td><td><?=htmlspecialchars((string)($state['v201']??'—'))?></td><td>PLC-01</td></tr>
    </tbody></table></div>
  </div></div>
</section>

<section class="view" data-view="trends">
  <div class="page-head"><div><div class="breadcrumb">Process / Trends</div><h1>Process Trends</h1><p>Ventana temporal de variables críticas.</p></div><div class="page-tools"><span class="tool-chip">15 MIN</span><span class="tool-chip">LIVE</span></div></div>
  <div class="trend-grid">
    <div class="trend"><div class="trend-head"><strong>FLOW01.PV</strong><span><?=htmlspecialchars((string)($state['flow']??'—'))?> L/s</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line" d="M0 78 C60 70 90 75 140 64 S220 56 280 61 S360 49 420 53 S520 47 600 44"/></svg></div>
    <div class="trend"><div class="trend-head"><strong>PRESSURE01.PV</strong><span><?=htmlspecialchars((string)($state['pressure']??'—'))?> bar</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line2" d="M0 62 C80 58 120 65 190 60 S300 55 360 58 S480 52 600 55"/></svg></div>
    <div class="trend"><div class="trend-head"><strong>TK01.LEVEL</strong><span><?=htmlspecialchars((string)($state['tank']??'—'))?> %</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line" d="M0 45 C120 48 180 44 260 50 S390 56 470 51 S550 49 600 52"/></svg></div>
    <div class="trend"><div class="trend-head"><strong>P101.CURRENT</strong><span>RUNNING</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line2" d="M0 75 L40 74 L44 35 L160 37 L165 42 L310 40 L315 36 L600 38"/></svg></div>
  </div>
</section>

<section class="view" data-view="alarms">
  <div class="page-head"><div><div class="breadcrumb">Process / Alarms</div><h1>Alarm Management</h1><p>Estado actual y contexto operacional.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Active Alarm Queue</h2><span><?=count($state['alarms']??[])?> active</span></div><div class="panel-body alarm-list">
    <?=empty($state['alarms'])?'<div class="alarm-row"><span>Plant process conditions within configured limits</span><strong class="ok-text">NORMAL</strong></div>':'<div class="alarm-row"><span>'.htmlspecialchars(implode(', ',$state['alarms'])).'</span><strong class="danger-text">ACTIVE</strong></div>'?>
  </div></div>
</section>

<section class="view" data-view="assets">
  <div class="page-head"><div><div class="breadcrumb">System / Assets</div><h1>OT Asset Inventory</h1><p>Contexto de activos observados en la red operacional.</p></div><div class="page-tools"><span class="tool-chip">6 SYSTEMS</span><span class="tool-chip">CELL-01</span></div></div>
  <div class="asset-layout">
    <div class="panel"><div class="panel-head"><h2>Observed Assets</h2><span>Operational inventory</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Asset</th><th>Type</th><th>Zone</th><th>Function</th><th>Status</th></tr></thead><tbody>
    <?php foreach($assets as $a): ?><tr data-asset-row='<?=htmlspecialchars(json_encode($a,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),ENT_QUOTES)?>'><td class="asset-link"><?=htmlspecialchars($a['id'])?></td><td><?=htmlspecialchars($a['type'])?></td><td><?=htmlspecialchars($a['zone'])?></td><td><?=htmlspecialchars($a['function'])?></td><td class="state-online"><?=htmlspecialchars($a['status'])?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
    <aside class="panel asset-detail"><div class="panel-head"><h2>Asset Context</h2><span>Selected system</span></div><div class="panel-body">
      <h3 id="asset-detail-id">PLC-01</h3><div class="type" id="asset-detail-type">PLC</div>
      <div class="kv"><span>Zone</span><strong id="asset-detail-zone">Control Cell 01</strong></div>
      <div class="kv"><span>Function</span><strong id="asset-detail-function">Process control</strong></div>
      <div class="kv"><span>Platform</span><strong id="asset-detail-vendor">Industrial control platform</strong></div>
      <div class="kv"><span>Related systems</span><strong id="asset-detail-related">P-101 · P-102 · V-201</strong></div>
      <p class="muted" style="font-size:9px;line-height:1.6">El inventario muestra contexto operacional; la función específica de cada clase de activo forma parte del análisis del entorno.</p>
    </div></aside>
  </div>
</section>

<section class="view" data-view="network">
  <div class="page-head"><div><div class="breadcrumb">System / Network</div><h1>Zones & Conduits</h1><p>Relaciones lógicas observadas entre sistemas operacionales.</p></div></div>
  <div class="network-layout">
    <div class="panel"><div class="panel-head"><h2>Operational Topology</h2><span>Logical view</span></div><div class="zone-map">
      <div class="zone"><div class="zone-title">Enterprise / Application Boundary</div><div class="zone-assets"><div class="node"><strong>INTERAFAS-WEB</strong>Application services</div></div></div>
      <div class="conduit">▼ TRUST BOUNDARY · WEB-TO-OPERATIONS ▼</div>
      <div class="zone"><div class="zone-title">OT Edge · OPS-NET-20</div><div class="zone-assets"><div class="node"><strong>RTU-GW-07</strong>Remote terminal gateway</div><div class="node"><strong>HMI-OPS-01</strong>Operator interface</div></div></div>
      <div class="conduit">▼ OT SERVICES CONDUIT ▼</div>
      <div class="zone"><div class="zone-title">OT Services / Engineering</div><div class="zone-assets"><div class="node"><strong>HIST-01</strong>Process data services</div><div class="node"><strong>EWS-01</strong>Engineering workstation</div><div class="node"><strong>BRS-01</strong>Backup / recovery</div></div></div>
      <div class="conduit">▼ CONTROL CONDUIT ▼</div>
      <div class="zone"><div class="zone-title">Control Cell 01</div><div class="zone-assets"><div class="node"><strong>PLC-01</strong>Process control</div><div class="node"><strong>P-101</strong>Main pump</div><div class="node"><strong>P-102</strong>Standby pump</div><div class="node"><strong>V-201</strong>Distribution valve</div></div></div>
    </div></div>
    <aside class="panel"><div class="panel-head"><h2>Network Context</h2><span>OPS-NET-20</span></div><div class="panel-body">
      <div class="kv"><span>Segment</span><strong>10.40.20.0/24</strong></div><div class="kv"><span>Zone</span><strong>Operations</strong></div><div class="kv"><span>Gateway</span><strong>RTU-GW-07</strong></div><div class="kv"><span>Control domain</span><strong>Cell-01</strong></div><div class="kv"><span>Observed systems</span><strong>6</strong></div>
    </div></aside>
  </div>
</section>

<section class="view" data-view="automation">
  <div class="page-head"><div><div class="breadcrumb">System / Automation</div><h1>Maintenance Automation</h1><p>Tareas programadas para validación y mantenimiento operacional.</p></div><div class="page-tools"><span class="tool-chip">ENGINE READY</span></div></div>
  <div class="automation-layout">
    <div class="panel"><div class="panel-head"><h2>Scheduled Jobs</h2><span>Maintenance scheduler</span></div><div class="panel-body">
      <div class="job-card"><div class="job-top"><div><h3>Daily historian health check</h3><div class="job-meta">HIST-01 · Daily 02:00 · health-check</div></div><div class="job-state">COMPLETED</div></div></div>
      <div class="job-card"><div class="job-top"><div><h3>RTU configuration verification</h3><div class="job-meta">RTU-GW-07 · Daily 04:00 · config-verify</div></div><div class="job-state">COMPLETED</div></div></div>
      <div class="job-card"><div class="job-top"><div><h3>Recovery catalog validation</h3><div class="job-meta">BRS-01 · Sunday 03:00 · recovery-verify</div></div><div class="job-state">SCHEDULED</div></div></div>
      <div class="empty-note">Nueva automatización de mantenimiento disponible durante ventanas de servicio.</div>
    </div></div>
    <aside class="panel"><div class="panel-head"><h2>Automation Engine</h2><span>OT-AUTO-01</span></div><div class="panel-body">
      <div class="kv"><span>State</span><strong class="ok-text">READY</strong></div><div class="kv"><span>Execution mode</span><strong>Scheduled / Manual</strong></div><div class="kv"><span>Maintenance mode</span><strong><?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></strong></div><div class="kv"><span>Audit profile</span><strong>Standard</strong></div>
      <p class="muted" style="font-size:9px;line-height:1.6">Las tareas se representan como operaciones de mantenimiento sobre activos de la red operacional. La creación de nuevos trabajos se habilitará en la siguiente fase del escenario.</p>
    </div></aside>
  </div>
</section>

<section class="view" data-view="maintenance">
  <div class="page-head"><div><div class="breadcrumb">System / Maintenance</div><h1>Maintenance Context</h1><p>Estado de activos sujetos a actividades de servicio.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Service State</h2><span>RTU-GW-07</span></div><div class="panel-body"><div class="kv"><span>Firmware</span><strong><?=htmlspecialchars((string)($fw['version']??'—'))?></strong></div><div class="kv"><span>Mode</span><strong><?=htmlspecialchars((string)($fw['mode']??'—'))?></strong></div><div class="kv"><span>Diagnostic</span><strong><?=htmlspecialchars((string)($fw['diagnostic']??'—'))?></strong></div><div class="kv"><span>Automation engine</span><strong>READY</strong></div></div></div>
</section>

<section class="view" data-view="configuration">
  <div class="page-head"><div><div class="breadcrumb">Engineering / Configuration</div><h1>Configuration Context</h1><p>Información de ingeniería visible para operación.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Control Project</h2><span>Read-only context</span></div><div class="panel-body"><div class="kv"><span>Project</span><strong>PMN_WATER_CONTROL</strong></div><div class="kv"><span>Controller</span><strong>PLC-01</strong></div><div class="kv"><span>Revision</span><strong>2026.09.14-r17</strong></div><div class="kv"><span>Engineering station</span><strong>EWS-01</strong></div><div class="kv"><span>Recovery service</span><strong>BRS-01</strong></div></div></div>
</section>

<section class="view" data-view="events">
  <div class="page-head"><div><div class="breadcrumb">Analysis / Events</div><h1>Operational Event Timeline</h1><p>Eventos recientes visibles para la sesión.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Timeline</h2><span>Current session</span></div><div class="panel-body"><div class="alarm-row"><span>HMI-OPS-01 · Operator interface loaded</span><strong class="ok-text">INFO</strong></div><div class="alarm-row"><span>RTU-GW-07 · Firmware state synchronized</span><strong class="ok-text">INFO</strong></div><div class="alarm-row"><span id="eventlog">No operator actions in current view.</span><strong>SESSION</strong></div></div></div>
</section>

<div class="footer-note">Entorno operacional simulado con fines académicos. Ninguna acción se comunica con infraestructura física.</div>
</div></main>
</div>

<script src="/operations/assets/ops-client.php"></script>
<script>
const assetContext={
  'P-101':{type:'Centrifugal Pump',zone:'Control Cell 01',controller:'PLC-01',tags:['P101.RUN','P101.CMD','P101.CURRENT']},
  'P-102':{type:'Standby Pump',zone:'Control Cell 01',controller:'PLC-01',tags:['P102.RUN','P102.CMD','P102.AVAILABLE']},
  'V-201':{type:'Control Valve',zone:'Control Cell 01',controller:'PLC-01',tags:['V201.POSITION','V201.CMD']},
  'TK-01':{type:'Process Tank',zone:'Control Cell 01',controller:'PLC-01',tags:['TK01.LEVEL','TK01.HIGH','TK01.LOW']}
};
document.querySelectorAll('[data-view-target]').forEach(btn=>btn.addEventListener('click',()=>{
  document.querySelectorAll('.nav-item[data-view-target]').forEach(x=>x.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.view').forEach(v=>v.classList.toggle('active',v.dataset.view===btn.dataset.viewTarget));
}));
document.querySelectorAll('[data-asset]').forEach(btn=>btn.addEventListener('click',()=>{
  document.querySelectorAll('[data-asset]').forEach(x=>x.classList.remove('active'));btn.classList.add('active');
  const id=btn.dataset.asset,c=assetContext[id];if(!c)return;
  document.getElementById('inspector-id').textContent=id;
  document.getElementById('inspector-type').textContent=c.type;
  document.getElementById('inspector-zone').textContent=c.zone;
  document.getElementById('inspector-controller').textContent=c.controller;
  document.getElementById('inspector-state').textContent=(id==='P-101'?document.getElementById('p101').textContent:id==='P-102'?document.getElementById('p102').textContent:id==='V-201'?document.getElementById('v201').textContent:'ACTIVE');
  document.getElementById('inspector-tags').innerHTML=c.tags.map(t=>'<span class="tag-pill">'+t+'</span>').join('');
}));
document.querySelectorAll('[data-asset-row]').forEach(row=>row.addEventListener('click',()=>{
  const a=JSON.parse(row.dataset.assetRow);
  document.getElementById('asset-detail-id').textContent=a.id;
  document.getElementById('asset-detail-type').textContent=a.type;
  document.getElementById('asset-detail-zone').textContent=a.zone;
  document.getElementById('asset-detail-function').textContent=a.function;
  document.getElementById('asset-detail-vendor').textContent=a.vendor;
  document.getElementById('asset-detail-related').textContent=a.related;
}));
async function refreshTelemetry(){
  try{
    const r=await fetch(window.INTERAFAS_OPS.telemetryEndpoint,{cache:'no-store'}),d=await r.json();if(!d.ok)return;
    document.getElementById('tank').textContent=d.tank;
    document.getElementById('flow').textContent=d.flow;
    document.getElementById('pressure').textContent=d.pressure;
    document.getElementById('quality').textContent=d.quality;
    document.getElementById('p101').textContent=d.p101;
    document.getElementById('p101-pid').textContent=d.p101;
    document.getElementById('p102').textContent=d.p102;
    document.getElementById('v201').textContent=d.v201;
    document.getElementById('alarms').innerHTML=d.alarms.length?'<div class="alarm-row"><span>'+d.alarms.join(', ')+'</span><strong class="danger-text">ACTIVE</strong></div>':'<div class="alarm-row"><span>No active process alarms</span><strong class="ok-text">NORMAL</strong></div>';
    if(d.firmware)document.getElementById('fw').textContent='Firmware '+(d.firmware.version??'—')+'\nMode '+(d.firmware.mode??'—')+'\nDiagnostic '+(d.firmware.diagnostic??'—');
  }catch(e){}
}
async function refreshFirmwareStatus(){
  try{
    const r=await fetch(window.INTERAFAS_OPS.firmwareStatusEndpoint,{cache:'no-store'}),d=await r.json(),node=document.getElementById('fw-update');if(!node)return;
    node.textContent=d.ok&&d.update?.available?'Update '+(d.update.candidate_version??'—')+' available · '+(d.channel??'—'):'Firmware channel synchronized';
  }catch(e){}
}
async function control(state){
  const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'p101',state})}),d=await r.json();
  const log=document.getElementById('eventlog');if(log)log.textContent=(d.message||d.error||'Evento')+' · '+new Date().toLocaleTimeString();
  if(d.process){document.getElementById('p101').textContent=d.process.p101;document.getElementById('p101-pid').textContent=d.process.p101;document.getElementById('flow').textContent=d.process.flow;document.getElementById('pressure').textContent=d.process.pressure;}
}
document.querySelectorAll('[data-state]').forEach(b=>b.addEventListener('click',()=>control(b.dataset.state)));
refreshFirmwareStatus();
setInterval(refreshTelemetry,window.INTERAFAS_OPS.refreshInterval);
setInterval(refreshFirmwareStatus,window.INTERAFAS_OPS.firmwareRefreshInterval);
</script>
</body></html>
