<?php
require __DIR__.'/includes/config.php';

// VULN 06: la aplicación exige autenticación, pero omite autorización por rol.
if (empty($_SESSION['user'])) {
    header('Location:/login.php');
    exit;
}

$pdo=db();
$uid=(int)($_SESSION['user']['id']??0);
$currentRole=(string)($_SESSION['user']['rol']??'citizen');

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $providerId=max(1,(int)($_POST['provider_id']??0));
    $action=(string)($_POST['action']??'');

    if($action==='validate-access'){
        $st=$pdo->prepare('SELECT id,nombre,servicio,estado FROM proveedores WHERE id=? LIMIT 1');
        $st->execute([$providerId]);
        $provider=$st->fetch();

        if($provider){
            lab_event(
                'VULN06_WRONG_ROLE_ACCESS',
                'Acceso de gestión autorizado sin validación de rol',
                'Proveedor '.$provider['id'].' · '.$provider['nombre'],
                [
                    'challenge'=>6,
                    'session_user_id'=>$uid,
                    'session_role'=>$currentRole,
                    'required_role'=>'procurement_admin',
                    'role_check_applied'=>false,
                    'provider_id'=>(int)$provider['id']
                ],
                'interafas-web',
                'warning',
                2
            );
            $msg='Validación operativa completada para '.$provider['nombre'].'.';
            $flag='UPSLP_CNOIV-WRONG-ROLE-06';
        }
    }
}

$rows=$pdo->query("SELECT id,nombre,servicio,estado FROM proveedores ORDER BY id LIMIT 12")->fetchAll();
$pageTitle='Gestión de proveedores';
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="wrap">
<div class="eyebrow">Módulo interno · Contrataciones</div>
<h1>Gestión del padrón de proveedores</h1>
<p class="section-intro">Herramienta operativa destinada a personal autorizado de Contrataciones para validar registros del padrón.</p>

<div class="info section-spacer"><b>Sesión actual:</b> <?=htmlspecialchars($_SESSION['user']['nombre']??'')?> · rol reportado: <code><?=htmlspecialchars($currentRole)?></code></div>

<?php if($msg):?><div class="success-banner section-spacer"><?=htmlspecialchars($msg)?></div><?php endif;?>

<div class="table-wrap section-spacer"><table class="table"><thead><tr><th>ID</th><th>Proveedor</th><th>Servicio</th><th>Estado</th><th>Gestión</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr>
<td><?= (int)$r['id']?></td>
<td><b><?=htmlspecialchars($r['nombre'])?></b></td>
<td><?=htmlspecialchars($r['servicio'])?></td>
<td><span class="tag<?= $r['estado']==='Vigente'?' tag-green':''?>"><?=htmlspecialchars($r['estado'])?></span></td>
<td><form method="post"><input type="hidden" name="provider_id" value="<?= (int)$r['id']?>"><input type="hidden" name="action" value="validate-access"><button class="btn btn-light" type="submit">Validar registro</button></form></td>
</tr><?php endforeach;?>
</tbody></table></div>

<?php if(!empty($flag)):?>
<div class="form-card section-spacer">
<div class="eyebrow">Resultado de validación</div>
<h2>Control de acceso inconsistente</h2>
<p>La operación fue aceptada aunque el rol de la sesión no corresponde al perfil administrativo esperado.</p>
<p><b>Referencia de validación:</b><br><code><?=htmlspecialchars($flag)?></code></p>
</div>
<?php endif;?>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
