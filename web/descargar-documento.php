<?php
require __DIR__.'/includes/config.php';
lab_sensitive_page('descargar-documento.php','Descarga de documento','notice');

if (empty($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$file = isset($_GET['file']) ? (string)$_GET['file'] : '';
if ($file === '') {
    http_response_code(400);
    exit('Solicitud de documento no valida.');
}

/*
 * VULN 09 · TOO-DEEP
 * El nombre recibido se concatena directamente al directorio esperado.
 * No se normaliza la ruta ni se comprueba que el archivo final permanezca
 * dentro de assets/docs/citizen.
 */
$baseDir = __DIR__.'/assets/docs/citizen';
$path = $baseDir.'/'.$file;

if (!is_file($path) || !is_readable($path)) {
    http_response_code(404);
    exit('Archivo no disponible.');
}

$normalized = str_replace('\\','/',$file);
$isTraversal = str_contains($normalized,'../');

lab_event(
    $isTraversal ? 'VULN09_PATH_TRAVERSAL' : 'DOCUMENT_DOWNLOAD',
    $isTraversal ? 'Lectura fuera del directorio previsto' : 'Documento ciudadano consultado',
    $file,
    [
        'challenge'=>9,
        'requested_file'=>$file,
        'base_directory'=>'assets/docs/citizen',
        'traversal_detected'=>$isTraversal
    ],
    'interafas-web',
    $isTraversal ? 'warning' : 'info',
    0
);

$mime = mime_content_type($path) ?: 'application/octet-stream';
header('Content-Type: '.$mime);
header('Content-Length: '.filesize($path));
header('Content-Disposition: inline; filename="'.basename($path).'"');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
