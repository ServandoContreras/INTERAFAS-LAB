<?php
require __DIR__.'/includes/config.php';
lab_sensitive_page('expediente.php','Expediente ciudadano','notice');

if (empty($_SESSION['user'])) {
    header('Location:/login.php');
    exit;
}

$sessionUid = (int)$_SESSION['user']['id'];
$requestedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/*
 * El portal trabaja con un identificador de expediente para permitir
 * enlaces directos desde notificaciones y trámites.
 */
if ($requestedId <= 0) {
    $own = db()->prepare('SELECT id FROM expedientes WHERE usuario_id=? ORDER BY fecha DESC,id DESC LIMIT 1');
    $own->execute([$sessionUid]);
    $ownId = (int)($own->fetchColumn() ?: 0);
    if ($ownId <= 0) {
        http_response_code(404);
        exit('No hay expediente disponible.');
    }
    header('Location:/expediente.php?id='.$ownId);
    exit;
}

/*
 * VULN 03 · NOT-YOURS
 * Se valida que el expediente exista, pero no que pertenezca
 * al usuario autenticado.
 */
$q = db()->prepare('SELECT * FROM expedientes WHERE id=? LIMIT 1');
$q->execute([$requestedId]);
$e = $q->fetch();

if (!$e) {
    http_response_code(404);
    exit('Expediente no encontrado.');
}

$q = db()->prepare('SELECT usuario,nombre,correo,municipio,telefono,fecha_nacimiento,curp,domicilio_notificacion,codigo_postal FROM usuarios WHERE id=? LIMIT 1');
$q->execute([(int)$e['usuario_id']]);
$u = $q->fetch();

$q = db()->prepare('SELECT * FROM cuentas_servicio WHERE id=? LIMIT 1');
$q->execute([(int)$e['cuenta_id']]);
$c = $q->fetch();

$isForeign = (int)$e['usuario_id'] !== $sessionUid;

lab_event(
    $isForeign ? 'VULN03_IDOR_CONFIRMED' : 'VULN03_OBJECT_VIEW',
    $isForeign ? 'Acceso a expediente de otro usuario' : 'Consulta de expediente propio',
    '/expediente.php?id='.$requestedId,
    [
        'challenge'=>3,
        'requested_object_id'=>$requestedId,
        'owner_user_id'=>(int)$e['usuario_id'],
        'session_user_id'=>$sessionUid,
        'ownership_check_applied'=>false
    ],
    'interafas-web',
    $isForeign ? 'warning' : 'info',
    3
);

$pageTitle='Mi expediente';
include __DIR__.'/includes/header.php';
?>
<section class="section account-section"><div class="wrap"><div class="eyebrow">Portal ciudadano</div><h1>Mi expediente</h1><p class="section-intro">Información de identificación, servicio y seguimiento asociada a tu perfil.</p><div class="profile-sections section-spacer"><section><div class="profile-section-head"><span>01</span><div><h2>Datos del titular</h2><p>Información registrada en Mi Portal.</p></div></div><dl class="detail-grid"><div><dt>Nombre</dt><dd><?= htmlspecialchars($u['nombre']) ?></dd></div><div><dt>Usuario</dt><dd><?= htmlspecialchars($u['usuario']) ?></dd></div><div><dt>Correo</dt><dd><?= htmlspecialchars($u['correo']) ?></dd></div><div><dt>Teléfono</dt><dd><?= htmlspecialchars($u['telefono']) ?></dd></div><div><dt>Fecha de nacimiento</dt><dd><?= htmlspecialchars($u['fecha_nacimiento']?:'No registrada') ?></dd></div><div><dt>CURP</dt><dd><?= htmlspecialchars($u['curp']?:'No registrada') ?></dd></div><div class="wide"><dt>Domicilio de notificación</dt><dd><?= htmlspecialchars($u['domicilio_notificacion']?:'No registrado') ?></dd></div></dl></section>
<section><div class="profile-section-head"><span>02</span><div><h2>Datos del servicio</h2><p>Cuenta, contrato, medidor y domicilio abastecido.</p></div></div><?php if($c): ?><dl class="detail-grid"><div><dt>Número de cuenta</dt><dd><?= htmlspecialchars($c['cuenta']) ?></dd></div><div><dt>Contrato</dt><dd><?= htmlspecialchars($c['contrato']) ?></dd></div><div><dt>Medidor</dt><dd><?= htmlspecialchars($c['medidor']) ?></dd></div><div><dt>Tipo de servicio</dt><dd><?= htmlspecialchars($c['tipo_servicio']) ?></dd></div><div><dt>Tarifa</dt><dd><?= htmlspecialchars($c['tarifa']) ?></dd></div><div><dt>Fecha de alta</dt><dd><?= htmlspecialchars($c['fecha_alta']) ?></dd></div><div><dt>Estatus</dt><dd><?= htmlspecialchars($c['estatus']) ?></dd></div><div><dt>Última lectura</dt><dd><?= number_format((float)$c['lectura_actual'],1) ?> · <?= htmlspecialchars($c['fecha_lectura']) ?></dd></div><div class="wide"><dt>Domicilio del servicio</dt><dd><?= htmlspecialchars($c['domicilio'].', '.$c['colonia'].', '.$c['municipio'].' C.P. '.$c['codigo_postal']) ?></dd></div></dl><?php else: ?><div class="empty-state compact"><p>No hay una cuenta de servicio vinculada.</p></div><?php endif; ?></section>
<section><div class="profile-section-head"><span>03</span><div><h2>Expediente administrativo</h2><p>Último trámite integrado.</p></div></div><dl class="detail-grid"><div><dt>Folio</dt><dd><?= htmlspecialchars($e['folio']) ?></dd></div><div><dt>Tipo</dt><dd><?= htmlspecialchars($e['tipo']) ?></dd></div><div><dt>Fecha</dt><dd><?= htmlspecialchars($e['fecha']) ?></dd></div><div><dt>Estatus</dt><dd><?= htmlspecialchars($e['estatus']) ?></dd></div><div><dt>Canal</dt><dd><?= htmlspecialchars($e['canal']) ?></dd></div><div><dt>Última actualización</dt><dd><?= htmlspecialchars($e['ultima_actualizacion']?:'—') ?></dd></div><div><dt>Referencia de validación</dt><dd class="mono"><?= htmlspecialchars($e['referencia_validacion']?:'—') ?></dd></div><div class="wide"><dt>Observaciones</dt><dd><?= htmlspecialchars($e['observaciones']?:'Sin observaciones') ?></dd></div></dl></section></div></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
