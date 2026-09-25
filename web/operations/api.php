<?php
require __DIR__.'/common.php'; require_ops_bridge_context(); $ctx=require_lab_attempt(); header('Content-Type: application/json; charset=utf-8');
$payload=json_decode(file_get_contents('php://input'),true) ?: [];
if(($payload['action']??'')==='p101'){
    $state=strtoupper((string)($payload['state']??''));
    lab_event('OT_CONTROL_ATTEMPT','Intento de control operacional','P-101 → '.$state,['asset'=>'P-101','requested_state'=>$state],'ot-hmi','warning',0);
    $r=ot_call('/control','POST',['asset'=>'P-101','state'=>$state]);
    if(!empty($r['ok'])){
        lab_event('OT_STATE_CHANGE','Cambio operacional simulado','P-101 '.$r['previous'].' → '.$r['state'],['asset'=>'P-101','previous'=>$r['previous'],'new'=>$r['state'],'flow'=>$r['process']['flow']??null,'pressure'=>$r['process']['pressure']??null,'alarms'=>$r['process']['alarms']??[]],'ot-sim',$state==='OFF'?'critical':'notice',0);
        echo json_encode(['ok'=>true,'message'=>'P-101 actualizado','process'=>$r['process']],JSON_UNESCAPED_UNICODE); exit;
    }
    lab_event('OT_CONTROL_FAILED','Fallo de control operacional','P-101 → '.$state,['response'=>$r],'ot-sim','warning',0);
    http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No fue posible aplicar el cambio']); exit;
}
http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Acción no reconocida']);
