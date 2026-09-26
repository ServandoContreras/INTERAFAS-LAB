<?php
declare(strict_types=1);

require_once __DIR__.'/includes/config.php';

$key=(string)($_GET['key']??'');
if($key!=='vuln20-final'){
    http_response_code(404);
    exit;
}

$q=db()->prepare("SELECT mime_type,image_blob,captured_at FROM scenario_snapshots WHERE snapshot_key=? LIMIT 1");
$q->execute([$key]);
$row=$q->fetch();

if(!$row || empty($row['image_blob'])){
    http_response_code(404);
    exit;
}

header('Content-Type: '.((string)$row['mime_type']));
header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Pulso-Media: operational-snapshot');
header('X-Pulso-Snapshot-Captured: '.rawurlencode((string)$row['captured_at']));
echo $row['image_blob'];
