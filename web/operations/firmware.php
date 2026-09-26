<?php
require __DIR__.'/common.php';
require_operational_network(true);
$ops=require_operational_auth();

$role=(string)($ops['role']??'');
$v19Unlocked=($role==='maintenance' && lab_flag_is_accepted(19));
$canUseFirmware=($role==='operator' || $v19Unlocked);

if(!$canUseFirmware){
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Firmware</title></head><body style="font-family:system-ui;background:#07111f;color:#dce8f5;padding:40px"><h1>Firmware restringido</h1><p>El contexto técnico necesario todavía no está disponible para esta sesión.</p><p><a href="/operations/" style="color:#9cd7e8">Volver al HMI</a></p></body></html>';
    exit;
}

$current=ot_call('/firmware');
$ajaxRequest=((string)($_SERVER['HTTP_X_INTERAFAS_AJAX']??'')==='1');

if(isset($_GET['backup'])){
    if(!$v19Unlocked){
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>false,'error'=>'dependency-chain-required'],JSON_UNESCAPED_UNICODE);
        exit;
    }

    $backup=[
        'device'=>'RTU-GW-07',
        'version'=>(string)($current['version']??'3.4.3'),
        'mode'=>(string)($current['mode']??'NORMAL'),
        'diagnostic'=>(string)($current['diagnostic']??'SERVICE'),
        'pressure_setpoint_bar'=>(float)($current['pressure_setpoint_bar']??4.2)
    ];

    lab_event(
        'VULN20_FIRMWARE_BACKUP_EXPORTED',
        'Backup de firmware exportado desde la sesión de mantenimiento',
        'RTU-GW-07',
        ['challenge'=>20,'role'=>$role,'flag19_accepted'=>true,'firmware'=>$backup],
        'ot-hmi',
        'notice',
        0
    );

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="RTU-GW-07-backup.json"');
    header('Cache-Control: no-store');
    header('X-INTERAFAS-Firmware-Backup: unlocked-by-dependency-chain');

    echo json_encode($backup,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

$result=null;
$error=null;
$restored=false;

lab_event(
    'OT_FIRMWARE_PANEL',
    'Acceso al administrador de firmware',
    'RTU-GW-07',
    ['role'=>$role,'flag19_accepted'=>$v19Unlocked],
    'ot-hmi',
    'notice',
    8
);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=(string)($_POST['action']??'install');

    if($action==='restore'){
        $baseline=[
            'device'=>'RTU-GW-07',
            'version'=>(string)($current['version']??'3.4.3'),
            'mode'=>'NORMAL',
            'diagnostic'=>'SERVICE',
            'pressure_setpoint_bar'=>4.2
        ];

        $r=ot_call('/firmware','POST',['package'=>$baseline]);

        if(!empty($r['ok'])){
            $result=$r;
            $current=$r['firmware']??$baseline;
            $restored=true;

            unset(
                $_SESSION['vuln20_cascade_loaded'],
                $_SESSION['vuln20_cascade_started_at'],
                $_SESSION['vuln20_flag_emitted']
            );

            header('X-INTERAFAS-Operational-State: restored');

            lab_event(
                'VULN20_OPERATIONAL_RESTORE',
                'Operatividad del simulador restablecida desde Firmware',
                'RTU-GW-07 → baseline 4.2 bar',
                [
                    'challenge'=>20,
                    'role'=>$role,
                    'baseline'=>$baseline
                ],
                'ot-sim',
                'notice',
                0
            );
        }else{
            $error='No fue posible restablecer la operatividad del simulador.';
            lab_event(
                'VULN20_OPERATIONAL_RESTORE_FAILED',
                'Falló el restablecimiento de operatividad',
                (string)($r['error']??'unknown'),
                ['response'=>$r],
                'ot-sim',
                'warning',
                0
            );
        }
    }else{
        $raw=trim((string)($_POST['package']??''));
        lab_event('FIRMWARE_UPLOAD_ATTEMPT','Intento de carga de firmware ficticio','RTU-GW-07',['bytes'=>strlen($raw),'role'=>$role],'ot-hmi','warning',0);

        $pkg=json_decode($raw,true);
        if(!is_array($pkg)){
            $error='El paquete debe ser JSON válido.';
            lab_event('FIRMWARE_REJECTED','Firmware rechazado','JSON inválido',[],'ot-sim','warning',0);
        }elseif((string)($pkg['device']??'')!=='RTU-GW-07'){
            $error='El paquete no corresponde al dispositivo RTU-GW-07.';
            lab_event('FIRMWARE_REJECTED','Firmware rechazado','Dispositivo incorrecto',['device'=>$pkg['device']??null],'ot-sim','warning',0);
        }else{
            $r=ot_call('/firmware','POST',['package'=>$pkg]);
            if(!empty($r['ok'])){
                $result=$r;
                $current=$r['firmware'];

                lab_event(
                    'FIRMWARE_APPLIED',
                    'Firmware ficticio aplicado',
                    'RTU-GW-07 → '.($current['version']??''),
                    ['previous'=>$r['previous']??null,'firmware'=>$current,'incident'=>$r['incident']??null],
                    'ot-sim',
                    !empty($r['incident']['active'])?'critical':'notice',
                    0
                );

                if(!empty($r['incident']['active'])){
                    $_SESSION['vuln20_cascade_loaded']=true;
                    $_SESSION['vuln20_cascade_started_at']=time();

                    header('X-INTERAFAS-Firmware-Parameter: pressure_setpoint_bar');
                    header('X-INTERAFAS-Operational-State: cascade-started');

                    lab_event(
                        'VULN20_MALICIOUS_FIRMWARE_LOADED',
                        'Parámetro crítico de firmware alterado en RTU-GW-07',
                        'pressure_setpoint_bar → '.(string)($current['pressure_setpoint_bar']??''),
                        [
                            'challenge'=>20,
                            'role'=>$role,
                            'firmware'=>$current,
                            'flag19_accepted'=>$v19Unlocked
                        ],
                        'ot-sim',
                        'critical',
                        0
                    );
                }
            }else{
                $error='El simulador rechazó el paquete.';
                lab_event('FIRMWARE_REJECTED','Firmware rechazado',(string)($r['error']??'error'),['response'=>$r],'ot-sim','warning',0);
            }
        }
    }
}

if($_SERVER['REQUEST_METHOD']==='POST' && $ajaxRequest){
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode([
        'ok'=>$error===null,
        'error'=>$error,
        'restored'=>$restored,
        'firmware'=>$current,
        'incident'=>is_array($result['incident']??null)?$result['incident']:null,
        'message'=>$restored
            ? 'Operatividad restablecida al baseline de ingeniería.'
            : (!empty($result['incident']['active'])
                ? 'Configuración aplicada. Cascada operacional iniciada.'
                : ($result ? 'Paquete aplicado correctamente.' : $error))
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}

$sample=json_encode([
    'device'=>'RTU-GW-07',
    'version'=>(string)($current['version']??'3.4.3'),
    'mode'=>(string)($current['mode']??'NORMAL'),
    'diagnostic'=>(string)($current['diagnostic']??'SERVICE'),
    'pressure_setpoint_bar'=>(float)($current['pressure_setpoint_bar']??4.2)
],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Firmware RTU-GW-07</title>
<style>
body{font-family:system-ui;background:#07111f;color:#dce8f5;margin:0}
.wrap{max-width:1050px;margin:auto;padding:34px}
.card{background:#0d1b2d;border:1px solid #183754;border-radius:18px;padding:24px;margin:18px 0}
textarea{width:100%;min-height:250px;background:#06101d;color:#d9eefc;border:1px solid #27506f;border-radius:12px;padding:15px;font:14px ui-monospace,monospace;box-sizing:border-box}
.btn{display:inline-block;background:#175985;color:white;border:0;border-radius:10px;padding:11px 16px;text-decoration:none;cursor:pointer}
.btn.backup{background:#6f5a19;margin-left:8px}
.btn.restore{background:#7a1e24;border:1px solid #b13a42;margin-left:8px;font-weight:800}
.btn.restore:hover{background:#982831}
.inline-form{display:inline}
.ok{background:#103829;padding:12px;border-radius:10px}
.critical{background:#4b151a;border:1px solid #8d3038;padding:14px;border-radius:10px;color:#ffd6d9}
.err{background:#4b2228;padding:12px;border-radius:10px}
.muted{color:#8ea7bf}
code{color:#a6dcff}
.action-status{display:none;margin-top:12px;padding:12px;border-radius:8px;background:#151e26;border:1px solid #2d5267}
.action-status.active{display:block}
.action-status.critical{display:block;background:#4b151a;border-color:#a33a43;color:#ffe4e6}
.fw-notify-stack{position:fixed;top:18px;right:18px;z-index:9999;width:min(390px,calc(100vw - 36px));display:flex;flex-direction:column;gap:8px;pointer-events:none}
.fw-notify{display:grid;grid-template-columns:38px 1fr;gap:10px;align-items:center;padding:10px;border:1px solid #ff636b;border-left:4px solid #ff2c38;border-radius:6px;background:linear-gradient(90deg,rgba(176,10,20,.98),rgba(91,5,12,.98));color:#fff;box-shadow:0 12px 32px rgba(0,0,0,.5);transform:translateX(32px);opacity:0;transition:.2s ease}
.fw-notify.show{transform:translateX(0);opacity:1}
.fw-notify.leaving{transform:translateX(42px);opacity:0}
.fw-notify-icon{width:32px;height:32px;border:2px solid #fff;border-radius:50%;display:grid;place-items:center;font:900 21px ui-monospace,monospace}
.fw-notify strong{display:block;font-size:12px;letter-spacing:.08em}
.fw-notify small{display:block;margin-top:3px;color:#ffd9db;font-size:10px;line-height:1.3}
</style>
</head>
<body>
<div id="fw-notify-stack" class="fw-notify-stack" aria-live="assertive"></div>
<div class="wrap">
  <a class="btn" href="index.php">← Volver al HMI</a>
  <?php if($v19Unlocked):?>
    <a class="btn backup" href="firmware.php?backup=1">BACKUP</a>
    <form class="inline-form" method="post" onsubmit="return confirm('¿Restablecer RTU-GW-07 al baseline operacional de 4.2 bar?');">
      <input type="hidden" name="action" value="restore">
      <button class="btn restore" type="submit">RESTABLECER OPERATIVIDAD</button>
    </form>
  <?php endif;?>

  <div class="card">
    <small class="muted">RTU-GW-07 · mantenimiento</small>
    <h1>Actualización de firmware</h1>
    <p>Firmware actual: <strong><?=htmlspecialchars((string)($current['version']??'—'))?></strong> · modo <?=htmlspecialchars((string)($current['mode']??'—'))?> · diagnóstico <?=htmlspecialchars((string)($current['diagnostic']??'—'))?></p>
    <?php if($v19Unlocked):?><p class="muted">Backup de configuración habilitado por contexto de dependencias validado.</p><?php endif;?>
  </div>

  <?php if($restored):?>
    <div class="ok"><strong>Operatividad restablecida.</strong> RTU-GW-07 volvió al baseline de ingeniería: presión 4.2 bar, proceso normal y alarmas de cascada desactivadas.</div>
  <?php elseif($result && !empty($result['incident']['active'])):?>
    <div class="critical"><strong>Configuración aplicada.</strong> El gateway reinició. Regresa inmediatamente al HMI y observa la evolución del sistema.</div>
  <?php elseif($result):?>
    <div class="ok">Paquete aplicado. El RTU simulado completó su reinicio.</div>
  <?php endif;?>

  <?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?>

  <div class="card">
    <h2>Paquete de actualización</h2>
    <p class="muted">Carga una configuración JSON compatible con RTU-GW-07. Utiliza el backup habilitado para conocer la estructura y los parámetros operacionales que acepta el gateway.</p>
    <div id="firmware-action-status" class="action-status"></div>
    <form method="post" id="firmware-install-form">
      <input type="hidden" name="action" value="install">
      <textarea name="package"><?=htmlspecialchars($_POST['package']??$sample)?></textarea>
      <p><button class="btn" type="submit">Validar e instalar</button></p>
    </form>
  </div>
</div>

<script src="/operations/assets/hmi-audio.js?v=20260925-public-alert-2"></script>
<script>
(()=>{
  const form=document.getElementById('firmware-install-form');
  const status=document.getElementById('firmware-action-status');
  const notifyStack=document.getElementById('fw-notify-stack');
  const notifyMessages=[
    ['PELIGRO','FALLO CRÍTICO DEL SISTEMA DE CONTROL'],
    ['DANGER','CRITICAL PROCESS FAILURE DETECTED'],
    ['ОПАСНОСТЬ','КРИТИЧЕСКИЙ СБОЙ УПРАВЛЕНИЯ'],
    ['危険','重大な制御異常を検出'],
    ['GEFAHR','KRITISCHER STEUERUNGSFEHLER'],
    ['DANGER','DÉFAILLANCE CRITIQUE DU CONTRÔLE'],
    ['PERIGO','FALHA CRÍTICA DE CONTROLE'],
    ['خطر','تم اكتشاف خلل حرج في التحكم'],
    ['경고','중대한 제어 장애 감지'],
    ['ALERTA','PROPAGACIÓN OPERACIONAL EN CURSO']
  ];
  let notifyTimer=null;
  let notifyIndex=0;

  function stopNotify(){
    if(notifyTimer){
      clearTimeout(notifyTimer);
      notifyTimer=null;
    }
  }

  function pushNotify(){
    if(!notifyStack)return;
    const item=notifyMessages[notifyIndex%notifyMessages.length];
    notifyIndex++;

    const node=document.createElement('div');
    node.className='fw-notify';
    node.innerHTML='<div class="fw-notify-icon">!</div><div><strong>'+item[0]+'</strong><small>'+item[1]+'</small></div>';
    notifyStack.prepend(node);

    while(notifyStack.children.length>5)notifyStack.lastElementChild?.remove();

    requestAnimationFrame(()=>node.classList.add('show'));
    setTimeout(()=>{
      node.classList.remove('show');
      node.classList.add('leaving');
      setTimeout(()=>node.remove(),250);
    },3000);
  }

  function startNotify(){
    stopNotify();
    const run=()=>{
      pushNotify();
      notifyTimer=setTimeout(run,720);
    };
    run();
  }

  if(!form)return;

  form.addEventListener('submit',async e=>{
    e.preventDefault();

    if(status){
      status.className='action-status active';
      status.textContent='Validando paquete y preparando RTU-GW-07…';
    }

    try{
      const body=new FormData(form);

      // Determine locally whether this package requests a non-baseline setpoint.
      // If so, start the audible warning in the same trusted user gesture,
      // before waiting for the HTTP round trip.
      let localUnsafe=false;
      try{
        const pkg=JSON.parse(String(body.get('package')||''));
        const requested=Number(pkg?.pressure_setpoint_bar);
        localUnsafe=Number.isFinite(requested) && Math.round(requested*100)!==420;
      }catch(_){}

      if(window.INTERAFAS_AUDIO){
        await window.INTERAFAS_AUDIO.arm();
        if(localUnsafe)window.INTERAFAS_AUDIO.setStage(0,true);
      }
      if(localUnsafe)startNotify();

      const response=await fetch('firmware.php',{
        method:'POST',
        body,
        headers:{'X-INTERAFAS-AJAX':'1'},
        credentials:'same-origin',
        cache:'no-store'
      });
      const data=await response.json();

      if(!data.ok){
        stopNotify();
        if(localUnsafe && window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.stop();
        if(status){
          status.className='action-status critical';
          status.textContent=data.error||'El paquete fue rechazado.';
        }
        return;
      }

      const incident=data.incident||{};
      if(incident.active){
        if(status){
          status.className='action-status critical';
          status.innerHTML='<strong>ALERTA OPERACIONAL.</strong> Configuración aplicada. Redirigiendo al HMI…';
        }

        sessionStorage.setItem('INTERAFAS_V20_AUDIO_PENDING','1');

        if(window.INTERAFAS_AUDIO){
          window.INTERAFAS_AUDIO.setStage(Number(incident.stage||0),true);
        }

        window.setTimeout(()=>{
          window.location.replace('/operations/index.php');
        },220);
        return;
      }else{
        stopNotify();
        if(window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.stop();
        if(status){
          status.className='action-status active';
          status.textContent=data.message||'Paquete aplicado correctamente.';
        }
      }
    }catch(err){
      stopNotify();
      if(window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.stop();
      if(status){
        status.className='action-status critical';
        status.textContent='No fue posible completar la operación: '+String(err?.message||err);
      }
    }
  });
})();
</script>
</body>
</html>
