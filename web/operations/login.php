<?php
require __DIR__.'/common.php';
require_operational_network(true);

if(operational_is_auth()){
    header('Location: /operations/');
    exit;
}

$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username=trim((string)($_POST['username']??''));
    $password=(string)($_POST['password']??'');

    $q=db()->prepare("SELECT id,username,display_name,role,password_hash,active FROM operational_users WHERE username=? LIMIT 1");
    $q->execute([$username]);
    $user=$q->fetch();

    if($user && (int)$user['active']===1 && password_verify($password,(string)$user['password_hash'])){
        session_regenerate_id(true);
        $_SESSION['ops_user']=[
            'id'=>(int)$user['id'],
            'username'=>(string)$user['username'],
            'display_name'=>(string)$user['display_name'],
            'role'=>(string)$user['role']
        ];
        lab_event(
            'OT_OPERATOR_LOGIN',
            'Inicio de sesión operacional',
            (string)$user['username'],
            ['role'=>$user['role']],
            'ot-hmi',
            'notice',
            0
        );
        header('Location: /operations/');
        exit;
    }
    $error='Credenciales operacionales incorrectas.';
}
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Acceso operacional · INTERAFAS</title>
<style>
body{font-family:system-ui;background:#07111f;color:#dce8f5;margin:0;min-height:100vh;display:grid;place-items:center}
.login{width:min(440px,calc(100% - 36px));background:#0d1b2d;border:1px solid #183754;border-radius:18px;padding:28px;box-sizing:border-box}
small,.muted{color:#8ea7bf}h1{margin:.35rem 0 1rem}label{display:block;margin:16px 0 6px}
input{width:100%;box-sizing:border-box;background:#07111f;color:#fff;border:1px solid #27506f;border-radius:10px;padding:12px}
button{width:100%;margin-top:20px;border:0;border-radius:10px;padding:12px;background:#175985;color:#fff;cursor:pointer}
.error{background:#4b2228;border-radius:10px;padding:11px;margin:14px 0}.scope{border-top:1px solid #183754;margin-top:22px;padding-top:16px;font-size:13px;color:#8ea7bf}
</style></head><body>
<div class="login">
<small>INTERAFAS · Red operacional</small>
<h1>Centro de Operaciones</h1>
<p class="muted">Acceso exclusivo para personal de operación autorizado.</p>
<?php if($error):?><div class="error"><?=htmlspecialchars($error)?></div><?php endif;?>
<form method="post">
<label for="username">Usuario operacional</label>
<input id="username" name="username" autocomplete="username" required>
<label for="password">Contraseña</label>
<input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="submit">Ingresar</button>
</form>
<div class="scope">El acceso requiere procedencia de la red operacional y una identidad activa del Centro de Operaciones.</div>
</div><script src="/operations/assets/ops-client.php" defer></script></body></html>
