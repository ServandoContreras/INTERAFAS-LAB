<?php
declare(strict_types=1);

require_once __DIR__.'/includes/config.php';
require_auth();

if($_SERVER['REQUEST_METHOD']!=='POST'){
    http_response_code(405);
    exit('Método no permitido.');
}
verify_csrf();

if(!attempt_is_active()){
    http_response_code(409);
    exit('El intento ya fue finalizado.');
}

$aid=current_attempt_id();
$student=current_student();
$matricula=normalize_matricula((string)($student['matricula']??''));
if($matricula==='') $matricula='MATRICULA';

$zipPath=null;

try{
    log_activity(
        'LAB_FINISH_REQUESTED',
        'Cierre de laboratorio solicitado',
        'El estudiante confirmó la finalización y la exportación automática de evidencias.'
    );

    $q=db()->prepare(
        "UPDATE lab_attempts
         SET status='finalized',finalized_at=NOW(),final_reason='student_finished'
         WHERE id=? AND status='active'"
    );
    $q->execute([$aid]);

    if($q->rowCount()!==1){
        throw new RuntimeException('No fue posible cerrar el intento activo.');
    }

    log_activity(
        'LAB_FINALIZED',
        'Laboratorio finalizado',
        'El intento quedó cerrado antes del restablecimiento del entorno.'
    );

    $telegram=telegram_send_log($aid);
    $q=db()->prepare(
        "UPDATE lab_attempts SET telegram_status=?,telegram_detail=? WHERE id=?"
    );
    $q->execute([
        (string)$telegram['status'],
        substr((string)$telegram['detail'],0,500),
        $aid
    ]);

    log_activity(
        'TELEGRAM_LOG',
        'Resultado del envío automático a Telegram',
        (string)$telegram['detail'],
        ['status'=>$telegram['status']]
    );

    // La evidencia se congela ANTES de limpiar el escenario.
    $zipPath=build_log_bundle_zip($aid);

    $reset=['database'=>['ok'=>false],'ot'=>['ok'=>false]];
    $resetError=null;

    try{
        $reset=reset_lab_runtime($aid);
    }catch(Throwable $e){
        $resetError=$e->getMessage();
    }

    // Añadir al paquete el resultado del restablecimiento sin modificar
    // los logs ya congelados.
    if(class_exists('ZipArchive') && is_file($zipPath)){
        $zip=new ZipArchive();
        if($zip->open($zipPath)===true){
            $zip->addFromString(
                'restablecimiento.json',
                json_encode([
                    'attempt_id'=>$aid,
                    'reset_at'=>date(DATE_ATOM),
                    'database'=>$reset['database']??null,
                    'ot'=>$reset['ot']??null,
                    'error'=>$resetError
                ],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
            );
            $zip->close();
        }
    }

    clear_lab_cookie();

    // Invalidar también la sesión del Auditor. El siguiente acceso comienza
    // desde la pantalla inicial y no conserva el intento anterior.
    $_SESSION=[];
    if(ini_get('session.use_cookies')){
        $params=session_get_cookie_params();
        setcookie(session_name(),'',[
            'expires'=>time()-42000,
            'path'=>$params['path'] ?: '/',
            'domain'=>$params['domain'] ?: '',
            'secure'=>(bool)$params['secure'],
            'httponly'=>(bool)$params['httponly'],
            'samesite'=>'Lax'
        ]);
    }
    session_destroy();

    $otOk=!empty($reset['ot']['ok']);
    $dbOk=!empty($reset['database']['ok']);

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.$matricula.'.zip"');
    header('Content-Length: '.filesize($zipPath));
    header('Cache-Control: no-store');
    header('X-INTERAFAS-Lab-Reset: '.($dbOk&&$otOk?'complete':'partial'));
    if(!$otOk){
        header('X-INTERAFAS-Reset-Warning: ot-reset-incomplete');
    }

    readfile($zipPath);
    @unlink($zipPath);
    exit;

}catch(Throwable $e){
    if($zipPath && is_file($zipPath)) @unlink($zipPath);
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'=>false,
        'error'=>'finish-failed',
        'message'=>$e->getMessage()
    ],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
