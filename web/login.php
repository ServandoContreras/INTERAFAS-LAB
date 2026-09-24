<?php
require __DIR__.'/includes/config.php';

$error='';

/*
 * VULN 10 · WHO-ARE-YOU
 * La aplicación crea una sesión antes de autenticar y deliberadamente NO
 * regenera el identificador al elevar el estado de anónimo a autenticado.
 * El reto consiste en demostrar que el PHPSESSID previo al login continúa
 * representando una sesión autenticada.
 */
if(empty($_SESSION['user']) && empty($_SESSION['vuln10_preauth_sid'])){
    $_SESSION['vuln10_preauth_sid']=session_id();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $stmt=db()->prepare('SELECT id,usuario,nombre,rol,password_hash FROM usuarios WHERE usuario=?');
    $stmt->execute([trim($_POST['usuario'] ?? '')]);
    $u=$stmt->fetch();

    if($u && password_verify($_POST['password'] ?? '', $u['password_hash'])){
        $preAuthSid=(string)($_SESSION['vuln10_preauth_sid'] ?? session_id());

        // Intencionalmente vulnerable: aquí debería ejecutarse
        // session_regenerate_id(true) antes de consolidar la sesión autenticada.
        $_SESSION['user']=[
            'id'=>$u['id'],
            'usuario'=>$u['usuario'],
            'nombre'=>$u['nombre'],
            'rol'=>$u['rol']
        ];

        $_SESSION['vuln10_session_probe']=[
            'preauth_sid'=>$preAuthSid,
            'authenticated_sid'=>session_id(),
            'login_user_agent'=>substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''),0,255),
            'authenticated_at'=>time()
        ];

        setcookie('portal_role',(string)$u['rol'],[
            'expires'=>0,
            'path'=>'/',
            'httponly'=>false,
            'samesite'=>'Lax'
        ]);

        lab_event(
            'VULN10_LOGIN_WITHOUT_ROTATION',
            'Autenticación sin rotación de identificador de sesión',
            $u['usuario'],
            [
                'challenge'=>10,
                'same_session_id'=>$preAuthSid===session_id()
            ],
            'interafas-web',
            'notice',
            0
        );

        header('Location: /panel.php');
        exit;
    }

    $error='Credenciales incorrectas.';
}

$pageTitle='Acceso ciudadano';
include __DIR__.'/includes/header.php';
?>
<section class="login-section"><div class="wrap login-grid">
  <div class="login-intro"><div class="eyebrow">Servicios digitales</div><h1>Portal ciudadano</h1><p>Consulta tu servicio, consumos, facturas, pagos, trámites y documentos desde un solo lugar.</p><div class="login-points"><div><span>01</span><p><b>Consulta centralizada</b><small>Historial completo de tu cuenta y servicio.</small></p></div><div><span>02</span><p><b>Disponibilidad digital</b><small>Acceso al portal las 24 horas.</small></p></div><div><span>03</span><p><b>Atención integrada</b><small>Pagos, reportes, documentos y notificaciones.</small></p></div></div></div>
  <div class="login-card"><div class="login-card-head"><small>INTERAFAS</small><h2>Iniciar sesión</h2><p>Ingresa a tu cuenta digital.</p></div><?php if($error): ?><div class="notice compact"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post"><div class="field"><label for="usuario">Usuario</label><input id="usuario" name="usuario" autocomplete="username" required placeholder="Tu usuario"></div><div class="field"><label for="password">Contraseña</label><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"></div><button class="btn btn-full">Ingresar al portal</button></form><div class="register-callout"><span>¿Primera vez en Mi Portal?</span><a href="/registro.php">Crear una cuenta</a></div><div class="login-help">¿Problemas para ingresar? Contacta a soporte del portal.</div></div>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
