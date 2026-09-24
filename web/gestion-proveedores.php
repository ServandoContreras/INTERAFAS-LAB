<?php
require __DIR__.'/includes/config.php';

// VULN 06: existe autenticación, pero falta la autorización por rol.
if (empty($_SESSION['user'])) {
    header('Location:/login.php');
    exit;
}

$pdo=db();
$uid=(int)($_SESSION['user']['id']??0);
$currentRole=(string)($_SESSION['user']['rol']??'citizen');

if(empty($_SESSION['vuln06_csrf'])){
    $_SESSION['vuln06_csrf']=bin2hex(random_bytes(24));
}
$csrf=$_SESSION['vuln06_csrf'];

$msg='';
$flag='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $posted=(string)($_POST['csrf']??'');
    if(!hash_equals($csrf,$posted)){
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $providerId=max(1,(int)($_POST['provider_id']??0));
    $action=(string)($_POST['action']??'');

    $st=$pdo->prepare('SELECT id,nombre,servicio,estado FROM proveedores WHERE id=? LIMIT 1');
    $st->execute([$providerId]);
    $provider=$st->fetch();

    if($provider && in_array($action,['set-review','restore-active'],true)){
        $newState=$action==='set-review' ? 'En revisión' : 'Vigente';

        // El defecto está aquí: no se comprueba que $currentRole sea procurement_admin.
        $up=$pdo->prepare('UPDATE proveedores SET estado=? WHERE id=?');
        $up->execute([$newState,$providerId]);

        lab_event(
            'VULN06_WRONG_ROLE_ACCESS',
            'Operación privilegiada ejecutada sin validación de rol',
            'Proveedor '.$provider['id'].' · '.$provider['nombre'].' → '.$newState,
            [
                'challenge'=>6,
                'session_user_id'=>$uid,
                'session_role'=>$currentRole,
                'required_role'=>'procurement_admin',
                'role_check_applied'=>false,
                'provider_id'=>(int)$provider['id'],
                'previous_state'=>$provider['estado'],
                'new_state'=>$newState
            ],
            'interafas-web',
            'warning',
            2
        );

        $msg='Estado actualizado para '.$provider['nombre'].': '.$newState.'.';

        if($currentRole!=='procurement_admin'){
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
<p class="section-intro">Herramienta operativa destinada a personal autorizado de Contrataciones para revisar el estado de registros del padrón.</p>

<div class="info section-spacer"><b>Sesión actual:</b> <?=htmlspecialchars($_SESSION['user']['nombre']??'')?> · rol reportado: <code><?=htmlspecialchars($currentRole)?></code> · rol esperado: <code>procurement_admin</code></div>

<?php if($msg):?><div class="success-banner section-spacer"><?=htmlspecialchars($msg)?></div><?php endif;?>

<div class="table-wrap section-spacer"><table class="table"><thead><tr><th>ID</th><th>Proveedor</th><th>Servicio</th><th>Estado</th><th>Gestión</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr>
<td><?= (int)$r['id']?></td>
<td><b><?=htmlspecialchars($r['nombre'])?></b></td>
<td><?=htmlspecialchars($r['servicio'])?></td>
<td><span class="tag<?= $r['estado']==='Vigente'?' tag-green':''?>"><?=htmlspecialchars($r['estado'])?></span></td>
<td>
<form method="post">
<input type="hidden" name="csrf" value="<?=htmlspecialchars($csrf)?>">
<input type="hidden" name="provider_id" value="<?= (int)$r['id']?>">
<?php if($r['estado']==='Vigente'):?>
<input type="hidden" name="action" value="set-review">
<button class="btn btn-light" type="submit">Marcar en revisión</button>
<?php else:?>
<input type="hidden" name="action" value="restore-active">
<button class="btn btn-light" type="submit">Restaurar vigente</button>
<?php endif;?>
</form>
</td>
</tr><?php endforeach;?>
</tbody></table></div>

<?php if($flag):?>
<div class="form-card section-spacer">
<div class="eyebrow">Resultado de autorización</div>
<h2>La operación fue aceptada con un rol incorrecto</h2>
<p>El servidor modificó un registro de gestión aunque la sesión actual no posee el rol administrativo esperado. Puedes restaurar el proveedor a <b>Vigente</b> desde la misma tabla.</p>
<p><b>Referencia de validación:</b><br><code><?=htmlspecialchars($flag)?></code></p>
</div>
<?php endif;?>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
