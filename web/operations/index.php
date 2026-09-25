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
        'plant'=>'Red Metropolitana INTERAFAS'
    ],
    'ot-hmi',
    'notice',
    5
);

$state=ot_call('/state');
$fw=$state['firmware']??[];
$canControl=(string)($ops['role']??'')==='operator';

$stations=[
    ['id'=>'EST-CP-01','city'=>'Cerro de San Pablo','name'=>'Estación Cerro Norte','function'=>'Pumping / distribution','supply'=>910,'demand'=>860,'reserve'=>79,'pressure'=>4.1,'quality'=>'NORMAL','alarms'=>0,'controller'=>'PLC-CP-01','gateway'=>'RTU-CP-01','status'=>'ONLINE'],
    ['id'=>'EST-SL-01','city'=>'Saint Louis','name'=>'Estación Central Saint Louis','function'=>'Regulation / distribution','supply'=>1130,'demand'=>1070,'reserve'=>84,'pressure'=>4.5,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-SL-01','gateway'=>'RTU-SL-01','status'=>'ONLINE'],
    ['id'=>'EST-SO-01','city'=>'Soledade','name'=>'Estación Oriente Soledade','function'=>'Pumping / distribution','supply'=>800,'demand'=>680,'reserve'=>81,'pressure'=>4.3,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-SO-01','gateway'=>'RTU-SO-01','status'=>'ONLINE']
];

$assets=[
    ['id'=>'HMI-OPS-01','type'=>'HMI','station'=>'Metropolitan','zone'=>'Control Room','function'=>'Operations console','status'=>'ONLINE','vendor'=>'Operator workstation','related'=>'HIST-01 · RTU-GW-07'],
    ['id'=>'HIST-01','type'=>'Historian','station'=>'Metropolitan','zone'=>'OT Services','function'=>'Process data services','status'=>'ONLINE','vendor'=>'Industrial historian','related'=>'PLC-CP-01 · PLC-SL-01 · PLC-SO-01'],
    ['id'=>'EWS-01','type'=>'Engineering Workstation','station'=>'Metropolitan','zone'=>'Engineering','function'=>'Control engineering','status'=>'ONLINE','vendor'=>'Engineering station','related'=>'PLC-CP-01 · PLC-SL-01 · PLC-SO-01 · BRS-01'],
    ['id'=>'BRS-01','type'=>'Backup / Recovery','station'=>'Metropolitan','zone'=>'OT Services','function'=>'Configuration recovery','status'=>'ONLINE','vendor'=>'Recovery services','related'=>'EWS-01 · RTU-GW-07'],
    ['id'=>'OT-AUTO-01','type'=>'Automation Engine','station'=>'Metropolitan','zone'=>'OT Services','function'=>'Maintenance automation','status'=>'ONLINE','vendor'=>'Automation service','related'=>'HIST-01 · EWS-01 · BRS-01'],
    ['id'=>'RTU-GW-07','type'=>'RTU / Gateway','station'=>'Metropolitan','zone'=>'OT Edge','function'=>'Remote interface','status'=>'ONLINE','vendor'=>'Remote terminal gateway','related'=>'HMI-OPS-01 · OT Services'],
    ['id'=>'PLC-CP-01','type'=>'PLC','station'=>'Cerro de San Pablo','zone'=>'Area Control','function'=>'Process control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'RTU-CP-01 · P-CP-101 · P-CP-102 · V-CP-201'],
    ['id'=>'RTU-CP-01','type'=>'RTU','station'=>'Cerro de San Pablo','zone'=>'OT Edge','function'=>'Station communications','status'=>'ONLINE','vendor'=>'Remote terminal unit','related'=>'PLC-CP-01 · RTU-GW-07'],
    ['id'=>'TK-CP-01','type'=>'Reservoir','station'=>'Cerro de San Pablo','zone'=>'Process','function'=>'Regulation storage','status'=>'ONLINE','vendor'=>'Process asset','related'=>'PLC-CP-01'],
    ['id'=>'P-CP-101','type'=>'Pump','station'=>'Cerro de San Pablo','zone'=>'Process','function'=>'Primary pumping','status'=>'RUNNING','vendor'=>'Field equipment','related'=>'PLC-CP-01'],
    ['id'=>'P-CP-102','type'=>'Pump','station'=>'Cerro de San Pablo','zone'=>'Process','function'=>'Standby pumping','status'=>'STANDBY','vendor'=>'Field equipment','related'=>'PLC-CP-01'],
    ['id'=>'V-CP-201','type'=>'Control Valve','station'=>'Cerro de San Pablo','zone'=>'Process','function'=>'Distribution control','status'=>'OPEN','vendor'=>'Field equipment','related'=>'PLC-CP-01'],
    ['id'=>'PLC-SL-01','type'=>'PLC','station'=>'Saint Louis','zone'=>'Area Control','function'=>'Process control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'RTU-SL-01 · P-SL-101 · P-SL-102 · V-SL-201'],
    ['id'=>'RTU-SL-01','type'=>'RTU','station'=>'Saint Louis','zone'=>'OT Edge','function'=>'Station communications','status'=>'ONLINE','vendor'=>'Remote terminal unit','related'=>'PLC-SL-01 · RTU-GW-07'],
    ['id'=>'TK-SL-01','type'=>'Reservoir','station'=>'Saint Louis','zone'=>'Process','function'=>'Regulation storage','status'=>'ONLINE','vendor'=>'Process asset','related'=>'PLC-SL-01'],
    ['id'=>'P-SL-101','type'=>'Pump','station'=>'Saint Louis','zone'=>'Process','function'=>'Primary pumping','status'=>'RUNNING','vendor'=>'Field equipment','related'=>'PLC-SL-01'],
    ['id'=>'P-SL-102','type'=>'Pump','station'=>'Saint Louis','zone'=>'Process','function'=>'Standby pumping','status'=>'STANDBY','vendor'=>'Field equipment','related'=>'PLC-SL-01'],
    ['id'=>'V-SL-201','type'=>'Control Valve','station'=>'Saint Louis','zone'=>'Process','function'=>'Zone A distribution','status'=>'OPEN','vendor'=>'Field equipment','related'=>'PLC-SL-01'],
    ['id'=>'V-SL-202','type'=>'Control Valve','station'=>'Saint Louis','zone'=>'Process','function'=>'Zone B distribution','status'=>'OPEN','vendor'=>'Field equipment','related'=>'PLC-SL-01'],
    ['id'=>'PLC-SO-01','type'=>'PLC','station'=>'Soledade','zone'=>'Area Control','function'=>'Process control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'RTU-SO-01 · P-SO-101 · P-SO-102 · V-SO-201'],
    ['id'=>'RTU-SO-01','type'=>'RTU','station'=>'Soledade','zone'=>'OT Edge','function'=>'Station communications','status'=>'ONLINE','vendor'=>'Remote terminal unit','related'=>'PLC-SO-01 · RTU-GW-07'],
    ['id'=>'TK-SO-01','type'=>'Reservoir','station'=>'Soledade','zone'=>'Process','function'=>'Regulation storage','status'=>'ONLINE','vendor'=>'Process asset','related'=>'PLC-SO-01'],
    ['id'=>'P-SO-101','type'=>'Pump','station'=>'Soledade','zone'=>'Process','function'=>'Primary pumping','status'=>'RUNNING','vendor'=>'Field equipment','related'=>'PLC-SO-01'],
    ['id'=>'P-SO-102','type'=>'Pump','station'=>'Soledade','zone'=>'Process','function'=>'Standby pumping','status'=>'STANDBY','vendor'=>'Field equipment','related'=>'PLC-SO-01'],
    ['id'=>'V-SO-201','type'=>'Control Valve','station'=>'Soledade','zone'=>'Process','function'=>'Distribution control','status'=>'OPEN','vendor'=>'Field equipment','related'=>'PLC-SO-01']
];

$totalSupply=array_sum(array_column($stations,'supply'));
$totalDemand=array_sum(array_column($stations,'demand'));
$avgReserve=round(array_sum(array_column($stations,'reserve'))/count($stations));
$avgPressure=round(array_sum(array_column($stations,'pressure'))/count($stations),1);
$totalAlarms=array_sum(array_column($stations,'alarms'));
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Metropolitan Water Operations · INTERAFAS</title>
<link rel="stylesheet" href="/operations/assets/hmi.css">
</head>
<body>
<div class="hmi-app">
<header class="hmi-topbar">
  <div class="hmi-brand">
    <div class="hmi-mark">IA</div>
    <div class="hmi-brand-text"><strong>INTERAFAS</strong><span>METROPOLITAN WATER OPERATIONS</span></div>
  </div>
  <div class="hmi-top-status">
    <div class="status-cluster"><span class="status-dot"></span> OPS-NET-20</div>
    <div class="status-cluster"><span class="status-dot"></span> 3 / 3 STATIONS ONLINE</div>
    <div class="status-cluster"><span class="status-dot <?=strtoupper((string)($fw['diagnostic']??''))==='SERVICE'?'warn':''?>"></span> <?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></div>
  </div>
  <div class="hmi-operator">
    <div class="who"><strong><?=htmlspecialchars((string)($ops['display_name']??$ops['username']??'Operador'))?></strong><span><?=htmlspecialchars((string)($ops['role']??'operator'))?> · <b data-ops-clock>--:--:--</b></span></div>
    <a href="logout.php">Cerrar sesión</a>
  </div>
</header>

<aside class="hmi-sidebar">
  <div class="nav-section">OPERATIONS</div>
  <button class="nav-item active" data-view-target="overview"><span class="nav-glyph">▣</span><span>Overview</span></button>
  <button class="nav-item" data-view-target="stations"><span class="nav-glyph">▦</span><span>Stations</span></button>
  <button class="nav-item" data-view-target="metropolitan"><span class="nav-glyph">⌘</span><span>Metropolitan Process</span></button>
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
  <?php if($canControl): ?>
  <a class="nav-item" href="firmware.php"><span class="nav-glyph">▱</span><span>Firmware</span></a>
  <?php else: ?>
  <button class="nav-item" data-view-target="maintenance"><span class="nav-glyph">▱</span><span>Firmware context</span></button>
  <?php endif; ?>
  <div class="nav-section">ANALYSIS</div>
  <button class="nav-item" data-view-target="events"><span class="nav-glyph">≡</span><span>Events</span></button>
</aside>

<main class="hmi-main"><div class="workspace">

<section class="view active" data-view="overview">
  <div class="page-head">
    <div><div class="breadcrumb">Operations / Metropolitan Overview</div><h1>Red Metropolitana de Abastecimiento</h1><p>Cerro de San Pablo · Saint Louis · Soledade · supervisión operacional consolidada</p></div>
    <div class="page-tools"><span class="tool-chip">MODE AUTO</span><span class="tool-chip"><?=htmlspecialchars(strtoupper((string)($ops['role']??'operator')))?></span><span class="tool-chip"><?=$totalAlarms?> ACTIVE ALARMS</span></div>
  </div>

  <div class="metrics metro-metrics">
    <div class="metric"><label>Total Supply</label><strong><?=$totalSupply?></strong><small>L/s</small><span class="sub">Metropolitan production</span></div>
    <div class="metric"><label>Total Demand</label><strong><?=$totalDemand?></strong><small>L/s</small><span class="sub">Current metropolitan demand</span></div>
    <div class="metric"><label>System Reserve</label><strong><?=$avgReserve?></strong><small>%</small><span class="sub">Average available reserve</span></div>
    <div class="metric"><label>Average Pressure</label><strong><?=$avgPressure?></strong><small>bar</small><span class="sub">Distribution headers</span></div>
    <div class="metric"><label>Water Quality</label><strong>NORMAL</strong><span class="sub">3 municipalities within limits</span></div>
    <div class="metric"><label>Stations</label><strong>3 / 3</strong><span class="sub">Metropolitan stations online</span></div>
    <div class="metric"><label>Critical Assets</label><strong><?=count($assets)?> / <?=count($assets)?></strong><span class="sub">Observed OT assets</span></div>
  </div>

  <div class="metro-overview-grid">
    <section class="panel">
      <div class="panel-head"><h2>Metropolitan Process Network</h2><span>Primary distribution topology</span></div>
      <div class="metro-canvas">
        <div class="metro-line trunk-v"></div>
        <div class="metro-line trunk-h"></div>
        <div class="metro-line branch cp"></div>
        <div class="metro-line branch sl"></div>
        <div class="metro-line branch so"></div>

        <button class="metro-node reservoir-node" data-view-jump="metropolitan"><strong>TQ-METRO-01</strong><span>Strategic reserve</span><b>81%</b></button>
        <button class="metro-node central-node" data-view-jump="metropolitan"><strong>PMN-CENTRAL</strong><span>Metropolitan regulation</span><b>1,420 L/s</b></button>
        <button class="metro-node gateway-node"><strong>RTU-GW-07</strong><span>OT Edge</span><b class="<?=strtoupper((string)($fw['diagnostic']??''))==='SERVICE'?'warn-text':'ok-text'?>"><?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></b></button>

        <?php foreach($stations as $i=>$station): ?>
        <button class="metro-node station-node station-<?=$i?>" data-station-open="<?=$i?>">
          <span class="city"><?=htmlspecialchars($station['city'])?></span>
          <strong><?=htmlspecialchars($station['id'])?></strong>
          <span><?=htmlspecialchars($station['name'])?></span>
          <b><?=htmlspecialchars((string)$station['supply'])?> L/s · <?=htmlspecialchars((string)$station['reserve'])?>%</b>
        </button>
        <?php endforeach; ?>

        <div class="metro-label trunk-label">TRUNK-MAIN-01</div>
        <div class="metro-label ops-label">METROPOLITAN OPERATIONS CORE</div>
      </div>
    </section>

    <aside class="situation-stack">
      <section class="panel">
        <div class="panel-head"><h2>Metropolitan Situation</h2><span>Current operational context</span></div>
        <div class="panel-body">
          <div class="event-row"><span class="event-time">11:39</span><div><strong>Saint Louis</strong><small>Pressure deviation +0.4 bar</small></div><b class="warn-text">MED</b></div>
          <div class="event-row"><span class="event-time">11:35</span><div><strong>RTU-GW-07</strong><small>Maintenance state <?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></small></div><b class="warn-text">SERVICE</b></div>
          <div class="event-row"><span class="event-time">11:21</span><div><strong>HIST-01</strong><small>Historian health check completed</small></div><b class="ok-text">INFO</b></div>
          <div class="event-row"><span class="event-time">11:08</span><div><strong>Soledade</strong><small>RTU latency threshold observed</small></div><b class="warn-text">LOW</b></div>
        </div>
      </section>
      <section class="panel">
        <div class="panel-head"><h2>Core Communications</h2><span>OT services</span></div>
        <div class="panel-body comm-list">
          <div><span>HMI-OPS-01</span><strong class="ok-text">ONLINE</strong></div>
          <div><span>HIST-01</span><strong class="ok-text">ONLINE</strong></div>
          <div><span>EWS-01</span><strong class="ok-text">ONLINE</strong></div>
          <div><span>BRS-01</span><strong class="ok-text">ONLINE</strong></div>
          <div><span>OT-AUTO-01</span><strong class="ok-text">READY</strong></div>
        </div>
      </section>
    </aside>
  </div>

  <div class="station-summary-grid">
    <?php foreach($stations as $i=>$station): ?>
    <article class="station-summary">
      <div class="station-card-head"><div><span><?=htmlspecialchars($station['city'])?></span><strong><?=htmlspecialchars($station['id'])?></strong></div><b class="ok-text"><?=htmlspecialchars($station['status'])?></b></div>
      <div class="station-values">
        <div><span>Supply</span><strong><?=htmlspecialchars((string)$station['supply'])?> L/s</strong></div>
        <div><span>Demand</span><strong><?=htmlspecialchars((string)$station['demand'])?> L/s</strong></div>
        <div><span>Reserve</span><strong><?=htmlspecialchars((string)$station['reserve'])?> %</strong></div>
        <div><span>Pressure</span><strong><?=htmlspecialchars((string)$station['pressure'])?> bar</strong></div>
      </div>
      <div class="station-footer"><span><?=htmlspecialchars($station['controller'])?> · <?=htmlspecialchars($station['gateway'])?></span><button class="btn station-open" data-station-open="<?=$i?>">OPEN STATION</button></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="view" data-view="stations">
  <div class="page-head"><div><div class="breadcrumb">Operations / Stations</div><h1>Metropolitan Stations</h1><p>Estado operacional de estaciones principales y controladores asociados.</p></div><div class="page-tools"><span class="tool-chip">3 STATIONS</span><span class="tool-chip">3 MUNICIPALITIES</span></div></div>
  <div class="station-list">
    <?php foreach($stations as $i=>$station): ?>
    <article class="station-large-card">
      <div class="station-card-head"><div><span><?=htmlspecialchars($station['city'])?></span><strong><?=htmlspecialchars($station['id'])?> · <?=htmlspecialchars($station['name'])?></strong></div><b class="ok-text"><?=htmlspecialchars($station['status'])?></b></div>
      <div class="station-large-grid">
        <div><span>Function</span><strong><?=htmlspecialchars($station['function'])?></strong></div>
        <div><span>Controller</span><strong><?=htmlspecialchars($station['controller'])?></strong></div>
        <div><span>Gateway</span><strong><?=htmlspecialchars($station['gateway'])?></strong></div>
        <div><span>Supply</span><strong><?=htmlspecialchars((string)$station['supply'])?> L/s</strong></div>
        <div><span>Demand</span><strong><?=htmlspecialchars((string)$station['demand'])?> L/s</strong></div>
        <div><span>Reserve</span><strong><?=htmlspecialchars((string)$station['reserve'])?> %</strong></div>
        <div><span>Pressure</span><strong><?=htmlspecialchars((string)$station['pressure'])?> bar</strong></div>
        <div><span>Alarms</span><strong class="<?=$station['alarms']?'warn-text':'ok-text'?>"><?=htmlspecialchars((string)$station['alarms'])?></strong></div>
      </div>
      <div class="station-actions"><button class="btn station-open" data-station-open="<?=$i?>">OPEN PROCESS VIEW</button></div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="view" data-view="metropolitan">
  <div class="page-head"><div><div class="breadcrumb">Operations / Metropolitan Process</div><h1>Metropolitan Distribution Topology</h1><p>Relación operacional entre reserva estratégica, regulación central y estaciones municipales.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Primary Hydraulic Topology</h2><span>Metropolitan abstraction</span></div>
    <div class="metro-detailed">
      <div class="hydraulic-row"><div class="hydro-box reservoir"><strong>TQ-METRO-01</strong><span>Strategic reserve</span><b>81%</b></div><div class="hydro-arrow">→</div><div class="hydro-box"><strong>PMN-CENTRAL</strong><span>Primary regulation</span><b>1,420 L/s</b></div><div class="hydro-arrow">→</div><div class="hydro-box service"><strong>RTU-GW-07</strong><span>Remote operations edge</span><b><?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></b></div></div>
      <div class="hydraulic-split">METROPOLITAN DISTRIBUTION TRUNK</div>
      <div class="hydraulic-branches">
        <?php foreach($stations as $station): ?>
        <div class="hydro-branch"><div class="branch-line"></div><div class="hydro-box"><strong><?=htmlspecialchars($station['id'])?></strong><span><?=htmlspecialchars($station['city'])?></span><b><?=htmlspecialchars((string)$station['supply'])?> L/s</b></div><div class="branch-assets">Tank · Primary pump · Standby pump · Distribution valve · Flow · Pressure</div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="view" data-view="process">
  <div class="page-head"><div><div class="breadcrumb">Process / Station Detail</div><h1 id="process-title">EST-SL-01 · Saint Louis</h1><p id="process-subtitle">Regulation / distribution · PLC-SL-01 · RTU-SL-01</p></div><div class="page-tools"><span class="tool-chip" id="process-mode">AUTO</span><span class="tool-chip">LOCAL DETAIL</span></div></div>

  <div class="process-detail-grid">
    <section class="panel">
      <div class="panel-head"><h2>Station Process</h2><span id="process-zone">Saint Louis distribution cell</span></div>
      <div class="station-process-canvas">
        <div class="station-pipe main"></div><div class="station-pipe branch-a"></div><div class="station-pipe branch-b"></div>
        <div class="station-tank"><strong id="proc-tank">TK-SL-01</strong><div class="tank-gauge"><span id="proc-tank-fill" style="height:84%"></span></div><b id="proc-reserve">84%</b></div>
        <div class="station-pump primary"><strong id="proc-pump1">P-SL-101</strong><span>RUNNING</span></div>
        <div class="station-pump standby"><strong id="proc-pump2">P-SL-102</strong><span>STANDBY</span></div>
        <div class="station-instrument flow"><strong id="proc-flow-tag">FLOW-SL-01</strong><span id="proc-flow">1130 L/s</span></div>
        <div class="station-instrument pressure"><strong id="proc-pressure-tag">PRESS-SL-01</strong><span id="proc-pressure">4.5 bar</span></div>
        <div class="station-valve valve-a"><strong id="proc-valve1">V-SL-201</strong><span>OPEN</span></div>
        <div class="station-valve valve-b"><strong id="proc-valve2">V-SL-202</strong><span>OPEN</span></div>
        <div class="station-destination a">ZONE A</div><div class="station-destination b">ZONE B</div>
      </div>
    </section>

    <aside class="inspector">
      <section class="panel"><div class="panel-head"><h2>Station Context</h2><span id="station-status">ONLINE</span></div><div class="panel-body">
        <div class="kv"><span>Station</span><strong id="station-id">EST-SL-01</strong></div>
        <div class="kv"><span>Municipality</span><strong id="station-city">Saint Louis</strong></div>
        <div class="kv"><span>Controller</span><strong id="station-controller">PLC-SL-01</strong></div>
        <div class="kv"><span>Gateway</span><strong id="station-gateway">RTU-SL-01</strong></div>
        <div class="kv"><span>Supply</span><strong id="station-supply">1130 L/s</strong></div>
        <div class="kv"><span>Demand</span><strong id="station-demand">1070 L/s</strong></div>
        <div class="kv"><span>Quality</span><strong class="ok-text">NORMAL</strong></div>
      </div></section>

      <section class="panel"><div class="panel-head"><h2>PMN Control Cell</h2><span>Legacy simulator context</span></div><div class="panel-body">
        <div class="kv"><span>Primary Pump</span><strong id="p101"><?=htmlspecialchars((string)($state['p101']??'—'))?></strong></div>
        <div class="kv"><span>Flow</span><strong id="flow"><?=htmlspecialchars((string)($state['flow']??'—'))?> L/s</strong></div>
        <div class="kv"><span>Pressure</span><strong id="pressure"><?=htmlspecialchars((string)($state['pressure']??'—'))?> bar</strong></div>
        <div class="kv"><span>Tank</span><strong id="tank"><?=htmlspecialchars((string)($state['tank']??'—'))?>%</strong></div>
        <?php if($canControl): ?>
        <div class="actions"><button class="btn" data-state="ON">START P-101</button><button class="btn danger" data-state="OFF">STOP P-101</button></div>
        <?php else: ?>
        <div class="actions"><span class="tool-chip">PROCESS CONTROL RESTRICTED</span></div>
        <?php endif; ?>
      </div></section>
    </aside>
  </div>
</section>

<section class="view" data-view="trends">
  <div class="page-head"><div><div class="breadcrumb">Process / Trends</div><h1>Metropolitan Trends</h1><p>Variables consolidadas por municipio y cabecera de distribución.</p></div><div class="page-tools"><span class="tool-chip">15 MIN</span><span class="tool-chip">METROPOLITAN</span><span class="tool-chip">LIVE</span></div></div>
  <div class="trend-grid">
    <div class="trend"><div class="trend-head"><strong>SUPPLY.TOTAL</strong><span><?=$totalSupply?> L/s</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line" d="M0 74 C60 68 100 72 150 61 S230 55 285 58 S365 46 430 50 S525 43 600 46"/></svg></div>
    <div class="trend"><div class="trend-head"><strong>DEMAND.TOTAL</strong><span><?=$totalDemand?> L/s</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line2" d="M0 82 C80 78 120 76 180 70 S280 62 340 66 S470 59 600 62"/></svg></div>
    <div class="trend"><div class="trend-head"><strong>PRESSURE.BY.CITY</strong><span>4.1 / 4.5 / 4.3 bar</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line" d="M0 67 C90 64 140 68 210 61 S320 58 390 60 S500 54 600 57"/><path class="line2" d="M0 78 C100 75 150 70 220 73 S340 66 430 69 S530 65 600 66"/></svg></div>
    <div class="trend"><div class="trend-head"><strong>RESERVOIR.LEVELS</strong><span>79 / 84 / 81 %</span></div><svg class="spark" viewBox="0 0 600 120" preserveAspectRatio="none"><path class="grid" d="M0 30H600M0 60H600M0 90H600"/><path class="line2" d="M0 52 C100 55 180 49 260 53 S390 59 480 52 S560 50 600 51"/></svg></div>
  </div>
</section>

<section class="view" data-view="alarms">
  <div class="page-head"><div><div class="breadcrumb">Process / Alarms</div><h1>Metropolitan Alarm Management</h1><p>Condiciones activas y eventos operacionales por estación.</p></div><div class="page-tools"><span class="tool-chip"><?=$totalAlarms?> ACTIVE</span><span class="tool-chip">0 CRITICAL</span></div></div>
  <div class="panel"><div class="panel-head"><h2>Active Alarm Queue</h2><span>Priority ordered</span></div><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Time</th><th>Priority</th><th>Station</th><th>Source</th><th>Condition</th><th>State</th></tr></thead>
    <tbody>
      <tr><td>11:39:12</td><td class="warn-text">MEDIUM</td><td>Saint Louis</td><td>PRESS-SL-01</td><td>Pressure deviation +0.4 bar</td><td>ACTIVE</td></tr>
      <tr><td>11:08:44</td><td class="warn-text">LOW</td><td>Soledade</td><td>RTU-SO-01</td><td>Communication latency threshold</td><td>ACTIVE</td></tr>
      <tr><td>10:42:07</td><td>INFO</td><td>Metropolitan</td><td>RTU-GW-07</td><td>Maintenance state entered</td><td>ACK</td></tr>
    </tbody>
  </table></div></div>
</section>

<section class="view" data-view="assets">
  <div class="page-head"><div><div class="breadcrumb">System / Assets</div><h1>Metropolitan OT Asset Inventory</h1><p>Servicios OT, controladores y activos de campo observados en las tres estaciones.</p></div><div class="page-tools"><span class="tool-chip"><?=count($assets)?> ASSETS</span><span class="tool-chip">4 ZONES</span></div></div>
  <div class="asset-layout">
    <div class="panel"><div class="panel-head"><h2>Observed Assets</h2><span>Operational inventory</span></div><div class="table-wrap asset-table-scroll"><table class="data-table"><thead><tr><th>Asset</th><th>Type</th><th>Station</th><th>Zone</th><th>Function</th><th>Status</th></tr></thead><tbody>
    <?php foreach($assets as $a): ?><tr data-asset-row='<?=htmlspecialchars(json_encode($a,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),ENT_QUOTES)?>'><td class="asset-link"><?=htmlspecialchars($a['id'])?></td><td><?=htmlspecialchars($a['type'])?></td><td><?=htmlspecialchars($a['station'])?></td><td><?=htmlspecialchars($a['zone'])?></td><td><?=htmlspecialchars($a['function'])?></td><td class="<?=in_array($a['status'],['ONLINE','RUNNING','OPEN'])?'state-online':'state-standby'?>"><?=htmlspecialchars($a['status'])?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
    <aside class="panel asset-detail"><div class="panel-head"><h2>Asset Context</h2><span>Selected system</span></div><div class="panel-body">
      <h3 id="asset-detail-id">HIST-01</h3><div class="type" id="asset-detail-type">Historian</div>
      <div class="kv"><span>Station</span><strong id="asset-detail-station">Metropolitan</strong></div>
      <div class="kv"><span>Zone</span><strong id="asset-detail-zone">OT Services</strong></div>
      <div class="kv"><span>Function</span><strong id="asset-detail-function">Process data services</strong></div>
      <div class="kv"><span>Platform</span><strong id="asset-detail-vendor">Industrial historian</strong></div>
      <div class="kv"><span>Related systems</span><strong id="asset-detail-related">PLC-CP-01 · PLC-SL-01 · PLC-SO-01</strong></div>
      <p class="muted asset-research-note">El inventario expone contexto operativo, no definiciones. La función de cada clase de activo forma parte del análisis técnico del entorno.</p>
    </div></aside>
  </div>
</section>

<section class="view" data-view="network">
  <div class="page-head"><div><div class="breadcrumb">System / Network</div><h1>Zones & Conduits</h1><p>Topología lógica metropolitana organizada por niveles funcionales OT.</p></div></div>
  <div class="network-layout">
    <div class="panel"><div class="panel-head"><h2>Operational Architecture</h2><span>Logical / Purdue-inspired view</span></div><div class="zone-map purdue">
      <div class="zone"><div class="zone-title">LEVEL 4 · Enterprise / Application Boundary</div><div class="zone-assets"><div class="node"><strong>INTERAFAS-WEB</strong>Application services</div></div></div>
      <div class="conduit">▼ TRUST BOUNDARY · WEB-TO-OPERATIONS ▼</div>
      <div class="zone"><div class="zone-title">LEVEL 3.5 · OT Edge · OPS-NET-20</div><div class="zone-assets"><div class="node"><strong>RTU-GW-07</strong>Remote operations gateway</div></div></div>
      <div class="conduit">▼ OT SERVICES CONDUIT ▼</div>
      <div class="zone"><div class="zone-title">LEVEL 3 · OT Services / Engineering</div><div class="zone-assets"><div class="node"><strong>HMI-OPS-01</strong>Operations console</div><div class="node"><strong>HIST-01</strong>Process data services</div><div class="node"><strong>EWS-01</strong>Engineering workstation</div><div class="node"><strong>BRS-01</strong>Backup / recovery</div><div class="node"><strong>OT-AUTO-01</strong>Automation engine</div></div></div>
      <div class="conduit">▼ CONTROL CONDUIT ▼</div>
      <div class="zone"><div class="zone-title">LEVEL 2 · Area Control</div><div class="zone-assets"><div class="node"><strong>PLC-CP-01</strong>Cerro de San Pablo</div><div class="node"><strong>PLC-SL-01</strong>Saint Louis</div><div class="node"><strong>PLC-SO-01</strong>Soledade</div></div></div>
      <div class="conduit">▼ FIELD / PROCESS CONDUIT ▼</div>
      <div class="zone"><div class="zone-title">LEVEL 1 · Process</div><div class="zone-assets"><div class="node"><strong>PUMPS</strong>Primary / standby</div><div class="node"><strong>VALVES</strong>Distribution control</div><div class="node"><strong>RESERVOIRS</strong>Regulation storage</div><div class="node"><strong>INSTRUMENTS</strong>Flow / pressure / quality</div></div></div>
    </div></div>
    <aside class="panel"><div class="panel-head"><h2>Network Context</h2><span>Metropolitan OT</span></div><div class="panel-body">
      <div class="kv"><span>Operations segment</span><strong>10.40.20.0/24</strong></div>
      <div class="kv"><span>Primary gateway</span><strong>RTU-GW-07</strong></div>
      <div class="kv"><span>Municipal control cells</span><strong>3</strong></div>
      <div class="kv"><span>OT services</span><strong>5</strong></div>
      <div class="kv"><span>Observed assets</span><strong><?=count($assets)?></strong></div>
    </div></aside>
  </div>
</section>

<section class="view" data-view="automation">
  <div class="page-head"><div><div class="breadcrumb">System / Automation</div><h1>Maintenance Automation</h1><p>Tareas programadas sobre servicios y activos de la red metropolitana.</p></div><div class="page-tools"><span class="tool-chip">OT-AUTO-01</span><span class="tool-chip">ENGINE READY</span></div></div>
  <div class="metrics automation-metrics">
    <div class="metric"><label>Jobs Today</label><strong>8</strong><span class="sub">Maintenance executions</span></div>
    <div class="metric"><label>Completed</label><strong>7</strong><span class="sub">Successful tasks</span></div>
    <div class="metric"><label>Scheduled</label><strong>1</strong><span class="sub">Pending execution</span></div>
    <div class="metric"><label>Failed</label><strong>0</strong><span class="sub">Last 24 hours</span></div>
  </div>
  <div class="automation-layout">
    <div class="panel"><div class="panel-head"><h2>Scheduled Jobs</h2><span>Maintenance scheduler</span></div><div class="panel-body">
      <div class="job-card"><div class="job-top"><div><h3>Daily historian health check</h3><div class="job-meta">HIST-01 · Daily 02:00 · health-check</div></div><div class="job-state">COMPLETED</div></div></div>
      <div class="job-card"><div class="job-top"><div><h3>Engineering project validation</h3><div class="job-meta">EWS-01 · Daily 02:20 · project-validate</div></div><div class="job-state">COMPLETED</div></div></div>
      <div class="job-card"><div class="job-top"><div><h3>Recovery catalog validation</h3><div class="job-meta">BRS-01 · Sunday 03:00 · recovery-verify</div></div><div class="job-state">SCHEDULED</div></div></div>
      <div class="job-card"><div class="job-top"><div><h3>RTU configuration verification</h3><div class="job-meta">RTU-GW-07 · Daily 04:00 · config-verify</div></div><div class="job-state">COMPLETED</div></div></div>
      <div class="empty-note">Nueva automatización de mantenimiento disponible durante ventanas de servicio.</div>
    </div></div>
    <aside class="panel"><div class="panel-head"><h2>Automation Engine</h2><span>OT-AUTO-01</span></div><div class="panel-body">
      <div class="kv"><span>State</span><strong class="ok-text">READY</strong></div>
      <div class="kv"><span>Execution mode</span><strong>Scheduled / Manual</strong></div>
      <div class="kv"><span>Maintenance mode</span><strong><?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></strong></div>
      <div class="kv"><span>Audit profile</span><strong>Standard</strong></div>
      <div class="kv"><span>Service scope</span><strong>Metropolitan OT</strong></div>
      <p class="muted asset-research-note">El motor presenta tareas de mantenimiento legítimas sobre servicios OT. La creación interactiva de nuevos trabajos todavía no está habilitada.</p>
    </div></aside>
  </div>
</section>

<section class="view" data-view="maintenance">
  <div class="page-head"><div><div class="breadcrumb">System / Maintenance</div><h1>Maintenance Context</h1><p>Estado de servicio de la puerta de enlace y contexto de mantenimiento metropolitano.</p></div></div>
  <div class="grid-main">
    <div class="panel"><div class="panel-head"><h2>RTU-GW-07 Service State</h2><span>OT Edge</span></div><div class="panel-body">
      <div class="kv"><span>Firmware</span><strong><?=htmlspecialchars((string)($fw['version']??'—'))?></strong></div>
      <div class="kv"><span>Mode</span><strong><?=htmlspecialchars((string)($fw['mode']??'—'))?></strong></div>
      <div class="kv"><span>Diagnostic</span><strong><?=htmlspecialchars((string)($fw['diagnostic']??'—'))?></strong></div>
      <div class="kv"><span>Automation engine</span><strong>READY</strong></div>
    </div></div>
    <div class="panel"><div class="panel-head"><h2>Service Scope</h2><span><?=htmlspecialchars(strtoupper((string)($ops['role']??'operator')))?></span></div><div class="panel-body">
      <div class="kv"><span>Stations visible</span><strong>3</strong></div>
      <div class="kv"><span>OT services visible</span><strong>5</strong></div>
      <div class="kv"><span>Process control</span><strong class="<?=$canControl?'ok-text':'warn-text'?>"><?=$canControl?'AVAILABLE':'RESTRICTED'?></strong></div>
    </div></div>
  </div>
</section>

<section class="view" data-view="configuration">
  <div class="page-head"><div><div class="breadcrumb">Engineering / Configuration</div><h1>Engineering Context</h1><p>Proyectos y controladores asociados a las estaciones metropolitanas.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Control Projects</h2><span>Read-only context</span></div><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Project</th><th>Station</th><th>Controller</th><th>Revision</th><th>Engineering</th><th>Recovery</th></tr></thead>
    <tbody>
      <tr><td class="asset-link">PMN_CP_DISTRIBUTION</td><td>Cerro de San Pablo</td><td>PLC-CP-01</td><td>2026.09.12-r11</td><td>EWS-01</td><td>BRS-01</td></tr>
      <tr><td class="asset-link">PMN_SL_DISTRIBUTION</td><td>Saint Louis</td><td>PLC-SL-01</td><td>2026.09.14-r17</td><td>EWS-01</td><td>BRS-01</td></tr>
      <tr><td class="asset-link">PMN_SO_DISTRIBUTION</td><td>Soledade</td><td>PLC-SO-01</td><td>2026.09.13-r09</td><td>EWS-01</td><td>BRS-01</td></tr>
    </tbody>
  </table></div></div>
</section>

<section class="view" data-view="events">
  <div class="page-head"><div><div class="breadcrumb">Analysis / Events</div><h1>Operational Event Timeline</h1><p>Eventos recientes visibles para la sesión metropolitana.</p></div></div>
  <div class="panel"><div class="panel-head"><h2>Timeline</h2><span>Current session</span></div><div class="panel-body">
    <div class="alarm-row"><span>HMI-OPS-01 · Metropolitan interface loaded</span><strong class="ok-text">INFO</strong></div>
    <div class="alarm-row"><span>RTU-GW-07 · Firmware state synchronized</span><strong class="ok-text">INFO</strong></div>
    <div class="alarm-row"><span>EST-SL-01 · Pressure deviation observed</span><strong class="warn-text">MEDIUM</strong></div>
    <div class="alarm-row"><span>EST-SO-01 · Communications latency observed</span><strong class="warn-text">LOW</strong></div>
    <div class="alarm-row"><span id="eventlog">No operator actions in current view.</span><strong>SESSION</strong></div>
  </div></div>
</section>

<div class="footer-note">Entorno operacional metropolitano simulado con fines académicos. Ninguna acción se comunica con infraestructura física.</div>
</div></main>
</div>

<script src="/operations/assets/ops-client.php"></script>
<script>
const stations=<?=json_encode($stations,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;

function showView(name){
  document.querySelectorAll('.nav-item[data-view-target]').forEach(x=>x.classList.toggle('active',x.dataset.viewTarget===name));
  document.querySelectorAll('.view').forEach(v=>v.classList.toggle('active',v.dataset.view===name));
}

document.querySelectorAll('[data-view-target]').forEach(btn=>btn.addEventListener('click',()=>showView(btn.dataset.viewTarget)));
document.querySelectorAll('[data-view-jump]').forEach(btn=>btn.addEventListener('click',()=>showView(btn.dataset.viewJump)));

function openStation(index){
  const s=stations[index]; if(!s)return;
  const code=s.id.split('-')[1];
  document.getElementById('process-title').textContent=s.id+' · '+s.city;
  document.getElementById('process-subtitle').textContent=s.function+' · '+s.controller+' · '+s.gateway;
  document.getElementById('process-zone').textContent=s.city+' distribution cell';
  document.getElementById('station-id').textContent=s.id;
  document.getElementById('station-city').textContent=s.city;
  document.getElementById('station-controller').textContent=s.controller;
  document.getElementById('station-gateway').textContent=s.gateway;
  document.getElementById('station-supply').textContent=s.supply+' L/s';
  document.getElementById('station-demand').textContent=s.demand+' L/s';
  document.getElementById('station-status').textContent=s.status;
  document.getElementById('proc-tank').textContent='TK-'+code+'-01';
  document.getElementById('proc-reserve').textContent=s.reserve+'%';
  document.getElementById('proc-tank-fill').style.height=s.reserve+'%';
  document.getElementById('proc-pump1').textContent='P-'+code+'-101';
  document.getElementById('proc-pump2').textContent='P-'+code+'-102';
  document.getElementById('proc-flow-tag').textContent='FLOW-'+code+'-01';
  document.getElementById('proc-flow').textContent=s.supply+' L/s';
  document.getElementById('proc-pressure-tag').textContent='PRESS-'+code+'-01';
  document.getElementById('proc-pressure').textContent=s.pressure+' bar';
  document.getElementById('proc-valve1').textContent='V-'+code+'-201';
  document.getElementById('proc-valve2').textContent='V-'+code+'-202';
  showView('process');
}

document.querySelectorAll('[data-station-open]').forEach(btn=>btn.addEventListener('click',()=>openStation(Number(btn.dataset.stationOpen))));

document.querySelectorAll('[data-asset-row]').forEach(row=>row.addEventListener('click',()=>{
  const a=JSON.parse(row.dataset.assetRow);
  document.getElementById('asset-detail-id').textContent=a.id;
  document.getElementById('asset-detail-type').textContent=a.type;
  document.getElementById('asset-detail-station').textContent=a.station;
  document.getElementById('asset-detail-zone').textContent=a.zone;
  document.getElementById('asset-detail-function').textContent=a.function;
  document.getElementById('asset-detail-vendor').textContent=a.vendor;
  document.getElementById('asset-detail-related').textContent=a.related;
}));

async function refreshTelemetry(){
  try{
    const r=await fetch(window.INTERAFAS_OPS.telemetryEndpoint,{cache:'no-store'}),d=await r.json();if(!d.ok)return;
    const set=(id,val)=>{const n=document.getElementById(id);if(n)n.textContent=val;};
    set('tank',(d.tank??'—')+'%');
    set('flow',(d.flow??'—')+' L/s');
    set('pressure',(d.pressure??'—')+' bar');
    set('p101',d.p101??'—');
  }catch(e){}
}

async function refreshFirmwareStatus(){ try{ await fetch(window.INTERAFAS_OPS.firmwareStatusEndpoint,{cache:'no-store'}); }catch(e){} }

async function control(state){
  const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'p101',state})}),d=await r.json();
  const log=document.getElementById('eventlog');if(log)log.textContent=(d.message||d.error||'Evento')+' · '+new Date().toLocaleTimeString();
  if(d.process){
    const p=document.getElementById('p101'); if(p)p.textContent=d.process.p101;
    const f=document.getElementById('flow'); if(f)f.textContent=d.process.flow+' L/s';
    const pr=document.getElementById('pressure'); if(pr)pr.textContent=d.process.pressure+' bar';
  }
}
document.querySelectorAll('[data-state]').forEach(b=>b.addEventListener('click',()=>control(b.dataset.state)));

refreshFirmwareStatus();
setInterval(refreshTelemetry,window.INTERAFAS_OPS.refreshInterval);
setInterval(refreshFirmwareStatus,window.INTERAFAS_OPS.firmwareRefreshInterval);
</script>
</body></html>
