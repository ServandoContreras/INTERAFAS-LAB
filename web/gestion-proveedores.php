<?php
require __DIR__.'/includes/config.php';

if (empty($_SESSION['user'])) {
    header('Location:/login.php');
    exit;
}

$pdo=db();
$uid=(int)($_SESSION['user']['id']??0);
$sessionRole=(string)($_SESSION['user']['rol']??'citizen');
$clientRole=(string)($_COOKIE['portal_role']??'');

// VULN 06: autorización basada en una cookie manipulable del cliente.
// El servidor debería autorizar con el rol almacenado en sesión/base de datos.
if ($clientRole !== 'procurement_admin') {
    lab_event(
        'VULN06_ROLE_DENIED',
        'Acceso rechazado por rol de cliente',
        'Área de gestión de proveedores',
        [
            'challenge'=>6,
            'session_user_id'=>$uid,
            'session_role'=>$sessionRole,
            'client_role'=>$clientRole,
            'authorization_source'=>'client_cookie'
        ],
        'interafas-web',
        'notice',
        0
    );
    http_response_code(403);
    $pageTitle='Acceso restringido';
    include __DIR__.'/includes/header.php';
    ?>
    <section class="section"><div class="wrap article">
      <div class="eyebrow">Área restringida</div>
      <h1>Acceso no autorizado</h1>
      <p class="section-intro">Esta función está reservada para personal de Contrataciones con permisos administrativos.</p>
      <div class="notice section-spacer">Tu sesión está autenticada, pero no cuenta con autorización para utilizar este módulo.</div>
      <a class="btn btn-outline" href="/proveedores.php">Volver al padrón</a>
    </div></section>
    <?php
    include __DIR__.'/includes/footer.php';
    exit;
}

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

        // La autorización ya fue tomada de la cookie portal_role.
        $up=$pdo->prepare('UPDATE proveedores SET estado=? WHERE id=?');
        $up->execute([$newState,$providerId]);

        $escalated=($sessionRole!=='procurement_admin' && $clientRole==='procurement_admin');

        lab_event(
            'VULN06_WRONG_ROLE_ACCESS',
            'Operación privilegiada autorizada por rol controlado por cliente',
            'Proveedor '.$provider['id'].' · '.$provider['nombre'].' → '.$newState,
            [
                'challenge'=>6,
                'session_user_id'=>$uid,
                'session_role'=>$sessionRole,
                'client_role'=>$clientRole,
                'required_role'=>'procurement_admin',
                'authorization_source'=>'client_cookie',
                'privilege_escalation'=>$escalated,
                'provider_id'=>(int)$provider['id'],
                'previous_state'=>$provider['estado'],
                'new_state'=>$newState
            ],
            'interafas-web',
            'warning',
            2
        );

        $msg='Estado actualizado para '.$provider['nombre'].': '.$newState.'.';

        if($escalated){
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

<div class="info section-spacer"><b>Autorización concedida.</b> Perfil administrativo reconocido por el módulo.</div>

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
<h2>Escalamiento vertical confirmado</h2>
<p>Una sesión ciudadana consiguió ejecutar una función administrativa porque el servidor confió en un dato de rol controlado por el cliente.</p>
<p><b>Referencia de validación:</b><br><code><?=htmlspecialchars($flag)?></code></p>
</div>
<?php endif;?>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
