<?php
require __DIR__.'/includes/config.php';
if(!empty($_SESSION['user'])){ header('Location:/panel.php'); exit; }
$errors=[]; $success=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $usuario=trim($_POST['usuario']??''); $nombre=trim($_POST['nombre']??''); $correo=trim($_POST['correo']??'');
  $telefono=trim($_POST['telefono']??''); $municipio=trim($_POST['municipio']??''); $cuenta=trim($_POST['cuenta_servicio']??'');
  $password=$_POST['password']??''; $confirm=$_POST['confirm']??'';
  if(!preg_match('/^[A-Za-z0-9._-]{5,30}$/',$usuario)) $errors[]='El usuario debe tener entre 5 y 30 caracteres y usar letras, números, punto, guion o guion bajo.';
  if(mb_strlen($nombre)<5) $errors[]='Escribe tu nombre completo.';
  if(!filter_var($correo,FILTER_VALIDATE_EMAIL)) $errors[]='Escribe un correo válido.';
  if(mb_strlen($telefono)<8) $errors[]='Escribe un teléfono válido.';
  if(!in_array($municipio,['Cerro de San Pablo','Saint Louis','Soledade'],true)) $errors[]='Selecciona un municipio.';
  if(strlen($password)<8) $errors[]='La contraseña debe contener al menos 8 caracteres.';
  if($password!==$confirm) $errors[]='Las contraseñas no coinciden.';
  if(!$errors){
    $pdo=db();
    $q=$pdo->prepare('SELECT id FROM usuarios WHERE usuario=? OR correo=? LIMIT 1'); $q->execute([$usuario,$correo]);
    if($q->fetch()) $errors[]='El usuario o correo ya está registrado.';
    if($cuenta!==''){
      $q=$pdo->prepare('SELECT id,usuario_id FROM cuentas_servicio WHERE cuenta=? LIMIT 1'); $q->execute([$cuenta]); $cs=$q->fetch();
      if(!$cs) $errors[]='El número de cuenta de servicio no existe.';
      elseif(!empty($cs['usuario_id'])) $errors[]='La cuenta de servicio ya está vinculada a otro perfil.';
    }
  }
  if(!$errors){
    try{
      $pdo->beginTransaction();
      $st=$pdo->prepare('INSERT INTO usuarios (usuario,nombre,correo,municipio,telefono,rol,password_hash) VALUES (?,?,?,?,?,\'citizen\',?)');
      $st->execute([$usuario,$nombre,$correo,$municipio,$telefono,password_hash($password,PASSWORD_DEFAULT)]);
      $uid=(int)$pdo->lastInsertId();
      if($cuenta!==''){
        $up=$pdo->prepare('UPDATE cuentas_servicio SET usuario_id=? WHERE cuenta=? AND usuario_id IS NULL'); $up->execute([$uid,$cuenta]);
      }
      $n=$pdo->prepare('INSERT INTO notificaciones (usuario_id,titulo,mensaje,tipo,leida) VALUES (?,?,?,?,0)');
      $n->execute([$uid,'Bienvenido a Mi Portal','Tu cuenta digital quedó creada. Completa tu perfil y revisa tus servicios vinculados.','cuenta']);
      $pdo->commit();
      $_SESSION['user']=['id'=>$uid,'usuario'=>$usuario,'nombre'=>$nombre,'rol'=>'citizen'];
      header('Location:/panel.php?nuevo=1'); exit;
    }catch(Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); $errors[]='No fue posible crear la cuenta. Intenta nuevamente.'; }
  }
}
$pageTitle='Crear cuenta'; include __DIR__.'/includes/header.php';
?>
<section class="login-section register-section"><div class="wrap register-grid">
<div class="login-intro"><div class="eyebrow">Mi Portal</div><h1>Crea tu cuenta digital</h1><p>Administra tus servicios, consulta consumos, facturas, pagos, trámites y documentos desde un solo lugar.</p><div class="login-points"><div><span>01</span><p><b>Cuenta personal</b><small>Tus datos, notificaciones y actividad.</small></p></div><div><span>02</span><p><b>Vincula tu servicio</b><small>Asocia una cuenta ahora o después.</small></p></div><div><span>03</span><p><b>Historial completo</b><small>Consumos, recibos y pagos disponibles.</small></p></div></div></div>
<div class="login-card register-card"><div class="login-card-head"><small>INTERAFAS</small><h2>Registro</h2><p>Completa tus datos para crear acceso a Mi Portal.</p></div>
<?php if($errors): ?><div class="notice compact"><b>Revisa lo siguiente:</b><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post"><div class="form-grid"><div class="field"><label>Nombre completo</label><input name="nombre" required value="<?= htmlspecialchars($_POST['nombre']??'') ?>"></div><div class="field"><label>Usuario</label><input name="usuario" required autocomplete="username" value="<?= htmlspecialchars($_POST['usuario']??'') ?>"></div></div><div class="form-grid"><div class="field"><label>Correo electrónico</label><input type="email" name="correo" required value="<?= htmlspecialchars($_POST['correo']??'') ?>"></div><div class="field"><label>Teléfono</label><input name="telefono" required value="<?= htmlspecialchars($_POST['telefono']??'') ?>"></div></div><div class="field"><label>Municipio</label><select name="municipio" required><option value="">Selecciona</option><?php foreach(['Cerro de San Pablo','Saint Louis','Soledade'] as $m): ?><option <?= (($_POST['municipio']??'')===$m?'selected':'') ?>><?= $m ?></option><?php endforeach; ?></select></div><div class="field"><label>Número de cuenta de servicio <small>(opcional)</small></label><input name="cuenta_servicio" placeholder="Ej. 40010021" value="<?= htmlspecialchars($_POST['cuenta_servicio']??'') ?>"><small>Puedes vincularla más adelante desde Mi Portal.</small></div><div class="form-grid"><div class="field"><label>Contraseña</label><input type="password" name="password" autocomplete="new-password" required></div><div class="field"><label>Confirmar contraseña</label><input type="password" name="confirm" autocomplete="new-password" required></div></div><button class="btn btn-full">Crear mi cuenta</button></form><div class="login-help">¿Ya tienes cuenta? <a href="/login.php"><b>Inicia sesión</b></a></div></div>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
