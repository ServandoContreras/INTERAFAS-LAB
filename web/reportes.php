<?php
require __DIR__.'/includes/config.php';

$ok=null;
$error=null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $categoria=trim((string)($_POST['categoria']??''));
    $municipio=trim((string)($_POST['municipio']??''));
    $descripcion=trim((string)($_POST['descripcion']??''));
    $correo=trim((string)($_POST['correo']??''));

    if($descripcion==='' || ($correo!=='' && !filter_var($correo,FILTER_VALIDATE_EMAIL))){
        $error='Completa la descripción y verifica el correo de contacto.';
    } else {
        $folio='REP-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6));
        $proof=bin2hex(random_bytes(24));
        $st=db()->prepare('INSERT INTO reportes_ciudadanos (folio,categoria,municipio,descripcion,correo,proof_token) VALUES (?,?,?,?,?,?)');
        $st->execute([$folio,$categoria,$municipio,$descripcion,$correo,$proof]);
        $ok=['id'=>(int)db()->lastInsertId(),'folio'=>$folio];

        lab_event('VULN08_REPORT_STORED','Reporte ciudadano almacenado',$folio,[
            'challenge'=>8,
            'report_id'=>$ok['id'],
            'description_length'=>strlen($descripcion)
        ],'interafas-web','notice',0);
    }
}

$pageTitle='Reportes';
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="wrap">
<div class="eyebrow">Atención ciudadana</div>
<h1>Reportes e incidencias</h1>
<p class="section-intro">Registra fugas, baja presión, falta de agua o incidencias de alcantarillado.</p>

<?php if($ok):?>
<div class="success-banner section-spacer">
  Reporte recibido. Folio <b><?=htmlspecialchars($ok['folio'])?></b>.
  <a href="/seguimiento-reporte.php?id=<?=$ok['id']?>">Consultar seguimiento →</a>
</div>
<?php endif;?>
<?php if($error):?><div class="notice section-spacer"><?=htmlspecialchars($error)?></div><?php endif;?>

<div class="stat-grid"><div class="stat-card"><b>1,428</b><span>Reportes del mes</span></div><div class="stat-card"><b>1,311</b><span>Atendidos</span></div><div class="stat-card"><b>97</b><span>En proceso</span></div><div class="stat-card"><b>20</b><span>Fuera de objetivo</span></div><div class="stat-card"><b>2.8 h</b><span>Primera respuesta</span></div><div class="stat-card"><b>9.1 h</b><span>Solución promedio</span></div></div>

<div class="two-col section-spacer"><div class="chart-card"><div class="chart-title"><b>Distribución de reportes</b><span>Mes actual</span></div><div class="bar-list"><div><span>Fuga</span><i><em style="width:41%"></em></i><b>41 %</b></div><div><span>Baja presión</span><i><em style="width:23%"></em></i><b>23 %</b></div><div><span>Falta de agua</span><i><em style="width:17%"></em></i><b>17 %</b></div><div><span>Alcantarillado</span><i><em style="width:14%"></em></i><b>14 %</b></div><div><span>Otros</span><i><em style="width:5%"></em></i><b>5 %</b></div></div></div>
<form method="post" class="form-card"><h2>Nuevo reporte</h2><div class="field"><label>Categoría</label><select name="categoria"><option>Fuga</option><option>Falta de agua</option><option>Baja presión</option><option>Alcantarillado</option><option>Otro</option></select></div><div class="field"><label>Municipio</label><select name="municipio"><option>Cerro de San Pablo</option><option>Saint Louis</option><option>Soledade</option></select></div><div class="field"><label>Descripción</label><textarea name="descripcion" rows="5" required></textarea></div><div class="field"><label>Correo de contacto</label><input type="email" name="correo"></div><button class="btn" type="submit">Enviar reporte</button></form></div>
</div></section>
<section class="promo-band"><div class="wrap support-feature"><img src="/assets/img/support-team.svg" alt="Equipo de atención"><div><span class="promo-label">Atención 24/7</span><h2>También puedes hablar con una persona.</h2><p>Línea metropolitana 800 123 9000 · WhatsApp informativo 444 000 9000.</p><a class="btn btn-white" href="/contacto.php">Ver canales</a></div></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
