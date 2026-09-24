<?php
require __DIR__.'/includes/config.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

if($_SERVER['REQUEST_METHOD']!=='GET'){
    http_response_code(405);
    echo 'method-not-allowed';
    exit;
}

$id=(int)($_GET['report_id']??0);
$proof=(string)($_GET['proof']??'');
$referer=(string)($_SERVER['HTTP_REFERER']??'');
$fetchDest=strtolower((string)($_SERVER['HTTP_SEC_FETCH_DEST']??''));

$refererOk=false;
if($referer!==''){
    $parts=parse_url($referer);
    $refPath=(string)($parts['path']??'');
    $refQuery=[];
    parse_str((string)($parts['query']??''),$refQuery);
    $refererOk=$refPath==='/seguimiento-reporte.php' && (int)($refQuery['id']??0)===$id;
}

$fetchOk=$fetchDest==='empty';

if(!$refererOk || !$fetchOk){
    http_response_code(403);
    echo 'execution-context-required';
    exit;
}

$st=db()->prepare('SELECT id,folio,proof_token FROM reportes_ciudadanos WHERE id=? LIMIT 1');
$st->execute([$id]);
$r=$st->fetch();

if(!$r || $proof==='' || !hash_equals((string)$r['proof_token'],$proof)){
    http_response_code(403);
    echo 'invalid';
    exit;
}

lab_event(
    'VULN08_STORED_XSS_CONFIRMED',
    'Ejecución de contenido persistente confirmada',
    (string)$r['folio'],
    [
        'challenge'=>8,
        'report_id'=>(int)$r['id'],
        'validation'=>'same-origin-fetch'
    ],
    'interafas-web',
    'warning',
    0
);

echo 'UPSLP_CNOIV-STORED-WORDS-08';
