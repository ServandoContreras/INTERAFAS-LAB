<?php
require __DIR__.'/common.php'; $ctx=require_lab_attempt(); $result=null; $error=null;
lab_event('OT_FIRMWARE_PANEL','Acceso al administrador de firmware','RTU-GW-07',[],'ot-hmi','notice',8);
$current=ot_call('/firmware');
if($_SERVER['REQUEST_METHOD']==='POST'){
    $raw=trim((string)($_POST['package']??''));
    lab_event('FIRMWARE_UPLOAD_ATTEMPT','Intento de carga de firmware ficticio','RTU-GW-07',['bytes'=>strlen($raw)],'ot-hmi','warning',0);
    $pkg=json_decode($raw,true);
    if(!is_array($pkg)){$error='El paquete debe ser JSON válido.'; lab_event('FIRMWARE_REJECTED','Firmware rechazado','JSON inválido',[],'ot-sim','warning',0);}
    else{
        $r=ot_call('/firmware','POST',['package'=>$pkg]);
        if(!empty($r['ok'])){$result=$r; $current=$r['firmware']; lab_event('FIRMWARE_APPLIED','Firmware ficticio aplicado','RTU-GW-07 → '.$current['version'],['previous'=>$r['previous']??null,'firmware'=>$current],'ot-sim','critical',0);}
        else{$error='El simulador rechazó el paquete.'; lab_event('FIRMWARE_REJECTED','Firmware rechazado',(string)($r['error']??'error'),['response'=>$r],'ot-sim','warning',0);}
    }
}
$sample=json_encode(['device'=>'RTU-GW-07','version'=>'3.4.3','mode'=>'NORMAL','diagnostic'=>'LOCKED'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Firmware RTU-GW-07</title><style>body{font-family:system-ui;background:#07111f;color:#dce8f5;margin:0}.wrap{max-width:1050px;margin:auto;padding:34px}.card{background:#0d1b2d;border:1px solid #183754;border-radius:18px;padding:24px;margin:18px 0}textarea{width:100%;min-height:250px;background:#06101d;color:#d9eefc;border:1px solid #27506f;border-radius:12px;padding:15px;font:14px ui-monospace,monospace;box-sizing:border-box}.btn{display:inline-block;background:#175985;color:white;border:0;border-radius:10px;padding:11px 16px;text-decoration:none;cursor:pointer}.ok{background:#103829;padding:12px;border-radius:10px}.err{background:#4b2228;padding:12px;border-radius:10px}.muted{color:#8ea7bf}code{color:#a6dcff}</style></head><body><div class="wrap"><a class="btn" href="index.php">← Volver al HMI</a><div class="card"><small class="muted">RTU-GW-07 · mantenimiento</small><h1>Actualización de firmware</h1><p>Firmware actual: <strong><?=htmlspecialchars((string)($current['version']??'—'))?></strong> · modo <?=htmlspecialchars((string)($current['mode']??'—'))?> · diagnóstico <?=htmlspecialchars((string)($current['diagnostic']??'—'))?></p></div><?php if($result):?><div class="ok">Paquete aplicado. El RTU simulado completó su reinicio.</div><?php endif;?><?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?><div class="card"><h2>Paquete de actualización</h2><p class="muted">Formato ficticio del laboratorio. En esta versión se utiliza para probar telemetría; posteriormente incorporaremos la validación vulnerable correspondiente a la bandera 18.</p><form method="post"><textarea name="package"><?=htmlspecialchars($_POST['package']??$sample)?></textarea><p><button class="btn" type="submit">Validar e instalar</button></p></form></div></div></body></html>
