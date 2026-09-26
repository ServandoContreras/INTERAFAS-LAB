<?php
require __DIR__.'/common.php';
require_operational_network(true);
$ops=require_operational_auth();

$role=(string)($ops['role']??'');
$labCtx=lab_context()??[];
$auditorName=trim(implode(' ',array_filter([
    (string)($labCtx['nombre']??''),
    (string)($labCtx['apellido_paterno']??''),
    (string)($labCtx['apellido_materno']??'')
])));
if($auditorName==='')$auditorName='Auditor';
$auditorMatricula=(string)($labCtx['matricula']??'—');

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
.evidence-card{border-color:#315875;background:linear-gradient(135deg,#0d1b2d,#10263a)}
.evidence-title{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.evidence-title h2{margin:0}
.evidence-badge{padding:5px 9px;border:1px solid #3e779a;border-radius:999px;background:#0a1623;color:#9fd8f5;font:800 10px ui-monospace,monospace;letter-spacing:.07em}
.evidence-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:16px 0}
.evidence-field{background:#081522;border:1px solid #1c4058;border-radius:10px;padding:11px}
.evidence-field span{display:block;color:#7996aa;font-size:10px;text-transform:uppercase;letter-spacing:.07em;margin-bottom:3px}
.evidence-field strong{font-size:14px}
.btn.evidence{background:#1d6f52;border:1px solid #318c6b;font-weight:800}
.btn.evidence:hover{background:#27825f}
.evidence-status{margin-top:12px;padding:10px 12px;border:1px solid #35566b;border-radius:9px;background:#091621;color:#9eb4c3;font-size:12px}
.evidence-status.ready{border-color:#2f7659;background:#0b261c;color:#9be6bd}
.evidence-status.warn{border-color:#8b6830;background:#2a1e0b;color:#f0d38a}
@media(max-width:680px){.evidence-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
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

  <div class="card evidence-card">
    <div class="evidence-title">
      <h2>Preparar evidencia ampliada</h2>
      <span class="evidence-badge">VULN-20 · EVIDENCIA FINAL</span>
    </div>
    <p>Antes de modificar el firmware puedes autorizar una captura de <strong>tu pantalla completa</strong>. Cuando el sistema alcance el estado catastrófico se tomará un único frame para documentar el momento final del ejercicio.</p>
    <div class="evidence-grid">
      <div class="evidence-field"><span>Auditor</span><strong><?=htmlspecialchars($auditorName)?></strong></div>
      <div class="evidence-field"><span>Matrícula</span><strong><?=htmlspecialchars($auditorMatricula)?></strong></div>
    </div>
    <p class="muted">El navegador mostrará su propio selector de compartición. Para la evidencia ampliada selecciona <strong>Pantalla completa</strong>. La captura incluirá únicamente lo que esté visible en ese monitor en ese instante; puedes detener la compartición cuando quieras.</p>
    <button class="btn evidence" id="prepare-screen-evidence" type="button">PREPARAR CAPTURA DE PANTALLA</button>
    <div id="screen-evidence-status" class="evidence-status">Evidencia ampliada no preparada. Si continúas así, se conservará únicamente la captura interna del HMI.</div>
  </div>

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

<script src="/operations/assets/hmi-audio.js?v=20260925-v20-6"></script>
<script>
(()=>{
  const form=document.getElementById('firmware-install-form');
  const status=document.getElementById('firmware-action-status');
  const prepareEvidenceBtn=document.getElementById('prepare-screen-evidence');
  const evidenceStatus=document.getElementById('screen-evidence-status');

  const AUDITOR_NAME=<?=json_encode($auditorName,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
  const AUDITOR_MATRICULA=<?=json_encode($auditorMatricula,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
  const EVIDENCE_READY_KEY='INTERAFAS_V20_SCREEN_READY';
  const EVIDENCE_CHANNEL='interafas-v20-evidence';

  let evidenceStream=null;
  let evidenceChannel=null;
  let evidenceCaptureBusy=false;

  try{
    if('BroadcastChannel' in window)evidenceChannel=new BroadcastChannel(EVIDENCE_CHANNEL);
  }catch(_){}

  function clearEvidenceReady(){
    try{localStorage.removeItem(EVIDENCE_READY_KEY)}catch(_){}
  }

  function stopEvidenceStream(){
    if(evidenceStream){
      evidenceStream.getTracks().forEach(track=>{try{track.stop()}catch(_){}});
      evidenceStream=null;
    }
    clearEvidenceReady();
  }

  function setEvidenceStatus(kind,text){
    if(!evidenceStatus)return;
    evidenceStatus.className='evidence-status'+(kind?' '+kind:'');
    evidenceStatus.textContent=text;
  }

  async function prepareScreenEvidence(){
    if(!navigator.mediaDevices?.getDisplayMedia){
      setEvidenceStatus('warn','Este navegador no admite captura de pantalla mediante Screen Capture API.');
      return;
    }

    stopEvidenceStream();

    try{
      const stream=await navigator.mediaDevices.getDisplayMedia({
        video:{displaySurface:'monitor'},
        audio:false
      });

      const track=stream.getVideoTracks()[0];
      const settings=track?.getSettings?.()||{};
      const surface=String(settings.displaySurface||'unknown');

      if(surface!=='unknown' && surface!=='monitor'){
        stream.getTracks().forEach(t=>t.stop());
        setEvidenceStatus('warn','Seleccionaste una ventana o pestaña. Vuelve a intentarlo y elige Pantalla completa.');
        return;
      }

      evidenceStream=stream;
      const ready={
        ts:Date.now(),
        surface,
        width:Number(settings.width||0),
        height:Number(settings.height||0)
      };
      try{localStorage.setItem(EVIDENCE_READY_KEY,JSON.stringify(ready))}catch(_){}

      track?.addEventListener('ended',()=>{
        evidenceStream=null;
        clearEvidenceReady();
        setEvidenceStatus('warn','La compartición de pantalla se detuvo. La evidencia final usará el snapshot interno del HMI.');
      },{once:true});

      const size=(settings.width&&settings.height)?' · '+settings.width+'×'+settings.height:'';
      setEvidenceStatus('ready','LISTO · Pantalla completa preparada'+size+'. La captura final se tomará automáticamente al alcanzar CATASTROPHIC STATE.');
    }catch(err){
      clearEvidenceReady();
      setEvidenceStatus('warn','No se autorizó la captura de pantalla. Se utilizará la evidencia interna del HMI.');
    }
  }

  function fitCaptureSize(width,height){
    const maxW=2560;
    const maxH=1440;
    const scale=Math.min(1,maxW/width,maxH/height);
    return {
      width:Math.max(1,Math.round(width*scale)),
      height:Math.max(1,Math.round(height*scale))
    };
  }

  function drawEvidenceStamp(ctx,width,height,capturedAt){
    const pad=Math.max(14,Math.round(width*.012));
    const boxH=Math.max(96,Math.round(height*.105));
    const y=height-boxH-pad;
    const x=pad;
    const boxW=Math.min(width-pad*2,Math.max(620,Math.round(width*.64)));

    ctx.save();
    ctx.fillStyle='rgba(7,12,18,.90)';
    ctx.strokeStyle='rgba(255,77,84,.95)';
    ctx.lineWidth=Math.max(2,Math.round(width/1100));
    ctx.fillRect(x,y,boxW,boxH);
    ctx.strokeRect(x,y,boxW,boxH);

    const titleSize=Math.max(17,Math.round(width/88));
    const textSize=Math.max(13,Math.round(width/118));

    ctx.fillStyle='#ff666d';
    ctx.font='800 '+titleSize+'px Arial, sans-serif';
    ctx.fillText('INTERAFAS · EVIDENCIA OPERACIONAL · VULN-20',x+16,y+28);

    ctx.fillStyle='#ffffff';
    ctx.font='700 '+textSize+'px Arial, sans-serif';
    ctx.fillText('AUDITOR: '+AUDITOR_NAME,x+16,y+54);
    ctx.fillText('MATRÍCULA: '+AUDITOR_MATRICULA,x+16,y+77);

    ctx.fillStyle='#c9d3da';
    ctx.textAlign='right';
    ctx.fillText('CATASTROPHIC STATE',x+boxW-16,y+54);
    ctx.fillText(capturedAt,x+boxW-16,y+77);
    ctx.restore();
  }

  async function capturePreparedScreen(message){
    if(evidenceCaptureBusy||!evidenceStream)return false;

    const track=evidenceStream.getVideoTracks()[0];
    if(!track||track.readyState!=='live'){
      stopEvidenceStream();
      return false;
    }

    evidenceCaptureBusy=true;

    try{
      const video=document.createElement('video');
      video.srcObject=evidenceStream;
      video.muted=true;
      video.playsInline=true;

      await video.play();
      if(!video.videoWidth||!video.videoHeight){
        await new Promise((resolve,reject)=>{
          const timer=setTimeout(()=>reject(new Error('screen-metadata-timeout')),1800);
          video.onloadedmetadata=()=>{clearTimeout(timer);resolve();};
        });
      }

      const sourceW=video.videoWidth||Number(track.getSettings?.().width||1920);
      const sourceH=video.videoHeight||Number(track.getSettings?.().height||1080);
      const size=fitCaptureSize(sourceW,sourceH);

      const canvas=document.createElement('canvas');
      canvas.width=size.width;
      canvas.height=size.height;

      const ctx=canvas.getContext('2d');
      ctx.drawImage(video,0,0,size.width,size.height);

      const capturedAt=new Intl.DateTimeFormat('es-MX',{
        year:'numeric',month:'2-digit',day:'2-digit',
        hour:'2-digit',minute:'2-digit',second:'2-digit',
        hour12:false
      }).format(new Date());

      drawEvidenceStamp(ctx,size.width,size.height,capturedAt);

      const payload={
        image:canvas.toDataURL('image/jpeg',.86),
        width:size.width,
        height:size.height,
        captured_at:new Date().toISOString(),
        stage:Number(message?.stage||5),
        stage_label:String(message?.stage_label||'CATASTROPHIC STATE'),
        source:'full-screen',
        display_surface:String(track.getSettings?.().displaySurface||'monitor'),
        source_width:sourceW,
        source_height:sourceH
      };

      const response=await fetch('/operations/snapshot.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        cache:'no-store',
        body:JSON.stringify(payload)
      });
      const result=await response.json();

      if(!response.ok||!result.ok)throw new Error(result.error||'snapshot-publication-failed');

      evidenceChannel?.postMessage({type:'capture-complete',source:'full-screen'});
      setEvidenceStatus('ready','EVIDENCIA CAPTURADA · '+capturedAt+' · Publicada en Pulso Metropolitano.');

      stopEvidenceStream();
      return true;
    }catch(err){
      evidenceChannel?.postMessage({type:'capture-failed'});
      setEvidenceStatus('warn','No fue posible capturar la pantalla completa. El HMI generará su snapshot interno.');
      stopEvidenceStream();
      return false;
    }finally{
      evidenceCaptureBusy=false;
    }
  }

  prepareEvidenceBtn?.addEventListener('click',prepareScreenEvidence);

  evidenceChannel?.addEventListener('message',event=>{
    const message=event.data||{};
    if(message.type==='capture-request'){
      capturePreparedScreen(message);
    }
  });

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

      let monitorWindow=null;
      let armPromise=null;

      if(localUnsafe){
        // Start/resume audio while the trusted click gesture is still active.
        if(window.INTERAFAS_AUDIO){
          armPromise=window.INTERAFAS_AUDIO.arm();
          window.INTERAFAS_AUDIO.setStage(0,true);
        }

        // Open the operational monitor in the same user gesture. Keeping this
        // firmware tab alive preserves the authorized AudioContext.
        monitorWindow=window.open('/operations/index.php','INTERAFAS_HMI');
      }

      if(armPromise)await armPromise;

      const response=await fetch('firmware.php',{
        method:'POST',
        body,
        headers:{'X-INTERAFAS-AJAX':'1'},
        credentials:'same-origin',
        cache:'no-store'
      });
      const data=await response.json();

      if(!data.ok){
        if(localUnsafe && window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.stop();
        try{ if(monitorWindow && !monitorWindow.closed)monitorWindow.close(); }catch(_){}
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
          status.innerHTML='<strong>ALERTA OPERACIONAL.</strong> Cascada iniciada. El HMI se abrió en la ventana de monitor; esta pestaña permanece como fuente de la alarma audible.';
        }

        if(window.INTERAFAS_AUDIO){
          window.INTERAFAS_AUDIO.setStage(Number(incident.stage||0),true);
          window.INTERAFAS_AUDIO.broadcastStage?.(Number(incident.stage||0),true);
        }

        try{
          if(monitorWindow && !monitorWindow.closed){
            monitorWindow.location.replace('/operations/index.php');
            monitorWindow.focus();
          }else{
            // Fallback only if the browser blocked the monitor popup.
            window.setTimeout(()=>window.location.replace('/operations/index.php'),1200);
          }
        }catch(_){
          window.setTimeout(()=>window.location.replace('/operations/index.php'),1200);
        }
        return;
      }else{
        if(window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.stop();
        try{ if(monitorWindow && !monitorWindow.closed)monitorWindow.close(); }catch(_){}
        if(status){
          status.className='action-status active';
          status.textContent=data.message||'Paquete aplicado correctamente.';
        }
      }
    }catch(err){
      if(window.INTERAFAS_AUDIO)window.INTERAFAS_AUDIO.stop();
      try{ if(typeof monitorWindow!=='undefined' && monitorWindow && !monitorWindow.closed)monitorWindow.close(); }catch(_){}
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
