<?php
require __DIR__.'/includes/config.php';

if(empty($_SESSION['user'])){
    header('Location:/login.php');
    exit;
}

$pdo=db();
$uid=(int)($_SESSION['user']['id']??0);
$clientRole=(string)($_COOKIE['portal_role']??'');

if($clientRole!=='procurement_admin'){
    http_response_code(403);
    $pageTitle='Acceso restringido';
    include __DIR__.'/includes/header.php';
    ?>
    <section class="section"><div class="wrap article">
      <div class="eyebrow">Área restringida</div>
      <h1>Acceso no autorizado</h1>
      <p class="section-intro">La revisión de relaciones contractuales está reservada al personal autorizado de Contrataciones.</p>
      <a class="btn btn-outline" href="/proveedores.php">Volver al padrón</a>
    </div></section>
    <?php
    include __DIR__.'/includes/footer.php';
    exit;
}

if(empty($_SESSION['procurement_relation_csrf'])){
    $_SESSION['procurement_relation_csrf']=bin2hex(random_bytes(24));
}
$csrf=$_SESSION['procurement_relation_csrf'];

$providerId=max(1,(int)($_GET['provider_id']??$_POST['provider_id']??1));
$msg='';
$validation='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $posted=(string)($_POST['csrf']??'');
    if(!hash_equals($csrf,$posted)){
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $providerId=max(1,(int)($_POST['provider_id']??0));
    $contractId=max(1,(int)($_POST['contract_id']??0));

    $p=$pdo->prepare('SELECT id,nombre,servicio,estado FROM proveedores WHERE id=? LIMIT 1');
    $p->execute([$providerId]);
    $targetProvider=$p->fetch();

    $c=$pdo->prepare('SELECT c.id,c.numero,c.proveedor_id,c.objeto,c.estado,p.nombre proveedor_actual FROM contratos c JOIN proveedores p ON p.id=c.proveedor_id WHERE c.id=? LIMIT 1');
    $c->execute([$contractId]);
    $contract=$c->fetch();

    if(!$targetProvider || !$contract){
        $msg='No fue posible localizar la relación solicitada.';
    } else {
        $previousProviderId=(int)$contract['proveedor_id'];

        $up=$pdo->prepare('UPDATE contratos SET proveedor_id=? WHERE id=?');
        $up->execute([$providerId,$contractId]);

        if($previousProviderId!==$providerId){
            header('X-INTERAFAS-Relationship-Control: mismatch-accepted');
            header('X-INTERAFAS-Validation: UPSLP_CNOIV-TRUSTED-SUPPLIER-14');
            $validation='UPSLP_CNOIV-TRUSTED-SUPPLIER-14';

            lab_event(
                'VULN14_RELATIONSHIP_BYPASS',
                'Relación proveedor-contrato modificada sin validación contextual',
                $contract['numero'],
                [
                    'challenge'=>14,
                    'contract_id'=>$contractId,
                    'previous_provider_id'=>$previousProviderId,
                    'submitted_provider_id'=>$providerId,
                    'authenticated_user_id'=>$uid
                ],
                'interafas-web',
                'warning',
                0
            );

            $msg='Relación contractual confirmada y actualizada.';
        } else {
            lab_event(
                'PROCUREMENT_RELATION_CONFIRMED',
                'Relación proveedor-contrato confirmada',
                $contract['numero'],
                [
                    'contract_id'=>$contractId,
                    'provider_id'=>$providerId,
                    'authenticated_user_id'=>$uid
                ],
                'interafas-web',
                'info',
                0
            );

            $msg='Relación contractual confirmada sin cambios.';
        }
    }
}

$p=$pdo->prepare('SELECT id,nombre,servicio,estado FROM proveedores WHERE id=? LIMIT 1');
$p->execute([$providerId]);
$provider=$p->fetch();

if(!$provider){
    http_response_code(404);
    exit('Proveedor no disponible.');
}

$c=$pdo->prepare('SELECT id,numero,objeto,modalidad,monto,fecha_inicio,fecha_fin,estado FROM contratos WHERE proveedor_id=? ORDER BY fecha_inicio DESC,id DESC LIMIT 12');
$c->execute([$providerId]);
$contracts=$c->fetchAll();

$pageTitle='Relaciones contractuales';
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="wrap">
<div class="eyebrow">Módulo interno · Contrataciones</div>
<h1>Revisión de relación proveedor–contrato</h1>
<p class="section-intro">Confirma que los contratos importados al expediente administrativo continúan asociados con el proveedor mostrado. Esta acción no debe crear nuevas asignaciones.</p>

<div class="form-card section-spacer">
  <small>Proveedor en revisión · ID <?= (int)$provider['id'] ?></small>
  <h2><?= htmlspecialchars($provider['nombre']) ?></h2>
  <p><?= htmlspecialchars($provider['servicio']) ?> · <?= htmlspecialchars($provider['estado']) ?></p>
</div>

<?php if($msg): ?><div class="success-banner section-spacer"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<div class="table-wrap section-spacer"><table class="table">
<thead><tr><th>ID contrato</th><th>Contrato / objeto</th><th>Modalidad</th><th>Monto</th><th>Estado</th><th>Revisión</th></tr></thead>
<tbody>
<?php foreach($contracts as $r): ?>
<tr>
<td><?= (int)$r['id'] ?></td>
<td><b><?= htmlspecialchars($r['numero']) ?></b><small><?= htmlspecialchars($r['objeto']) ?></small></td>
<td><?= htmlspecialchars($r['modalidad']) ?></td>
<td>$<?= number_format((float)$r['monto'],2) ?></td>
<td><span class="tag"><?= htmlspecialchars($r['estado']) ?></span></td>
<td>
<form method="post">
<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
<input type="hidden" name="provider_id" value="<?= (int)$provider['id'] ?>">
<input type="hidden" name="contract_id" value="<?= (int)$r['id'] ?>">
<button class="btn btn-light" type="submit">Confirmar relación</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if(!$contracts): ?><tr><td colspan="6">Este proveedor no tiene contratos asociados en el registro actual.</td></tr><?php endif; ?>
</tbody></table></div>

<div class="info section-spacer"><b>Regla administrativa:</b> la confirmación sólo debe validar una relación ya existente entre el proveedor mostrado y cada contrato listado. Cualquier reasignación requiere un procedimiento separado.</div>

<?php if($validation): ?>
<div class="form-card section-spacer">
<div class="eyebrow">Resultado de validación</div>
<h2>Relación inconsistente aceptada</h2>
<p>La solicitud modificó una relación proveedor–contrato que no correspondía con el contexto mostrado por la interfaz.</p>
<p><b>Referencia:</b><br><code><?= htmlspecialchars($validation) ?></code></p>
</div>
<?php endif; ?>

<p class="section-spacer"><a class="btn btn-outline" href="/gestion-proveedores.php">← Volver a gestión de proveedores</a></p>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
