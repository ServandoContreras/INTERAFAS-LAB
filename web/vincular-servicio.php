<?php
require __DIR__.'/includes/config.php'; if(empty($_SESSION['user'])){header('Location:/login.php');exit;}
$msg=''; $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $cuenta=trim($_POST['cuenta']??'');
  $q=db()->prepare('SELECT id,usuario_id,domicilio,municipio FROM cuentas_servicio WHERE cuenta=? LIMIT 1'); $q->execute([$cuenta]); $cs=$q->fetch();
  if(!$cs) $error='No encontramos esa cuenta de servicio.';
  elseif(!empty($cs['usuario_id']) && (int)$cs['usuario_id']!==(int)$_SESSION['user']['id']) $error='La cuenta ya se encuentra vinculada.';
  else { $up=db()->prepare('UPDATE cuentas_servicio SET usuario_id=? WHERE id=?');$up->execute([$_SESSION['user']['id'],$cs['id']]);$msg='Servicio vinculado correctamente.'; }
}
$st=db()->prepare('SELECT cuenta,contrato,domicilio,municipio,estatus FROM cuentas_servicio WHERE usuario_id=? ORDER BY id');$st->execute([$_SESSION['user']['id']]);$accounts=$st->fetchAll();
$pageTitle='Vincular servicio';include __DIR__.'/includes/header.php'; ?>
<section class="section"><div class="wrap narrow"><div class="eyebrow">Mi Portal</div><h1>Vincular cuenta de servicio</h1><p class="section-intro">Asocia una cuenta de agua a tu perfil digital para consultar consumos, facturas y pagos.</p><?php if($msg): ?><div class="success-banner"><?= htmlspecialchars($msg) ?></div><?php endif; ?><?php if($error): ?><div class="notice"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post" class="form-card section-spacer"><div class="field"><label>Número de cuenta</label><input name="cuenta" required placeholder="Ej. 40010021"></div><button class="btn">Vincular servicio</button></form><?php if($accounts): ?><div class="section-spacer"><h2>Servicios vinculados</h2><div class="linked-services"><?php foreach($accounts as $a): ?><div><span>Cuenta <?= htmlspecialchars($a['cuenta']) ?></span><h3><?= htmlspecialchars($a['domicilio']) ?></h3><p><?= htmlspecialchars($a['municipio']) ?> · <?= htmlspecialchars($a['contrato']) ?></p><b><?= htmlspecialchars($a['estatus']) ?></b></div><?php endforeach; ?></div></div><?php endif; ?></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
