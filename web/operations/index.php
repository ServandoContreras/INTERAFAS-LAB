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
$v19FirmwareUnlocked=((string)($ops['role']??'')==='maintenance' && !empty($_SESSION['vuln19_complete']));

$stations=[
    ['id'=>'EST-CP-01','city'=>'Cerro de San Pablo','name'=>'Estación Cerro Norte','function'=>'Primary pumping / distribution','supply'=>910,'demand'=>860,'reserve'=>79,'pressure'=>4.1,'quality'=>'NORMAL','alarms'=>2,'controller'=>'PLC-CP-01','gateway'=>'RTU-CP-01','status'=>'ONLINE','img'=>'station-cp.svg','population'=>1050000],
    ['id'=>'EST-CP-02','city'=>'Cerro de San Pablo','name'=>'Estación Cerro Sur','function'=>'Southern pressure regulation','supply'=>610,'demand'=>575,'reserve'=>76,'pressure'=>4.0,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-CP-04','gateway'=>'RTU-CP-03','status'=>'ONLINE','img'=>'station-cp.svg','population'=>720000],
    ['id'=>'EST-CP-03','city'=>'Cerro de San Pablo','name'=>'Estación Poniente','function'=>'Booster / distribution','supply'=>470,'demand'=>452,'reserve'=>74,'pressure'=>3.9,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-CP-05','gateway'=>'RTU-CP-04','status'=>'ONLINE','img'=>'station-cp.svg','population'=>590000],
    ['id'=>'EST-CP-04','city'=>'Cerro de San Pablo','name'=>'Estación Valle Alto','function'=>'Reservoir / high zone','supply'=>390,'demand'=>361,'reserve'=>82,'pressure'=>4.4,'quality'=>'NORMAL','alarms'=>0,'controller'=>'PLC-CP-06','gateway'=>'RTU-CP-05','status'=>'ONLINE','img'=>'station-cp.svg','population'=>490000],

    ['id'=>'EST-SL-01','city'=>'Saint Louis','name'=>'Estación Central Saint Louis','function'=>'Metropolitan regulation / distribution','supply'=>1130,'demand'=>1070,'reserve'=>84,'pressure'=>4.5,'quality'=>'NORMAL','alarms'=>3,'controller'=>'PLC-SL-01','gateway'=>'RTU-SL-01','status'=>'ONLINE','img'=>'station-sl.svg','population'=>1370000],
    ['id'=>'EST-SL-02','city'=>'Saint Louis','name'=>'Estación Industrial','function'=>'Industrial corridor pumping','supply'=>930,'demand'=>890,'reserve'=>78,'pressure'=>4.2,'quality'=>'NORMAL','alarms'=>2,'controller'=>'PLC-SL-04','gateway'=>'RTU-SL-03','status'=>'ONLINE','img'=>'station-sl.svg','population'=>1050000],
    ['id'=>'EST-SL-03','city'=>'Saint Louis','name'=>'Estación Oriente','function'=>'Eastern distribution','supply'=>820,'demand'=>765,'reserve'=>80,'pressure'=>4.3,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-SL-05','gateway'=>'RTU-SL-04','status'=>'ONLINE','img'=>'station-sl.svg','population'=>880000],
    ['id'=>'EST-SL-04','city'=>'Saint Louis','name'=>'Estación Aeropuerto','function'=>'Booster / strategic corridor','supply'=>540,'demand'=>514,'reserve'=>77,'pressure'=>4.1,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-SL-06','gateway'=>'RTU-SL-05','status'=>'ONLINE','img'=>'station-sl.svg','population'=>700000],

    ['id'=>'EST-SO-01','city'=>'Soledade','name'=>'Estación Oriente Soledade','function'=>'Primary pumping / distribution','supply'=>800,'demand'=>680,'reserve'=>81,'pressure'=>4.3,'quality'=>'NORMAL','alarms'=>2,'controller'=>'PLC-SO-01','gateway'=>'RTU-SO-01','status'=>'ONLINE','img'=>'station-so.svg','population'=>980000],
    ['id'=>'EST-SO-02','city'=>'Soledade','name'=>'Estación Norte','function'=>'Northern pressure regulation','supply'=>690,'demand'=>651,'reserve'=>75,'pressure'=>4.0,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-SO-04','gateway'=>'RTU-SO-03','status'=>'ONLINE','img'=>'station-so.svg','population'=>850000],
    ['id'=>'EST-SO-03','city'=>'Soledade','name'=>'Estación Valle','function'=>'Reservoir / booster','supply'=>580,'demand'=>541,'reserve'=>83,'pressure'=>4.4,'quality'=>'NORMAL','alarms'=>1,'controller'=>'PLC-SO-05','gateway'=>'RTU-SO-04','status'=>'ONLINE','img'=>'station-so.svg','population'=>770000],
    ['id'=>'EST-SO-04','city'=>'Soledade','name'=>'Estación Sur','function'=>'Southern distribution','supply'=>510,'demand'=>477,'reserve'=>80,'pressure'=>4.2,'quality'=>'NORMAL','alarms'=>0,'controller'=>'PLC-SO-06','gateway'=>'RTU-SO-05','status'=>'ONLINE','img'=>'station-so.svg','population'=>670000]
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
    ['id'=>'V-SO-201','type'=>'Control Valve','station'=>'Soledade','zone'=>'Process','function'=>'Distribution control','status'=>'OPEN','vendor'=>'Field equipment','related'=>'PLC-SO-01'],
    ['id'=>'PLC-CP-02','type'=>'PLC','station'=>'Cerro de San Pablo','zone'=>'Area Control','function'=>'Pumping control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'P-CP-101 · P-CP-102'],
    ['id'=>'PLC-CP-03','type'=>'PLC','station'=>'Cerro de San Pablo','zone'=>'Area Control','function'=>'Reservoir auxiliary control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'TK-CP-01 · QCS-CP-01'],
    ['id'=>'RTU-CP-02','type'=>'RTU','station'=>'Cerro de San Pablo','zone'=>'OT Edge','function'=>'Remote booster communications','status'=>'ONLINE','vendor'=>'Remote terminal unit','related'=>'PLC-CP-02 · RTU-GW-07'],
    ['id'=>'QCS-CP-01','type'=>'Quality Controller','station'=>'Cerro de San Pablo','zone'=>'Process Control','function'=>'Water quality supervision','status'=>'ONLINE','vendor'=>'Quality control system','related'=>'PLC-CP-03 · HIST-01'],
    ['id'=>'PLC-SL-02','type'=>'PLC','station'=>'Saint Louis','zone'=>'Area Control','function'=>'Pumping control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'P-SL-101 · P-SL-102'],
    ['id'=>'PLC-SL-03','type'=>'PLC','station'=>'Saint Louis','zone'=>'Area Control','function'=>'Reservoir auxiliary control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'TK-SL-01 · QCS-SL-01'],
    ['id'=>'RTU-SL-02','type'=>'RTU','station'=>'Saint Louis','zone'=>'OT Edge','function'=>'Remote booster communications','status'=>'DEGRADED','vendor'=>'Remote terminal unit','related'=>'PLC-SL-02 · RTU-GW-07'],
    ['id'=>'QCS-SL-01','type'=>'Quality Controller','station'=>'Saint Louis','zone'=>'Process Control','function'=>'Water quality supervision','status'=>'ONLINE','vendor'=>'Quality control system','related'=>'PLC-SL-03 · HIST-01'],
    ['id'=>'PLC-SO-02','type'=>'PLC','station'=>'Soledade','zone'=>'Area Control','function'=>'Pumping control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'P-SO-101 · P-SO-102'],
    ['id'=>'PLC-SO-03','type'=>'PLC','station'=>'Soledade','zone'=>'Area Control','function'=>'Reservoir auxiliary control','status'=>'ONLINE','vendor'=>'Industrial control platform','related'=>'TK-SO-01 · QCS-SO-01'],
    ['id'=>'RTU-SO-02','type'=>'RTU','station'=>'Soledade','zone'=>'OT Edge','function'=>'Remote booster communications','status'=>'ONLINE','vendor'=>'Remote terminal unit','related'=>'PLC-SO-02 · RTU-GW-07'],
    ['id'=>'QCS-SO-01','type'=>'Quality Controller','station'=>'Soledade','zone'=>'Process Control','function'=>'Water quality supervision','status'=>'ONLINE','vendor'=>'Quality control system','related'=>'PLC-SO-03 · HIST-01']
];

$totalSupply=array_sum(array_column($stations,'supply'));
$totalDemand=array_sum(array_column($stations,'demand'));
$avgReserve=round(array_sum(array_column($stations,'reserve'))/count($stations));
$avgPressure=round(array_sum(array_column($stations,'pressure'))/count($stations),1);
$totalAlarms=array_sum(array_column($stations,'alarms'));
$balance=$totalSupply-$totalDemand;
$availability=99.82;
$plcOnline=18;
$rtuOnline=15;
$unackAlarms=7;
$maintenanceActive=4;
$degradedLinks=2;
$populationServed=10120000;
$serviceConnections=2840000;
$dailyVolume=round($totalSupply*86.4);
$storageCapacity=1180;
$pumpingUnits=46;
$pressureZones=18;
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
    <div class="status-cluster"><span class="status-dot"></span> 12 / 12 STATIONS ONLINE</div>
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
  <a class="nav-item <?=$v19FirmwareUnlocked?'':'firmware-locked'?>" id="firmware-nav" href="<?=$v19FirmwareUnlocked?'firmware.php':'#'?>" data-unlocked="<?=$v19FirmwareUnlocked?'1':'0'?>"><span class="nav-glyph">▱</span><span><?=$v19FirmwareUnlocked?'Firmware':'Firmware context'?></span></a>
  <?php endif; ?>
  <div class="nav-section">ANALYSIS</div>
  <button class="nav-item" data-view-target="events"><span class="nav-glyph">≡</span><span>Events</span></button>
  <button class="nav-item" data-view-target="statistics"><span class="nav-glyph">▥</span><span>Statistics</span></button>
</aside>

<main class="hmi-main"><div class="workspace">

<section class="view active" data-view="overview">
  <div class="page-head">
    <div><div class="breadcrumb">Operations / Metropolitan Overview</div><h1>Red Metropolitana de Abastecimiento</h1><p>Cerro de San Pablo · Saint Louis · Soledade · supervisión operacional consolidada</p></div>
    <div class="page-tools"><span class="tool-chip">MODE AUTO</span><span class="tool-chip"><?=htmlspecialchars(strtoupper((string)($ops['role']??'operator')))?></span><span class="tool-chip"><?=$totalAlarms?> ACTIVE ALARMS</span></div>
  </div>

  <div class="metrics metro-metrics dense">
    <div class="metric"><label>Total Supply</label><strong data-live-stat="supply" data-decimals="0"><?=$totalSupply?></strong><small>L/s</small><span class="sub">Metropolitan production</span></div>
    <div class="metric"><label>Total Demand</label><strong data-live-stat="demand" data-decimals="0"><?=$totalDemand?></strong><small>L/s</small><span class="sub">Current demand</span></div>
    <div class="metric"><label>Supply Balance</label><strong>+<?=$balance?></strong><small>L/s</small><span class="sub">Available operating margin</span></div>
    <div class="metric"><label>System Reserve</label><strong data-live-stat="reserve" data-decimals="1"><?=$avgReserve?></strong><small>%</small><span class="sub">Average reserve</span></div>
    <div class="metric"><label>Minimum Reserve</label><strong>79</strong><small>%</small><span class="sub">Cerro de San Pablo</span></div>
    <div class="metric"><label>Average Pressure</label><strong data-live-stat="pressure" data-decimals="2"><?=$avgPressure?></strong><small>bar</small><span class="sub">Distribution headers</span></div>
    <div class="metric"><label>Pressure Range</label><strong>4.1–4.5</strong><small>bar</small><span class="sub">Observed municipal range</span></div>
    <div class="metric"><label>Water Quality</label><strong>NORMAL</strong><span class="sub">3 municipalities within limits</span></div>
    <div class="metric"><label>Energy Load</label><strong data-live-stat="energy" data-decimals="1">16.7</strong><small>MW</small><span class="sub">Pumping systems</span></div>
    <div class="metric"><label>Active Alarms</label><strong data-live-stat="active" data-decimals="0"><?=$totalAlarms?></strong><span class="sub"><?=$unackAlarms?> unacknowledged</span></div>
    <div class="metric"><label>Population Served</label><strong><?=number_format($populationServed/1000000,2)?></strong><small>M</small><span class="sub">Simulated metropolitan population</span></div>
    <div class="metric"><label>Service Connections</label><strong><?=number_format($serviceConnections/1000000,2)?></strong><small>M</small><span class="sub">Domestic / commercial / industrial</span></div>
    <div class="metric"><label>Daily Volume</label><strong><?=number_format($dailyVolume)?></strong><small>ML/d</small><span class="sub">Current hydraulic throughput</span></div>
    <div class="metric"><label>Storage Capacity</label><strong><?=$storageCapacity?></strong><small>ML</small><span class="sub">Strategic + municipal storage</span></div>
    <div class="metric"><label>Pumping Units</label><strong><?=$pumpingUnits?></strong><span class="sub">Primary / standby / booster</span></div>
  </div>
  <div class="tech-strip">
    <div><span>Stations</span><strong>12 / 12</strong></div>
    <div><span>PLC Online</span><strong><?=$plcOnline?> / 18</strong></div>
    <div><span>RTU Online</span><strong><?=$rtuOnline?> / 15</strong></div>
    <div><span>OT Services</span><strong>5 / 5</strong></div>
    <div><span>Degraded Links</span><strong class="warn-text"><?=$degradedLinks?></strong></div>
    <div><span>Maintenance</span><strong class="warn-text"><?=$maintenanceActive?></strong></div>
    <div><span>Unack Alarms</span><strong class="warn-text"><?=$unackAlarms?></strong></div>
    <div><span>Availability</span><strong><?=$availability?>%</strong></div>
  </div>
  <div class="live-event-ticker" id="live-event-ticker">
    <span class="sev-badge sev-info">INFO</span><b>HMI-OPS-01</b><span>Waiting for live operational events…</span><time>--:--:--</time>
  </div>

  <div class="metro-overview-grid">
    <section class="panel">
      <div class="panel-head"><h2>Metropolitan Process Network</h2><span>Primary distribution topology</span></div>
      <div class="metro-image-stage">
        <img src="/operations/assets/img/metro-overview.svg" alt="Vista metropolitana de la red de abastecimiento INTERAFAS">
        <button class="image-hotspot hs-cp" data-station-open="0"><strong>Cerro de San Pablo</strong><span>4 stations · 2.85M served</span><b>2,380 L/s</b></button>
        <button class="image-hotspot hs-sl" data-station-open="4"><strong>Saint Louis</strong><span>4 stations · 4.00M served</span><b>3,420 L/s</b></button>
        <button class="image-hotspot hs-so" data-station-open="8"><strong>Soledade</strong><span>4 stations · 3.27M served</span><b>2,580 L/s</b></button>
        <button class="image-hotspot hs-core" data-view-jump="metropolitan"><strong>PMN-CENTRAL</strong><span>Metropolitan regulation core</span><b>ONLINE</b></button>
        <button class="image-hotspot hs-gw"><strong>RTU-GW-07</strong><span>OT Edge</span><b class="<?=strtoupper((string)($fw['diagnostic']??''))==='SERVICE'?'warn-text':'ok-text'?>"><?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></b></button>
      </div>    </section>

    <aside class="situation-stack">
      <section class="panel">
        <div class="panel-head"><h2>Metropolitan Situation</h2><span>Current operational context</span></div>
        <div class="panel-body">
          <div class="event-row"><span class="event-time">11:46</span><div><strong>Cerro de San Pablo</strong><small>Reservoir level approaching operating threshold</small></div><b class="warn-text">MED</b></div>
          <div class="event-row"><span class="event-time">11:39</span><div><strong>Saint Louis</strong><small>Pressure deviation +0.4 bar</small></div><b class="warn-text">MED</b></div>
          <div class="event-row"><span class="event-time">11:35</span><div><strong>RTU-GW-07</strong><small>Maintenance state <?=htmlspecialchars((string)($fw['diagnostic']??'NORMAL'))?></small></div><b class="warn-text">SERVICE</b></div>
          <div class="event-row"><span class="event-time">11:28</span><div><strong>RTU-SL-02</strong><small>Communication quality degraded</small></div><b class="warn-text">LOW</b></div>
          <div class="event-row"><span class="event-time">11:21</span><div><strong>HIST-01</strong><small>Historian health check completed</small></div><b class="ok-text">INFO</b></div>
          <div class="event-row"><span class="event-time">11:14</span><div><strong>EWS-01</strong><small>Engineering baseline synchronized</small></div><b class="ok-text">INFO</b></div>
          <div class="event-row"><span class="event-time">11:08</span><div><strong>Soledade</strong><small>RTU latency threshold observed</small></div><b class="warn-text">LOW</b></div>
          <div class="event-row"><span class="event-time">10:54</span><div><strong>BRS-01</strong><small>Recovery catalog check queued</small></div><b class="ok-text">INFO</b></div>
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

  <div class="municipal-process-grid">
    <article class="municipal-process-card municipal-process-cp">
      <div class="municipal-process-head">
        <div><span>CERRO DE SAN PABLO</span><strong>HILLSIDE BOOSTER STATION</strong></div>
        <b>PROCESS OVERVIEW</b>
      </div>
      <div class="municipal-process-viewport">
        <div class="pid-scene overview-process-scene" data-process-overview="Cerro de San Pablo"></div>
      </div>
      <div class="municipal-process-foot">
        <span>Same live-process topology · compact system</span>
        <button class="btn station-open" data-station-open="0">OPEN PROCESS</button>
      </div>
    </article>

    <article class="municipal-process-card municipal-process-sl">
      <div class="municipal-process-head">
        <div><span>SAINT LOUIS</span><strong>METROPOLITAN PRIMARY WORKS</strong></div>
        <b>PROCESS OVERVIEW</b>
      </div>
      <div class="municipal-process-viewport">
        <div class="pid-scene overview-process-scene" data-process-overview="Saint Louis"></div>
      </div>
      <div class="municipal-process-foot">
        <span>Same live-process topology · large metropolitan system</span>
        <button class="btn station-open" data-station-open="4">OPEN PROCESS</button>
      </div>
    </article>

    <article class="municipal-process-card municipal-process-so">
      <div class="municipal-process-head">
        <div><span>SOLEDADE</span><strong>EASTERN DISTRIBUTION WORKS</strong></div>
        <b>PROCESS OVERVIEW</b>
      </div>
      <div class="municipal-process-viewport">
        <div class="pid-scene overview-process-scene" data-process-overview="Soledade"></div>
      </div>
      <div class="municipal-process-foot">
        <span>Same live-process topology · medium pressure-control system</span>
        <button class="btn station-open" data-station-open="8">OPEN PROCESS</button>
      </div>
    </article>
  </div>
</section>

<section class="view" data-view="stations">
  <div class="page-head"><div><div class="breadcrumb">Operations / Stations</div><h1>Metropolitan Stations</h1><p>Estado operacional de estaciones principales y controladores asociados.</p></div><div class="page-tools"><span class="tool-chip">12 STATIONS</span><span class="tool-chip">3 MUNICIPALITIES</span><span class="tool-chip">10.12M SERVED</span><span class="tool-chip">18 PRESSURE ZONES</span></div></div>
  <div class="station-list">
    <?php foreach($stations as $i=>$station): ?>
    <article class="station-large-card station-image-card" data-station-card>
      <div class="station-native-head">
        <div><span><?=htmlspecialchars($station['city'])?></span><strong><?=htmlspecialchars($station['id'])?> · <?=htmlspecialchars($station['name'])?></strong><small><?=htmlspecialchars($station['function'])?> · <?=number_format($station['population'])?> people served</small></div>
        <div class="station-native-status"><i></i><b><?=htmlspecialchars($station['status'])?></b></div>
      </div>
      <div class="station-large-grid">
        <div><span>Function</span><strong><?=htmlspecialchars($station['function'])?></strong></div>
        <div><span>Controller</span><strong><?=htmlspecialchars($station['controller'])?></strong></div>
        <div><span>Gateway</span><strong><?=htmlspecialchars($station['gateway'])?></strong></div>
        <div><span>Supply</span><strong><?=htmlspecialchars((string)$station['supply'])?> L/s</strong></div>
        <div><span>Demand</span><strong><?=htmlspecialchars((string)$station['demand'])?> L/s</strong></div>
        <div><span>Reserve</span><strong><?=htmlspecialchars((string)$station['reserve'])?> %</strong></div>
        <div><span>Pressure</span><strong><?=htmlspecialchars((string)$station['pressure'])?> bar</strong></div>
        <div><span>Alarms</span><strong class="<?=$station['alarms']?'warn-text':'ok-text'?>"><?=htmlspecialchars((string)$station['alarms'])?></strong></div>
        <div><span>Population</span><strong><?=number_format($station['population'])?></strong></div>
        <div><span>Service zones</span><strong>3</strong></div>
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
  <div class="page-head process-page-head">
    <div>
      <div class="breadcrumb">Process / Live Station HMI</div>
      <h1><span data-current-station>EST-SL-01</span> · <span data-current-city>Saint Louis</span></h1>
      <p><span data-current-function>Metropolitan regulation / distribution</span> · <span data-current-controller>PLC-SL-01</span> · <span data-current-gateway>RTU-SL-01</span></p>
    </div>
    <div class="page-tools">
      <span class="tool-chip live-chip"><i></i> LIVE</span>
      <span class="tool-chip">AUTO</span>
      <span class="tool-chip"><span data-live-updated>--:--:--</span></span>
      <span class="tool-chip"><?=htmlspecialchars(strtoupper((string)($ops['role']??'operator')))?></span>
    </div>
  </div>

  <div class="process-status-strip">
    <div><span>FLOW</span><strong data-live-tag="FLOW">—</strong><i class="pilot" data-state-pilot="FLOW"></i></div>
    <div><span>HEADER PRESSURE</span><strong data-live-tag="PRESS">—</strong><i class="pilot"></i></div>
    <div><span>RESERVOIR</span><strong data-live-tag="LEVEL">—</strong><i class="pilot"></i></div>
    <div><span>CHLORINE</span><strong data-live-tag="CHLORINE">—</strong><i class="pilot"></i></div>
    <div><span>TURBIDITY</span><strong data-live-tag="TURBIDITY">—</strong><i class="pilot"></i></div>
    <div><span>RTU LATENCY</span><strong data-live-tag="LATENCY">—</strong><i class="pilot"></i></div>
    <div><span>MODE</span><strong data-live-tag="MODE" data-live-format="state">AUTO</strong><i class="pilot"></i></div>
  </div>

  <div class="pid-shell">
    <div class="pid-toolbar">
      <div class="pid-toolbar-left">
        <button class="pid-tab active">LIVE PROCESS</button>
        <button class="pid-tab" data-view-jump="assets">EQUIPMENT</button>
        <button class="pid-tab" data-view-jump="alarms">ALARMS</button>
        <button class="pid-tab" data-view-jump="maintenance">MAINTENANCE</button>
      </div>
      <div class="pid-legend">
        <span><i class="legend-dot ok"></i>NORMAL</span>
        <span><i class="legend-dot warn"></i>WARNING</span>
        <span><i class="legend-dot alarm"></i>ALARM</span>
        <span><i class="legend-dot off"></i>OFFLINE / STOP</span>
      </div>
    </div>

    <div class="pid-grid">
      <section class="pid-canvas">
        <div class="pid-titlebar"><strong>STATION PROCESS SCHEMATIC</strong><span>Digital twin simulation · live tags</span></div>

        <div class="pid-scene">
          <div class="pid-zone zone-intake">INTAKE / STORAGE</div>
          <div class="pid-zone zone-pumping">PRIMARY PUMPING</div>
          <div class="pid-zone zone-quality">QUALITY / REGULATION</div>
          <div class="pid-zone zone-distribution">DISTRIBUTION</div>

          <!-- pipes -->
          <div class="pipe pipe-main p1 flow-active"></div>
          <div class="pipe pipe-main p2" data-pipe-state="P101"></div>
          <div class="pipe pipe-main p3 flow-active"></div>
          <div class="pipe pipe-main p4" data-pipe-state="V201"></div>
          <div class="pipe pipe-main p5" data-pipe-state="V202"></div>
          <div class="pipe pipe-vertical pv1 flow-active"></div>
          <div class="pipe pipe-vertical pv2 flow-active"></div>
          <div class="pipe pipe-vertical pv3 flow-active"></div>

          <!-- reservoir -->
          <div class="pid-equipment reservoir-vessel eq-click" data-equipment="TQ-01">
            <div class="vessel-cap top"></div>
            <div class="vessel-body"><div class="liquid-level" data-level-fill="LEVEL"></div><div class="vessel-grid"></div></div>
            <div class="vessel-cap bottom"></div>
            <div class="equipment-label"><b>TQ-01</b><span>Regulation Reservoir</span></div>
            <div class="digital-readout"><small>LT-101</small><strong data-live-tag="LEVEL">—</strong></div>
          </div>

          <!-- pump A -->
          <div class="pid-equipment pump-unit pump-a running eq-click" data-pump-state="P101" data-equipment="P-101">
            <div class="motor"><div class="motor-rotor"></div></div>
            <div class="pump-volute"></div>
            <div class="equipment-label"><b>P-101</b><span>Primary Pump A</span></div>
            <div class="equipment-state" data-live-tag="P101" data-live-format="state" data-on="RUN" data-off="STOP">RUN</div>
            <div class="mini-values"><span>LOAD <b data-live-tag="MOTOR_A">—</b></span><span>CURR <b data-live-tag="CURRENT_A">—</b></span></div>
          </div>

          <!-- pump B -->
          <div class="pid-equipment pump-unit pump-b stopped eq-click" data-pump-state="P102" data-equipment="P-102">
            <div class="motor"><div class="motor-rotor"></div></div>
            <div class="pump-volute"></div>
            <div class="equipment-label"><b>P-102</b><span>Standby Pump B</span></div>
            <div class="equipment-state" data-live-tag="P102" data-live-format="state" data-on="RUN" data-off="STBY">STBY</div>
            <div class="mini-values"><span>LOAD <b data-live-tag="MOTOR_B">—</b></span><span>CURR <b data-live-tag="CURRENT_B">—</b></span></div>
          </div>

          <!-- quality skid -->
          <div class="pid-equipment quality-skid eq-click" data-equipment="QCS-01">
            <div class="skid-cabinet"><div class="cabinet-screen"><span>QCS</span><i></i><i></i><i></i></div></div>
            <div class="sample-cell"></div>
            <div class="equipment-label"><b>QCS-01</b><span>Quality Control Skid</span></div>
            <div class="quality-values">
              <div><small>CL₂</small><strong data-live-tag="CHLORINE">—</strong></div>
              <div><small>NTU</small><strong data-live-tag="TURBIDITY">—</strong></div>
              <div><small>TEMP</small><strong data-live-tag="TEMP">—</strong></div>
            </div>
          </div>

          <!-- header -->
          <div class="pid-equipment pressure-header eq-click" data-equipment="HDR-01">
            <div class="header-cylinder"></div>
            <div class="equipment-label"><b>HDR-01</b><span>Distribution Header</span></div>
          </div>

          <!-- instruments -->
          <div class="instrument inst-flow"><div class="inst-tag">FT-101</div><strong data-live-tag="FLOW">—</strong></div>
          <div class="instrument inst-pressure"><div class="inst-tag">PT-201</div><strong data-live-tag="PRESS">—</strong></div>
          <div class="instrument inst-latency"><div class="inst-tag">RTU RTT</div><strong data-live-tag="LATENCY">—</strong></div>

          <!-- valves -->
          <div class="pid-valve valve-1 open eq-click" data-valve-state="V201" data-equipment="V-201"><div class="valve-symbol"></div><span>V-201</span><b data-live-tag="V201" data-live-format="state" data-on="OPEN" data-off="CLOSED">OPEN</b></div>
          <div class="pid-valve valve-2 open eq-click" data-valve-state="V202" data-equipment="V-202"><div class="valve-symbol"></div><span>V-202</span><b data-live-tag="V202" data-live-format="state" data-on="OPEN" data-off="CLOSED">OPEN</b></div>

          <!-- destinations -->
          <div class="distribution-zone dist-a"><b>ZONE A</b><span>Primary distribution</span><strong data-live-tag="FLOW">—</strong></div>
          <div class="distribution-zone dist-b"><b>ZONE B</b><span>Secondary distribution</span><strong>ACTIVE</strong></div>

          <!-- controller rack -->
          <div class="controller-rack eq-click" data-equipment="PLC">
            <div class="rack-head"><b data-current-controller>PLC-SL-01</b><span>CONTROL CELL</span></div>
            <div class="rack-modules"><?php for($i=0;$i<8;$i++): ?><i class="<?=$i<6?'on':''?>"></i><?php endfor; ?></div>
            <div class="rack-footer"><span>CPU RUN</span><b class="state-ok">ONLINE</b></div>
          </div>

          <div class="rtu-panel eq-click" data-equipment="RTU">
            <b data-current-gateway>RTU-SL-01</b><span>STATION EDGE</span>
            <div class="rtu-led-row"><i></i><i></i><i class="warn"></i><i></i></div>
            <small>LATENCY</small><strong data-live-tag="LATENCY">—</strong>
          </div>
        </div>
      </section>

      <aside class="pid-side">
        <section class="pid-panel">
          <header><span>PROCESS STATUS</span><b class="state-ok">RUNNING</b></header>
          <div class="pid-kv"><span>Station</span><b data-current-station>EST-SL-01</b></div>
          <div class="pid-kv"><span>Municipality</span><b data-current-city>Saint Louis</b></div>
          <div class="pid-kv"><span>Controller</span><b data-current-controller>PLC-SL-01</b></div>
          <div class="pid-kv"><span>Gateway</span><b data-current-gateway>RTU-SL-01</b></div>
          <div class="pid-kv"><span>Control mode</span><b data-live-tag="MODE" data-live-format="state">AUTO</b></div>
        </section>

        <section class="pid-panel">
          <header><span>LIVE INSTRUMENTS</span><b><span data-live-updated>--:--:--</span></b></header>
          <div class="instrument-list">
            <div><span>FT-101</span><b data-live-tag="FLOW">—</b><i class="pilot"></i></div>
            <div><span>PT-201</span><b data-live-tag="PRESS">—</b><i class="pilot"></i></div>
            <div><span>LT-101</span><b data-live-tag="LEVEL">—</b><i class="pilot"></i></div>
            <div><span>AIT-301</span><b data-live-tag="CHLORINE">—</b><i class="pilot"></i></div>
            <div><span>AIT-302</span><b data-live-tag="TURBIDITY">—</b><i class="pilot"></i></div>
            <div><span>TT-101</span><b data-live-tag="TEMP">—</b><i class="pilot"></i></div>
          </div>
        </section>

        <section class="pid-panel process-actions-panel">
          <header><span>PROCESS COMMAND</span><b><?=htmlspecialchars(strtoupper((string)($ops['role']??'operator')))?></b></header>
          <?php if($canControl): ?>
          <div class="process-command-grid">
            <button class="command-btn start" data-state="ON">START P-101</button>
            <button class="command-btn stop" data-state="OFF">STOP P-101</button>
          </div>
          <?php else: ?>
          <div class="control-restricted"><i></i><b>CONTROL RESTRICTED</b><span>Maintenance sessions are read-only for process commands.</span></div>
          <?php endif; ?>
        </section>

        <section class="pid-panel">
          <header><span>ACTIVE PROCESS NOTICES</span><b class="warn-text">2</b></header>
          <div class="notice-list">
            <div class="notice warn"><i></i><div><b>RTU latency variance</b><span>Communication path under observation.</span></div></div>
            <div class="notice info"><i></i><div><b>Service window active</b><span>RTU-GW-07 diagnostic SERVICE.</span></div></div>
          </div>
        </section>
      </aside>
    </div>
  </div>
</section>

<section class="view" data-view="trends">
  <div class="page-head">
    <div><div class="breadcrumb">Process / Trends</div><h1>Metropolitan Process Analytics</h1><p>Series temporales, correlación operativa y comportamiento de la red por subsistema.</p></div>
    <div class="page-tools trend-range">
      <button class="tool-chip" data-trend-range="15m">15 MIN</button>
      <button class="tool-chip active" data-trend-range="1h">1 H</button>
      <button class="tool-chip" data-trend-range="8h">8 H</button>
      <button class="tool-chip" data-trend-range="24h">24 H</button>
      <button class="tool-chip" data-trend-range="7d">7 D</button>
    </div>
  </div>

  <div class="trend-stat-grid">
    <div class="trend-stat"><span>Network Supply</span><strong>8,380 L/s</strong><small>AVG 8,214 · P95 8,612 · +2.0% vs baseline</small></div>
    <div class="trend-stat"><span>Demand</span><strong>7,836 L/s</strong><small>AVG 7,691 · PEAK 8,042 · reserve margin 6.5%</small></div>
    <div class="trend-stat"><span>Pressure</span><strong>4.21 bar</strong><small>MIN 3.86 · MAX 4.58 · σ 0.17</small></div>
    <div class="trend-stat"><span>Storage</span><strong>79.4%</strong><small>1,180 ML installed · 937 ML available</small></div>
    <div class="trend-stat"><span>Pumping Energy</span><strong>16.7 MW</strong><small>PF 0.94 · 46 units · 31 running</small></div>
    <div class="trend-stat"><span>RTU Latency</span><strong>31 ms</strong><small>P95 58 ms · 2 degraded links</small></div>
  </div>

  <div class="analytics-grid">
    <section class="analytics-card wide">
      <header><div><span>HYDRAULIC BALANCE</span><h3>Supply vs metropolitan demand</h3></div><div class="live-badge"><i></i> LIVE</div></header>
      <canvas id="chart-supply" class="ops-chart"></canvas>
      <footer><span>Source: HIST-01 / FLOW aggregation</span><span>Sampling: 5 s</span></footer>
    </section>

    <section class="analytics-card wide">
      <header><div><span>PRESSURE NETWORK</span><h3>Municipal distribution pressure</h3></div><div class="legend-inline"><i></i> CP <i></i> SL <i></i> SO</div></header>
      <canvas id="chart-pressure" class="ops-chart"></canvas>
      <footer><span>18 pressure zones</span><span>Target envelope 3.8–4.6 bar</span></footer>
    </section>

    <section class="analytics-card">
      <header><div><span>STORAGE</span><h3>Reservoir utilization</h3></div></header>
      <canvas id="chart-reservoir" class="ops-chart compact"></canvas>
      <footer><span>12 major storage assets</span><span>Trend stable</span></footer>
    </section>

    <section class="analytics-card">
      <header><div><span>ENERGY</span><h3>Pumping electrical load</h3></div></header>
      <canvas id="chart-energy" class="ops-chart compact"></canvas>
      <footer><span>46 pumping units</span><span>Peak 17.9 MW</span></footer>
    </section>

    <section class="analytics-card">
      <header><div><span>OT COMMUNICATIONS</span><h3>RTU communication latency</h3></div><b class="warn-text">2 DEGRADED</b></header>
      <canvas id="chart-latency" class="ops-chart compact"></canvas>
      <footer><span>15 RTU / gateway paths</span><span>P95 58 ms</span></footer>
    </section>

    <section class="analytics-card">
      <header><div><span>WATER QUALITY</span><h3>Turbidity / residual chlorine</h3></div></header>
      <canvas id="chart-quality" class="ops-chart compact"></canvas>
      <footer><span>6 QCS controllers</span><span>All within operating envelope</span></footer>
    </section>
  </div>

  <div class="trend-bottom-grid">
    <section class="panel"><div class="panel-head"><h2>Operational Correlation</h2><span>Current period</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Signal</th><th>Current</th><th>Avg</th><th>Min</th><th>Max</th><th>Δ</th></tr></thead><tbody>
      <tr><td>FLOW.METRO</td><td>8,380 L/s</td><td>8,214</td><td>7,802</td><td>8,612</td><td class="ok-text">+2.0%</td></tr>
      <tr><td>DEMAND.METRO</td><td>7,836 L/s</td><td>7,691</td><td>7,318</td><td>8,042</td><td class="warn-text">+1.9%</td></tr>
      <tr><td>PRESSURE.AVG</td><td>4.21 bar</td><td>4.18</td><td>3.86</td><td>4.58</td><td>+0.03</td></tr>
      <tr><td>ENERGY.PUMP</td><td>16.7 MW</td><td>16.1</td><td>13.8</td><td>17.9</td><td class="warn-text">+3.7%</td></tr>
      <tr><td>RTU.LATENCY</td><td>31 ms</td><td>28</td><td>18</td><td>91</td><td class="warn-text">+10.7%</td></tr>
    </tbody></table></div></section>

    <section class="panel"><div class="panel-head"><h2>Trend Diagnostics</h2><span>Automated observation</span></div><div class="panel-body">
      <div class="diagnostic-item"><i class="ok"></i><div><b>Hydraulic balance stable</b><span>Supply margin remains above operating threshold.</span></div></div>
      <div class="diagnostic-item"><i class="warn"></i><div><b>Saint Louis communication variance</b><span>RTU-SL-02 latency shows intermittent peaks.</span></div></div>
      <div class="diagnostic-item"><i class="ok"></i><div><b>Storage trajectory normal</b><span>Reservoir depletion rate remains within scheduled profile.</span></div></div>
      <div class="diagnostic-item"><i class="ok"></i><div><b>Quality envelope maintained</b><span>No cross-zone water quality deviation detected.</span></div></div>
    </div></section>
  </div>
</section>

<section class="view" data-view="alarms">
  <div class="page-head"><div><div class="breadcrumb">Process / Alarms</div><h1>Metropolitan Alarm Management</h1><p>Condiciones activas, reconocidas y recientes por estación y servicio OT.</p></div><div class="page-tools"><span class="tool-chip"><?=$totalAlarms?> ACTIVE</span><span class="tool-chip"><?=$unackAlarms?> UNACK</span><span class="tool-chip">0 CRITICAL</span></div></div>
  <div class="alarm-filter-strip"><span>ALL 12</span><span>HIGH 1</span><span>MEDIUM 3</span><span>LOW 3</span><span>INFO 5</span><span>UNACK <?=$unackAlarms?></span></div>
  <div class="panel"><div class="panel-head"><h2>Alarm & Event Queue</h2><span>Priority ordered</span></div><div class="table-wrap"><table class="data-table">
    <thead><tr><th>Time</th><th>Priority</th><th>Station</th><th>Source</th><th>Condition</th><th>State</th></tr></thead>
    <tbody>
      <tr><td>11:46:03</td><td class="warn-text">HIGH</td><td>Cerro de San Pablo</td><td>TK-CP-01</td><td>Reservoir level approaching low operating threshold</td><td>UNACK</td></tr>
      <tr><td>11:39:12</td><td class="warn-text">MEDIUM</td><td>Saint Louis</td><td>PRESS-SL-01</td><td>Header pressure high deviation +0.4 bar</td><td>UNACK</td></tr>
      <tr><td>11:28:51</td><td class="warn-text">MEDIUM</td><td>Saint Louis</td><td>RTU-SL-02</td><td>Communication quality degraded</td><td>UNACK</td></tr>
      <tr><td>11:08:44</td><td class="warn-text">MEDIUM</td><td>Soledade</td><td>RTU-SO-02</td><td>Communication latency threshold</td><td>UNACK</td></tr>
      <tr><td>10:58:09</td><td>LOW</td><td>Saint Louis</td><td>P-SL-102</td><td>Standby pump availability test delayed</td><td>ACK</td></tr>
      <tr><td>10:51:27</td><td>LOW</td><td>Cerro de San Pablo</td><td>FLOW-CP-02</td><td>Flow variance above baseline</td><td>ACK</td></tr>
      <tr><td>10:43:18</td><td>LOW</td><td>Soledade</td><td>QCS-SO-01</td><td>Quality sample synchronization delay</td><td>ACK</td></tr>
      <tr><td>10:42:07</td><td>INFO</td><td>Metropolitan</td><td>RTU-GW-07</td><td>Maintenance diagnostic state entered</td><td>ACK</td></tr>
      <tr><td>10:31:33</td><td>INFO</td><td>Metropolitan</td><td>HIST-01</td><td>Archive backlog recovered</td><td>CLEARED</td></tr>
      <tr><td>10:14:22</td><td>INFO</td><td>Metropolitan</td><td>BRS-01</td><td>Recovery catalog validation scheduled</td><td>OPEN</td></tr>
      <tr><td>09:57:40</td><td>INFO</td><td>Metropolitan</td><td>EWS-01</td><td>Engineering baseline synchronized</td><td>CLEARED</td></tr>
      <tr><td>09:41:15</td><td>INFO</td><td>Saint Louis</td><td>PLC-SL-02</td><td>Configuration verification completed</td><td>CLEARED</td></tr>
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
      <div class="kv"><span>Dependency class</span><strong id="asset-detail-class">—</strong></div>
      <div class="asset-related-block"><span class="asset-related-label">Related systems</span><div id="asset-detail-related" class="asset-related-list">PLC-CP-01 · PLC-SL-01 · PLC-SO-01</div></div>
      <div class="asset-chain-state" id="asset-chain-state">
        <div class="asset-chain-head"><span>Dependency path</span><strong id="asset-chain-progress">0/6</strong></div>
        <div class="asset-chain-track" id="asset-chain-track" aria-label="Dependency chain progress">
          <i></i><i></i><i></i><i></i><i></i><i></i>
        </div>
      </div>
      <div class="asset-firmware-context" id="asset-firmware-context" hidden>
        <span>Legacy firmware profile</span>
        <strong id="asset-firmware-profile">—</strong>
        <small id="asset-firmware-warning">—</small>
      </div>
      <p class="muted asset-research-note">El inventario expone contexto operativo y relaciones observadas entre activos. Selecciona un sistema relacionado para consultar su dependencia.</p>
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
  <div class="page-head"><div><div class="breadcrumb">System / Maintenance</div><h1>Maintenance Operations Context</h1><p>Ventanas de servicio, firmware, validaciones técnicas y actividades recientes de la red metropolitana.</p></div><div class="page-tools"><span class="tool-chip">2 ACTIVE / PENDING</span><span class="tool-chip">SERVICE WINDOW</span></div></div>
  <div class="maintenance-kpis">
    <div><span>Active Windows</span><strong>1</strong><small>RTU-GW-07</small></div>
    <div><span>Scheduled</span><strong>2</strong><small>Next 12 hours</small></div>
    <div><span>Firmware Reviews</span><strong>1</strong><small>Pending</small></div>
    <div><span>Config Validations</span><strong>2</strong><small>Queued</small></div>
    <div><span>Recovery Checks</span><strong>1</strong><small>Scheduled</small></div>
    <div><span>Assets in Service</span><strong>1</strong><small>RTU-GW-07</small></div>
  </div>
  <div class="maintenance-grid">
    <section class="panel"><div class="panel-head"><h2>Maintenance Windows</h2><span>Current & scheduled</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Window</th><th>Asset</th><th>Scope</th><th>State</th><th>Owner</th></tr></thead><tbody>
      <tr><td>MW-260925-01</td><td>RTU-GW-07</td><td>Firmware / diagnostics</td><td class="warn-text">ACTIVE</td><td>OT Support</td></tr>
      <tr><td>MW-260925-02</td><td>EST-SL-01</td><td>Controller validation</td><td>SCHEDULED</td><td>Engineering</td></tr>
      <tr><td>MW-260926-01</td><td>BRS-01</td><td>Recovery catalog</td><td>SCHEDULED</td><td>OT Services</td></tr>
    </tbody></table></div></section>
    <section class="panel"><div class="panel-head"><h2>Firmware Estate</h2><span>Edge / station devices</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Asset</th><th>Version</th><th>Diagnostic</th><th>State</th></tr></thead><tbody>
      <tr><td>RTU-GW-07</td><td><?=htmlspecialchars((string)($fw['version']??'—'))?></td><td><?=htmlspecialchars((string)($fw['diagnostic']??'—'))?></td><td class="warn-text">SERVICE</td></tr>
      <tr><td>RTU-CP-01</td><td>2.9.8</td><td>LOCKED</td><td class="ok-text">CURRENT</td></tr>
      <tr><td>RTU-SL-01</td><td>3.1.2</td><td>LOCKED</td><td class="ok-text">CURRENT</td></tr>
      <tr><td>RTU-SO-01</td><td>3.0.7</td><td>LOCKED</td><td class="ok-text">CURRENT</td></tr>
    </tbody></table></div></section>
    <section class="panel"><div class="panel-head"><h2>Recent Maintenance</h2><span>Last operations</span></div><div class="panel-body">
      <div class="maint-event"><b>11:35</b><span>RTU-GW-07 diagnostic changed to SERVICE</span><strong class="warn-text">ACTIVE</strong></div>
      <div class="maint-event"><b>09:42</b><span>PLC-SL-02 configuration verification</span><strong class="ok-text">PASS</strong></div>
      <div class="maint-event"><b>08:11</b><span>BRS-01 recovery catalog check</span><strong class="ok-text">PASS</strong></div>
      <div class="maint-event"><b>07:48</b><span>HIST-01 storage health validation</span><strong class="ok-text">PASS</strong></div>
      <div class="maint-event"><b>06:32</b><span>RTU-SO-02 communications test</span><strong class="warn-text">DEGRADED</strong></div>
    </div></section>
    <section class="panel"><div class="panel-head"><h2>Pending Actions</h2><span>Operational queue</span></div><div class="panel-body">
      <div class="kv"><span>Firmware review</span><strong>RTU-GW-07</strong></div>
      <div class="kv"><span>Config validation</span><strong>PLC-SL-02</strong></div>
      <div class="kv"><span>Config validation</span><strong>PLC-CP-03</strong></div>
      <div class="kv"><span>Recovery validation</span><strong>BRS-01</strong></div>
      <div class="kv"><span>Process control</span><strong class="<?=$canControl?'ok-text':'warn-text'?>"><?=$canControl?'AVAILABLE':'RESTRICTED'?></strong></div>
    </div></section>
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
  <div class="page-head"><div><div class="breadcrumb">Analysis / Events</div><h1>Operational Event Timeline</h1><p>Actividad reciente de estaciones, controladores, servicios OT y mantenimiento.</p></div><div class="page-tools"><span class="tool-chip">20 RECENT</span><span class="tool-chip">LIVE SESSION</span></div></div>
  <div class="panel live-log-panel">
    <div class="panel-head"><h2>Live Operational Stream</h2><span><i class="live-dot"></i> ingesting · <b data-live-updated>--:--:--</b></span></div>
    <div class="table-wrap live-log-wrap"><table class="data-table live-log-table">
      <thead><tr><th>Time</th><th>Severity</th><th>Source</th><th>Message</th></tr></thead>
      <tbody id="live-log-body"></tbody>
    </table></div>
  </div>
  <div class="log-gap"></div>
  <div class="panel"><div class="panel-head"><h2>Timeline</h2><span>Metropolitan operations</span></div><div class="event-timeline">
    <div><time>11:52:09</time><b>HMI-OPS-01</b><span>Metropolitan operator interface refresh completed</span><em>INFO</em></div>
    <div><time>11:46:03</time><b>TK-CP-01</b><span>Reservoir operating threshold advisory created</span><em class="warn-text">HIGH</em></div>
    <div><time>11:39:12</time><b>PRESS-SL-01</b><span>Pressure deviation detected in Saint Louis header</span><em class="warn-text">MED</em></div>
    <div><time>11:35:06</time><b>RTU-GW-07</b><span>Diagnostic state synchronized as SERVICE</span><em class="warn-text">SERVICE</em></div>
    <div><time>11:28:51</time><b>RTU-SL-02</b><span>Communication quality changed to DEGRADED</span><em class="warn-text">LOW</em></div>
    <div><time>11:21:44</time><b>HIST-01</b><span>Historian health check completed</span><em>INFO</em></div>
    <div><time>11:14:18</time><b>EWS-01</b><span>Engineering baseline synchronized</span><em>INFO</em></div>
    <div><time>11:08:44</time><b>RTU-SO-02</b><span>Communication latency threshold observed</span><em class="warn-text">MED</em></div>
    <div><time>10:58:09</time><b>P-SL-102</b><span>Standby availability test postponed</span><em>LOW</em></div>
    <div><time>10:54:27</time><b>BRS-01</b><span>Recovery catalog validation queued</span><em>INFO</em></div>
    <div><time>10:51:27</time><b>FLOW-CP-02</b><span>Flow variance advisory acknowledged</span><em>LOW</em></div>
    <div><time>10:43:18</time><b>QCS-SO-01</b><span>Quality sample synchronization restored</span><em>INFO</em></div>
    <div><time>10:31:33</time><b>HIST-01</b><span>Archive backlog returned to baseline</span><em>INFO</em></div>
    <div><time>10:14:22</time><b>BRS-01</b><span>Night recovery validation scheduled</span><em>INFO</em></div>
    <div><time>09:57:40</time><b>EWS-01</b><span>Project checksum baseline updated</span><em>INFO</em></div>
    <div><time>09:41:15</time><b>PLC-SL-02</b><span>Configuration verification completed</span><em>INFO</em></div>
    <div><time>09:19:08</time><b>PLC-CP-03</b><span>Reservoir auxiliary logic synchronized</span><em>INFO</em></div>
    <div><time>08:47:31</time><b>OT-AUTO-01</b><span>Maintenance scheduler cycle completed</span><em>INFO</em></div>
    <div><time>08:11:02</time><b>BRS-01</b><span>Recovery catalog integrity check passed</span><em>INFO</em></div>
    <div><time>07:48:36</time><b>HIST-01</b><span>Storage health validation passed</span><em>INFO</em></div>
    <div id="eventlog" class="session-event"><time>SESSION</time><b>HMI</b><span>No operator actions in current view.</span><em>SESSION</em></div>
  </div></div>
</section>

<section class="view" data-view="statistics">
  <div class="page-head"><div><div class="breadcrumb">Analysis / Statistics</div><h1>Metropolitan Operational Statistics</h1><p>Indicadores agregados de disponibilidad, eventos, mantenimiento y comportamiento por municipio.</p></div><div class="page-tools"><span class="tool-chip">24 H</span><span class="tool-chip">3 MUNICIPALITIES</span></div></div>
  <div class="live-statistics-strip">
    <div><span>LIVE SUPPLY</span><strong data-live-stat="supply" data-decimals="0">—</strong><small>L/s</small></div>
    <div><span>LIVE DEMAND</span><strong data-live-stat="demand" data-decimals="0">—</strong><small>L/s</small></div>
    <div><span>AVG PRESSURE</span><strong data-live-stat="pressure" data-decimals="2">—</strong><small>bar</small></div>
    <div><span>AVG RESERVE</span><strong data-live-stat="reserve" data-decimals="1">—</strong><small>%</small></div>
    <div><span>ENERGY</span><strong data-live-stat="energy" data-decimals="1">—</strong><small>MW</small></div>
    <div><span>ACTIVE ALARMS</span><strong data-live-stat="active" data-decimals="0">—</strong></div>
    <div><span>RTU LATENCY</span><strong data-live-stat="latency" data-decimals="0">—</strong><small>ms</small></div>
    <div><span>AVAILABILITY</span><strong data-live-stat="availability" data-decimals="2">—</strong><small>%</small></div>
    <div><span>RUNNING PUMPS</span><strong data-live-stat="runningPumps" data-decimals="0">—</strong><small>/ 46</small></div>
    <div class="live-stat-updated"><span>LAST RECALC</span><strong data-live-updated>--:--:--</strong><small>1.2 s cycle</small></div>
  </div>
  <div class="statistics-grid">
    <section class="panel"><div class="panel-head"><h2>Availability</h2><span>24 hours</span></div><div class="big-stat"><?=$availability?>%</div><div class="stat-lines"><span>PLC 100%</span><span>RTU 98.7%</span><span>OT Services 100%</span></div></section>
    <section class="panel"><div class="panel-head"><h2>Alarm Distribution</h2><span>By municipality</span></div><div class="horizontal-bars"><div><span>Cerro de San Pablo</span><i style="width:48%"></i><b>2</b></div><div><span>Saint Louis</span><i style="width:72%"></i><b>3</b></div><div><span>Soledade</span><i style="width:48%"></i><b>2</b></div><div><span>Metropolitan</span><i style="width:38%"></i><b>2</b></div></div></section>
    <section class="panel"><div class="panel-head"><h2>Event Severity</h2><span>Last 24 hours</span></div><div class="donut-stat"><div class="donut"></div><div><span>High 4%</span><span>Medium 16%</span><span>Low 22%</span><span>Info 58%</span></div></div></section>
    <section class="panel"><div class="panel-head"><h2>Maintenance Activity</h2><span>Last 24 hours</span></div><div class="big-stat">11</div><div class="stat-lines"><span>8 completed</span><span>2 scheduled</span><span>1 active</span></div></section>
    <section class="panel wide"><div class="panel-head"><h2>Top Event-Producing Assets</h2><span>Operational records</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Asset</th><th>Class</th><th>Events</th><th>Warnings</th><th>Availability</th></tr></thead><tbody><tr><td>RTU-GW-07</td><td>Gateway</td><td>21</td><td>3</td><td>99.4%</td></tr><tr><td>HIST-01</td><td>Historian</td><td>17</td><td>1</td><td>100%</td></tr><tr><td>PLC-SL-02</td><td>PLC</td><td>14</td><td>2</td><td>100%</td></tr><tr><td>RTU-SL-02</td><td>RTU</td><td>12</td><td>4</td><td>96.8%</td></tr><tr><td>BRS-01</td><td>Recovery</td><td>9</td><td>0</td><td>100%</td></tr></tbody></table></div></section>
    <section class="panel wide"><div class="panel-head"><h2>Municipal Operating Profile</h2><span>Current period</span></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Municipality</th><th>Supply</th><th>Demand</th><th>Reserve</th><th>Pressure</th><th>Alarms</th><th>Availability</th></tr></thead><tbody><tr><td>Cerro de San Pablo</td><td>2,380 L/s</td><td>2,248 L/s</td><td>78%</td><td>4.1 bar</td><td>4</td><td>99.91%</td></tr><tr><td>Saint Louis</td><td>3,420 L/s</td><td>3,239 L/s</td><td>80%</td><td>4.3 bar</td><td>7</td><td>99.72%</td></tr><tr><td>Soledade</td><td>2,580 L/s</td><td>2,349 L/s</td><td>80%</td><td>4.2 bar</td><td>4</td><td>99.83%</td></tr></tbody></table></div></section>

    <section class="panel wide"><div class="panel-head"><h2>24h Water Production</h2><span>Hourly metropolitan output</span></div><div class="area-chart"><svg viewBox="0 0 900 220" preserveAspectRatio="none"><path class="chart-grid" d="M0 55H900M0 110H900M0 165H900"/><path class="area-fill" d="M0 170 C70 155 100 150 150 142 S250 118 310 126 S400 97 470 105 S560 76 640 88 S750 64 820 78 S870 70 900 72 L900 220 L0 220Z"/><path class="area-line" d="M0 170 C70 155 100 150 150 142 S250 118 310 126 S400 97 470 105 S560 76 640 88 S750 64 820 78 S870 70 900 72"/></svg><div class="chart-caption"><span>00:00</span><span>06:00</span><span>12:00</span><span>18:00</span><span>24:00</span></div></div></section>

    <section class="panel"><div class="panel-head"><h2>Controller Fleet</h2><span>Operational state</span></div><div class="stacked-stat"><div><i style="width:82%"></i><em style="width:12%"></em><b style="width:6%"></b></div><span>Online 82%</span><span>Maintenance 12%</span><span>Degraded 6%</span></div></section>

    <section class="panel"><div class="panel-head"><h2>Energy by Municipality</h2><span>Current MW</span></div><div class="vertical-bars"><div><i style="height:68%"></i><span>CP</span><b>4.8</b></div><div><i style="height:92%"></i><span>SL</span><b>6.5</b></div><div><i style="height:76%"></i><span>SO</span><b>5.4</b></div></div></section>

    <section class="panel"><div class="panel-head"><h2>Maintenance by Category</h2><span>7 days</span></div><div class="horizontal-bars compact"><div><span>Configuration</span><i style="width:82%"></i><b>18</b></div><div><span>Firmware</span><i style="width:36%"></i><b>8</b></div><div><span>Recovery</span><i style="width:50%"></i><b>11</b></div><div><span>Comms</span><i style="width:64%"></i><b>14</b></div></div></section>

    <section class="panel"><div class="panel-head"><h2>Alarm Heatmap</h2><span>24 hours</span></div><div class="heatmap"><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i><i class="h1"></i><i class="h3"></i><i class="h0"></i><i class="h2"></i><i class="h4"></i></div><div class="heatmap-labels"><span>00h</span><span>06h</span><span>12h</span><span>18h</span><span>24h</span></div></section>

    <section class="panel wide"><div class="panel-head"><h2>Demand Share by Service Area</h2><span>Current load</span></div><div class="service-share"><div class="share-donut"></div><div><p><b>Cerro de San Pablo</b><span>29%</span></p><p><b>Saint Louis</b><span>41%</span></p><p><b>Soledade</b><span>30%</span></p></div></div></section>

    <section class="panel wide"><div class="panel-head"><h2>Network Capacity Utilization</h2><span>Major systems</span></div><div class="capacity-list"><div><span>Primary trunk</span><i><b style="width:72%"></b></i><em>72%</em></div><div><span>Storage</span><i><b style="width:79%"></b></i><em>79%</em></div><div><span>Pumping</span><i><b style="width:68%"></b></i><em>68%</em></div><div><span>Historian ingestion</span><i><b style="width:57%"></b></i><em>57%</em></div><div><span>RTU communications</span><i><b style="width:63%"></b></i><em>63%</em></div></div></section>
  </div>
</section>

<div class="footer-note">Entorno operacional metropolitano simulado con fines académicos. Ninguna acción se comunica con infraestructura física.</div>
</div></main>
</div>

<script src="/operations/assets/ops-client.php"></script>
<script>window.INTERAFAS_STATIONS=<?=json_encode($stations,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script>
<script src="/operations/assets/hmi-v4.js"></script>
<script src="/operations/assets/hmi-v5-live.js"></script>\n<script src="/operations/assets/hmi-v6-scale.js"></script>
<script src="/operations/assets/hmi-ru.js"></script>
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
  const set=(id,val)=>{const el=document.getElementById(id);if(el)el.textContent=val;};
  document.querySelectorAll('[data-current-station]').forEach(el=>el.textContent=s.id);
  document.querySelectorAll('[data-current-city]').forEach(el=>el.textContent=s.city);
  document.querySelectorAll('[data-current-controller]').forEach(el=>el.textContent=s.controller);
  document.querySelectorAll('[data-current-gateway]').forEach(el=>el.textContent=s.gateway);
  document.querySelectorAll('[data-current-function]').forEach(el=>el.textContent=s.function);
  set('station-id',s.id);set('station-city',s.city);set('station-controller',s.controller);set('station-gateway',s.gateway);
  if(window.INTERAFAS_LIVE)window.INTERAFAS_LIVE.setStation(index);
  showView('process');
}

document.querySelectorAll('[data-station-open]').forEach(btn=>btn.addEventListener('click',()=>openStation(Number(btn.dataset.stationOpen))));
document.querySelectorAll('[data-station-card]').forEach((card,i)=>{card.style.cursor='pointer';card.addEventListener('click',e=>{if(e.target.closest('button'))return;openStation(i);});});

document.getElementById('firmware-nav')?.addEventListener('click',e=>{
  const nav=e.currentTarget;
  if(nav.dataset.unlocked==='1')return;
  e.preventDefault();
  showView('assets');
});

async function loadAssetContext(assetId,fallback=null){
  const set=(id,val)=>{const el=document.getElementById(id);if(el)el.textContent=val??'—';};
  const related=document.getElementById('asset-detail-related');
  const chainState=document.getElementById('asset-chain-state');
  const chainProgress=document.getElementById('asset-chain-progress');
  const chainTrack=document.getElementById('asset-chain-track');
  const firmwareContext=document.getElementById('asset-firmware-context');
  const firmwareProfile=document.getElementById('asset-firmware-profile');
  const firmwareWarning=document.getElementById('asset-firmware-warning');

  try{
    const url=window.INTERAFAS_OPS.assetContextEndpoint+'?asset='+encodeURIComponent(assetId);
    const response=await fetch(url,{credentials:'same-origin',cache:'no-store'});
    const data=await response.json();

    if(!response.ok || !data.ok)throw new Error(data.error||'asset-context-unavailable');

    set('asset-detail-id',data.asset);
    set('asset-detail-type',data.type);
    set('asset-detail-station',data.station);
    set('asset-detail-zone',data.zone);
    set('asset-detail-function',data.function);
    set('asset-detail-vendor','Operational dependency model');
    set('asset-detail-class',data.dependency_class);

    if(related){
      related.innerHTML='';
      (data.related||[]).forEach(id=>{
        const btn=document.createElement('button');
        btn.type='button';
        btn.className='asset-related-link';
        btn.dataset.relatedAsset=id;
        btn.textContent=id;
        related.appendChild(btn);
      });
      if(!(data.related||[]).length)related.textContent='—';
    }

    if(chainState && chainProgress && chainTrack){
      const total=Number(data.path?.sequence_length||6);
      const depth=Number(data.path?.depth||0);
      const failed=!!data.path?.error;
      const complete=!!data.path?.complete;

      chainState.classList.toggle('complete',complete);
      chainState.classList.toggle('error',failed);
      chainProgress.textContent=failed
        ? 'ОШИБКА · 0/'+total
        : (complete ? 'ЗАВЕРШЕНО · '+depth+'/'+total : depth+'/'+total);

      [...chainTrack.children].forEach((segment,index)=>{
        segment.classList.toggle('active',!failed && index<depth);
        segment.classList.toggle('error',failed);
      });
    }

    if(firmwareContext && firmwareProfile && firmwareWarning){
      const ctx=data.firmware_context;
      firmwareContext.hidden=!ctx;
      if(ctx){
        firmwareProfile.textContent=ctx.legacy_profile+' · '+ctx.device;
        firmwareWarning.textContent=ctx.warning||'';

        const firmwareNav=document.getElementById('firmware-nav');
        if(firmwareNav){
          firmwareNav.href='firmware.php';
          firmwareNav.dataset.unlocked='1';
          firmwareNav.classList.remove('firmware-locked');
          const label=firmwareNav.querySelector('span:last-child');
          if(label)label.textContent='Firmware';
        }
      }
    }
  }catch(err){
    if(!fallback)return;
    set('asset-detail-id',fallback.id);
    set('asset-detail-type',fallback.type);
    set('asset-detail-station',fallback.station);
    set('asset-detail-zone',fallback.zone);
    set('asset-detail-function',fallback.function);
    set('asset-detail-vendor',fallback.vendor);
    set('asset-detail-class','—');
    if(related)related.textContent=fallback.related||'—';
    if(firmwareContext)firmwareContext.hidden=true;
    if(chainState && chainProgress && chainTrack){
      chainState.classList.remove('complete');
      chainState.classList.add('error');
      chainProgress.textContent='ОШИБКА';
      [...chainTrack.children].forEach(segment=>{
        segment.classList.remove('active');
        segment.classList.add('error');
      });
    }
  }
}

document.querySelectorAll('[data-asset-row]').forEach(row=>row.addEventListener('click',e=>{
  e.stopImmediatePropagation();
  const a=JSON.parse(row.dataset.assetRow);
  loadAssetContext(a.id,a);
}));

document.getElementById('asset-detail-related')?.addEventListener('click',e=>{
  const btn=e.target.closest('[data-related-asset]');
  if(!btn)return;
  loadAssetContext(btn.dataset.relatedAsset);
});

document.querySelectorAll('.eq-click').forEach(el=>el.addEventListener('click',()=>{
  const name=el.dataset.equipment||'Process asset';
  if(window.INTERAFAS_HMI?.openDrawer){
    window.INTERAFAS_HMI.openDrawer(name,'LIVE PROCESS ASSET',
      '<div class="drawer-kpi">'+name+'</div><p>Live asset context from the selected station process cell.</p><div class="drawer-meta"><span>Station</span><b>'+((document.querySelector('[data-current-station]')||{}).textContent||'—')+'</b><span>Controller</span><b>'+((document.querySelector('[data-current-controller]')||{}).textContent||'—')+'</b><span>State</span><b class="ok-text">ONLINE</b><span>Telemetry</span><b>LIVE</b></div>');
  }
}));

async function refreshTelemetry(){
  try{
    const r=await fetch(window.INTERAFAS_OPS.telemetryEndpoint,{cache:'no-store'}),d=await r.json();if(!d.ok)return;
    const set=(id,val)=>{const n=document.getElementById(id);if(n)n.textContent=val;};
    set('tank',(d.tank??'—')+'%');
    set('flow',(d.flow??'—')+' L/s');
    set('pressure',(d.pressure??'—')+' bar');
    set('p101',d.p101??'—');
    if(window.INTERAFAS_LIVE?.applyTelemetry)window.INTERAFAS_LIVE.applyTelemetry(d);
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
