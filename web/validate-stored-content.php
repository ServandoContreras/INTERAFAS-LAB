<?php
require __DIR__.'/includes/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    echo json_encode(['ok'=>false]);
    exit;
}

$body=json_decode(file_get_contents('php://input'),true);
$id=(int)($body['report_id']??0);
$proof=(string)($body['proof']??'');

$st=db()->prepare('SELECT id,folio,proof_token FROM reportes_ciudadanos WHERE id=? LIMIT 1');
$st->execute([$id]);
$r=$st->fetch();

if(!$r || $proof==='' || !hash_equals((string)$r['proof_token'],$proof)){
    http_response_code(403);
    echo json_encode(['ok'=>false]);
    exit;
}

lab_event('VULN08_STORED_XSS_CONFIRMED','Ejecución de contenido persistente confirmada',(string)$r['folio'],[
    'challenge'=>8,
    'report_id'=>(int)$r['id']
],'interafas-web','warning',0);

echo json_encode([
  'ok'=>true,
  'flag'=>'UPSLP_CNOIV-STORED-WORDS-08'
],JSON_UNESCAPED_SLASHES);
