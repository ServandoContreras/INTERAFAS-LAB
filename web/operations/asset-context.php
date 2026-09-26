<?php
require __DIR__.'/common.php';
require_operational_network(true);
$ops=require_operational_auth(true);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-INTERAFAS-Asset-Context: operational-dependency');

$asset=strtoupper(trim((string)($_GET['asset']??'')));
$role=(string)($ops['role']??'');

$map=[
 'RTU-GW-07'=>[
   'type'=>'RTU / Gateway','station'=>'Metropolitan','zone'=>'OT Edge',
   'function'=>'Remote operations gateway','status'=>'ONLINE',
   'relation_class'=>'UPSTREAM / CONTROL',
   'related'=>['HMI-OPS-01','BRS-01','PLC-SL-01']
 ],
 'PLC-SL-01'=>[
   'type'=>'PLC','station'=>'Saint Louis','zone'=>'Area Control',
   'function'=>'Primary process control','status'=>'ONLINE',
   'relation_class'=>'CONTROL',
   'related'=>['RTU-SL-01','P-SL-101','P-SL-102','V-SL-201']
 ],
 'P-SL-101'=>[
   'type'=>'Pump','station'=>'Saint Louis','zone'=>'Process',
   'function'=>'Primary high-lift pumping','status'=>'RUNNING',
   'relation_class'=>'PROCESS',
   'related'=>['PLC-SL-01','HDR-SL-01']
 ],
 'HDR-SL-01'=>[
   'type'=>'Hydraulic Header','station'=>'Saint Louis','zone'=>'Distribution',
   'function'=>'Metropolitan distribution header','status'=>'PRESSURIZED',
   'relation_class'=>'DISTRIBUTION',
   'related'=>['P-SL-101','V-SL-201','V-SL-202']
 ],
 'V-SL-201'=>[
   'type'=>'Control Valve','station'=>'Saint Louis','zone'=>'Distribution',
   'function'=>'Zone A distribution control','status'=>'OPEN',
   'relation_class'=>'FIELD',
   'related'=>['HDR-SL-01','ZONE-SL-A']
 ],
 'ZONE-SL-A'=>[
   'type'=>'Service Zone','station'=>'Saint Louis','zone'=>'Distribution',
   'function'=>'Zone A hydraulic service area','status'=>'NORMAL',
   'relation_class'=>'TERMINAL',
   'related'=>['V-SL-201']
 ],
 'HMI-OPS-01'=>[
   'type'=>'HMI','station'=>'Metropolitan','zone'=>'Control Room',
   'function'=>'Operations console','status'=>'ONLINE',
   'relation_class'=>'SUPERVISION',
   'related'=>['HIST-01','RTU-GW-07']
 ],
 'BRS-01'=>[
   'type'=>'Backup / Recovery','station'=>'Metropolitan','zone'=>'OT Services',
   'function'=>'Configuration recovery','status'=>'ONLINE',
   'relation_class'=>'SERVICE',
   'related'=>['EWS-01','RTU-GW-07']
 ],
 'RTU-SL-01'=>[
   'type'=>'RTU','station'=>'Saint Louis','zone'=>'OT Edge',
   'function'=>'Station communications','status'=>'ONLINE',
   'relation_class'=>'COMMUNICATIONS',
   'related'=>['PLC-SL-01','RTU-GW-07']
 ],
 'P-SL-102'=>[
   'type'=>'Pump','station'=>'Saint Louis','zone'=>'Process',
   'function'=>'Standby pumping','status'=>'STANDBY',
   'relation_class'=>'PROCESS',
   'related'=>['PLC-SL-01','HDR-SL-01']
 ],
 'V-SL-202'=>[
   'type'=>'Control Valve','station'=>'Saint Louis','zone'=>'Distribution',
   'function'=>'Zone B distribution control','status'=>'OPEN',
   'relation_class'=>'FIELD',
   'related'=>['HDR-SL-01']
 ]
];

if($asset==='' || !isset($map[$asset])){
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'asset-context-not-found'],JSON_UNESCAPED_UNICODE);
  exit;
}

/*
 * Maintenance sessions require dependency visibility for diagnostics, but the
 * service does not enforce the intended boundary between service context and
 * full process topology. VULN-19 is demonstrated by traversing one complete
 * dependency path through the HMI.
 */
$sequence=['RTU-GW-07','PLC-SL-01','P-SL-101','HDR-SL-01','V-SL-201','ZONE-SL-A'];
$progress=is_array($_SESSION['vuln19_dependency_path']??null) ? $_SESSION['vuln19_dependency_path'] : [];
$pathError=false;
$errorAsset=null;

if($role==='maintenance'){
  if($asset===$sequence[0]){
    $progress=[$asset];
  }elseif($progress===$sequence && $asset===$sequence[count($sequence)-1]){
    // Keep the completed chain stable while the terminal asset remains selected.
  }else{
    $expected=$sequence[count($progress)]??null;
    if($expected!==null && $asset===$expected){
      $progress[]=$asset;
    }else{
      $pathError=true;
      $errorAsset=$asset;
      $progress=[];
    }
  }
  $_SESSION['vuln19_dependency_path']=$progress;
}

$complete=($role==='maintenance' && !$pathError && $progress===$sequence);

if($complete){
  $_SESSION['vuln19_complete']=true;
  $_SESSION['vuln19_completed_at']=$_SESSION['vuln19_completed_at']??time();

  header('X-INTERAFAS-Dependency-Chain: complete');
  header('X-INTERAFAS-Authorization-Scope: exceeded');
  header('X-INTERAFAS-Validation: UPSLP_CNOIV-CHAIN-REACTION-19');

  lab_event(
    'VULN19_DEPENDENCY_CHAIN_COMPLETED',
    'Perfil de mantenimiento recorrió una cadena completa de dependencias OT',
    implode(' → ',$sequence),
    [
      'challenge'=>19,
      'role'=>$role,
      'sequence'=>$sequence,
      'authorization_scope'=>'maintenance',
      'exposed_scope'=>'full-process-dependency'
    ],
    'ot-hmi',
    'critical',
    0
  );
}

$data=$map[$asset];

echo json_encode([
  'ok'=>true,
  'asset'=>$asset,
  'type'=>$data['type'],
  'station'=>$data['station'],
  'zone'=>$data['zone'],
  'function'=>$data['function'],
  'status'=>$data['status'],
  'dependency_class'=>$data['relation_class'],
  'related'=>$data['related'],
  'access'=>[
    'role'=>$role,
    'scope'=>'maintenance-context',
    'server_enforced_dependency_boundary'=>false
  ],
  'path'=>[
    'sequence_length'=>count($sequence),
    'visited'=>$progress,
    'depth'=>count($progress),
    'complete'=>$complete,
    'error'=>$pathError,
    'error_asset'=>$errorAsset,
    'expected_next'=>$complete ? null : ($sequence[count($progress)]??$sequence[0])
  ],
  'firmware_context'=>$complete ? [
    'device'=>'RTU-GW-07',
    'legacy_profile'=>'CASCADE',
    'classification'=>'retired-metropolitan-stress-test',
    'channel'=>'/operations/firmware.php',
    'backup'=>'/operations/firmware.php?backup=1',
    'warning'=>'Legacy profile must never be deployed to an operational gateway.'
  ] : null
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
