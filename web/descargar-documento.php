<?php
require __DIR__.'/includes/config.php';
lab_sensitive_page('descargar-documento.php','Descarga de documento','notice');

if (empty($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$file = isset($_GET['file']) ? (string)$_GET['file'] : '';
if ($file === '' || basename($file) !== $file) {
    http_response_code(400);
    exit('Solicitud de documento no valida.');
}

$stmt = db()->prepare('SELECT nombre FROM documentos WHERE usuario_id = ? AND nombre = ? LIMIT 1');
$stmt->execute([$_SESSION['user']['id'], $file]);
$doc = $stmt->fetch();

if (!$doc) {
    http_response_code(404);
    exit('Documento no encontrado.');
}

$baseDir = realpath(__DIR__.'/assets/docs/citizen');
$path = realpath(__DIR__.'/assets/docs/citizen/'.$doc['nombre']);

if ($baseDir === false || $path === false || strpos($path, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
    http_response_code(404);
    exit('Archivo no disponible.');
}

header('Content-Type: application/pdf');
header('Content-Length: '.filesize($path));
header('Content-Disposition: inline; filename="'.basename($path).'"');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
