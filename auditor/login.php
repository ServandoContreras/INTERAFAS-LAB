<?php
require_once __DIR__.'/includes/config.php';
if (is_auth()) { header('Location: index.php'); exit; }
$error=''; $notice='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $mode=(string)($_POST['mode']??'register');
    if($mode==='register'){
        $nombre=trim((string)($_POST['nombre']??''));
        $ap=trim((string)($_POST['apellido_paterno']??''));
        $am=trim((string)($_POST['apellido_materno']??''));
        $mat=normalize_matricula((string)($_POST['matricula']??''));
        if($nombre===''||$ap===''||$am===''||$mat==='') $error='Completa los cuatro campos para iniciar el laboratorio.';
        elseif(strlen($mat)<3) $error='La matrícula no parece válida.';
        else{
            $exists=db()->prepare("SELECT id FROM lab_students WHERE matricula=?"); $exists->execute([$mat]);
            if($exists->fetch()) $error='La matrícula ya está registrada. Usa la opción “Reingresar con matrícula”.';
            else{
                db()->beginTransaction();
                try{
                    $q=db()->prepare("INSERT INTO lab_students(nombre,apellido_paterno,apellido_materno,matricula) VALUES(?,?,?,?)"); $q->execute([$nombre,$ap,$am,$mat]);
                    $sid=(int)db()->lastInsertId();
                    $token=bin2hex(random_bytes(32));
                    $q=db()->prepare("INSERT INTO lab_attempts(student_id,attempt_token,status) VALUES(?,?,'active')"); $q->execute([$sid,$token]);
                    $aid=(int)db()->lastInsertId();
                    db()->commit();
                    session_regenerate_id(true); $_SESSION['student_id']=$sid; $_SESSION['attempt_id']=$aid; set_lab_cookie($token);
                    log_activity('ACCOUNT_CREATED','Cuenta de laboratorio creada','Registro inicial del estudiante.',['matricula'=>$mat]);
                    log_activity('LAB_STARTED','Laboratorio iniciado','Se abrió un nuevo intento de laboratorio.');
                    header('Location: index.php'); exit;
                }catch(Throwable $e){ if(db()->inTransaction()) db()->rollBack(); $error='No fue posible crear la cuenta.'; }
            }
        }
    }elseif($mode==='resume'){
        $mat=normalize_matricula((string)($_POST['matricula_resume']??''));
        $q=db()->prepare("SELECT * FROM lab_students WHERE matricula=?"); $q->execute([$mat]); $s=$q->fetch();
        if(!$s){$error='No existe una cuenta con esa matrícula.';}
        else{
            $q=db()->prepare("SELECT * FROM lab_attempts WHERE student_id=? ORDER BY id DESC LIMIT 1"); $q->execute([$s['id']]); $a=$q->fetch();
            if(!$a){$error='La cuenta no tiene un intento asociado.';}
            else{
                session_regenerate_id(true); $_SESSION['student_id']=(int)$s['id']; $_SESSION['attempt_id']=(int)$a['id']; set_lab_cookie(ensure_attempt_token((int)$a['id']));
                log_activity('LOGIN','Reingreso al portal','Sesión recuperada mediante matrícula.',['status'=>$a['status']]);
                header('Location: index.php'); exit;
            }
        }
    }
}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso · Auditor INTERAFAS</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-body">
<div class="login-shell">
<section class="login-card"><div class="login-mark">AI</div><p class="eyebrow">INTERAFAS · Ejercicio controlado</p><h1>Crear cuenta</h1><p class="muted">Antes de comenzar registra tus datos. Esta identidad quedará asociada a toda la bitácora del laboratorio.</p><?php if($error):?><div class="alert error"><?=h($error)?></div><?php endif;?>
<form method="post"><input type="hidden" name="mode" value="register"><label>Nombre<input name="nombre" autocomplete="given-name" required></label><label>Apellido paterno<input name="apellido_paterno" autocomplete="family-name" required></label><label>Apellido materno<input name="apellido_materno" required></label><label>Matrícula<input name="matricula" autocomplete="username" required></label><button class="primary" type="submit">Crear cuenta e iniciar</button></form></section>
<section class="login-card secondary-login"><p class="eyebrow">¿Ya comenzaste?</p><h2>Reingresar con matrícula</h2><p class="muted">Recupera el mismo intento y conserva banderas, eventos y bitácora.</p><form method="post"><input type="hidden" name="mode" value="resume"><label>Matrícula<input name="matricula_resume" autocomplete="username" required></label><button class="secondary wide" type="submit">Reingresar</button></form></section>
</div></body></html>
