<?php
require __DIR__.'/includes/config.php';

$rawTipo = $_GET['tipo'] ?? 'proveedores';
$tipo = is_string($rawTipo) ? trim($rawTipo) : '';

lab_event(
    'DATA_EXPORT',
    'Exportación de datos públicos',
    $tipo !== '' ? $tipo : '[entrada no válida]',
    ['query'=>$_SERVER['QUERY_STRING']??''],
    'interafas-web',
    'notice',
    0
);

try {
    /*
     * El exportador usa una selección exhaustiva de los conjuntos soportados.
     * No existe caso "default": una entrada distinta provoca un
     * UnhandledMatchError real de PHP.
     */
    $exportKind = match ($tipo) {
        'proveedores' => 'proveedores',
        'contratos'   => 'contratos',
    };

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="interafas_'.$exportKind.'.csv"');

    $o=fopen('php://output','w');
    fwrite($o,"\xEF\xBB\xBF");

    if($exportKind==='contratos'){
        fputcsv($o,['Contrato','Proveedor','Objeto','Modalidad','Monto','Inicio','Fin','Origen del recurso','Estado']);
        $rows=db()->query('SELECT c.*,p.nombre proveedor FROM contratos c JOIN proveedores p ON p.id=c.proveedor_id ORDER BY c.id')->fetchAll();
        foreach($rows as $r){
            fputcsv($o,[$r['numero'],$r['proveedor'],$r['objeto'],$r['modalidad'],$r['monto'],$r['fecha_inicio'],$r['fecha_fin'],$r['origen_recurso'],$r['estado']]);
        }
    } else {
        fputcsv($o,['ID','Proveedor','Servicio','Estado']);
        foreach(db()->query('SELECT * FROM proveedores ORDER BY nombre') as $r){
            fputcsv($o,[$r['id'],$r['nombre'],$r['servicio'],$r['estado']]);
        }
    }

    fclose($o);
    exit;

} catch (Throwable $e) {
    lab_event(
        'VULN05_VERBOSE_ERROR',
        'Respuesta de error con información técnica expuesta',
        '/exportar.php?'.($_SERVER['QUERY_STRING']??''),
        [
            'challenge'=>5,
            'exception_class'=>get_class($e),
            'module'=>'public-export',
            'debug_response'=>true
        ],
        'interafas-web',
        'warning',
        2
    );

    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');

    $safeMessage = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    $safeFile = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
    $safeClass = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
    $safeTrace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Export service error | INTERAFAS</title>';
    echo '<style>body{margin:0;background:#0b1420;color:#dce7f3;font:14px/1.55 ui-monospace,SFMono-Regular,Consolas,monospace}.box{max-width:980px;margin:46px auto;padding:0 22px}.head{border-left:4px solid #d75c5c;padding:4px 16px;margin-bottom:24px}.head h1{margin:0 0 6px;font-size:24px}.muted{color:#8194aa}.panel{background:#101e2e;border:1px solid #263a51;border-radius:12px;padding:18px;margin:13px 0}.row{display:grid;grid-template-columns:190px 1fr;gap:14px;padding:8px 0;border-bottom:1px solid #1e3044}.row:last-child{border-bottom:0}.row b{color:#8dbadd}.danger{color:#ffb2b2}.trace{white-space:pre-wrap;word-break:break-word;color:#aebed0}</style></head><body><main class="box">';
    echo '<div class="head"><div class="muted">INTERAFAS Public Data Exporter</div><h1>Unhandled export exception</h1><div class="danger">DEBUG RESPONSE ENABLED</div></div>';
    echo '<section class="panel">';
    echo '<div class="row"><b>Exception</b><span>'.$safeClass.'</span></div>';
    echo '<div class="row"><b>Message</b><span>'.$safeMessage.'</span></div>';
    echo '<div class="row"><b>Source file</b><span>'.$safeFile.'</span></div>';
    echo '<div class="row"><b>Source line</b><span>'.(int)$e->getLine().'</span></div>';
    echo '<div class="row"><b>Module</b><span>public-export / 2026.09-r5</span></div>';
    echo '<div class="row"><b>Environment</b><span>production</span></div>';
    echo '<div class="row"><b>Database service</b><span>db:3306 / schema interafas</span></div>';
    echo '<div class="row"><b>Build validation</b><span>UPSLP_CNOIV-TOO-MUCH-INFO-05</span></div>';
    echo '</section>';
    echo '<section class="panel"><b>Stack trace</b><pre class="trace">'.$safeTrace.'</pre></section>';
    echo '<p class="muted">This diagnostic page is intended for internal troubleshooting.</p>';
    echo '</main></body></html>';
    exit;
}
