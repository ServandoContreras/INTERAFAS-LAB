<?php
require_once __DIR__.'/includes/config.php'; require_auth(); log_page_view('flags.php','Banderas');
$msg=''; $err=''; $hintMsg=''; $selectedHintFlag=max(1,min(20,(int)($_POST['hint_flag']??$_GET['hint_flag']??1)));
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!attempt_is_active()){$err='El laboratorio ya fue finalizado. La captura de banderas y solicitud de pistas está cerrada.';}
 else{
  verify_csrf(); $action=(string)($_POST['action']??'flag');
  if($action==='hint'){
    $flagNo=$selectedHintFlag;
    $q=db()->prepare("SELECT h.hint_order,h.hint_text FROM flag_hints h WHERE h.flag_number=? AND NOT EXISTS (SELECT 1 FROM lab_hint_usage u WHERE u.attempt_id=? AND u.flag_number=h.flag_number AND u.hint_order=h.hint_order) ORDER BY h.hint_order LIMIT 1");
    $q->execute([$flagNo,current_attempt_id()]); $hint=$q->fetch();
    if(!$hint){$hintMsg='No quedan más pistas configuradas para el reto '.sprintf('%02d',$flagNo).'.';}
    else{
      $i=db()->prepare("INSERT INTO lab_hint_usage(attempt_id,student_id,flag_number,hint_order) VALUES(?,?,?,?)");
      $i->execute([current_attempt_id(),current_student_id(),$flagNo,(int)$hint['hint_order']]);
      log_activity('HINT_REQUESTED','Pista consultada','Reto '.sprintf('%02d',$flagNo).' · pista '.(int)$hint['hint_order'],['flag_number'=>$flagNo,'hint_order'=>(int)$hint['hint_order']],'auditor','notice');
      $hintMsg='Reto '.sprintf('%02d',$flagNo).' · Pista '.(int)$hint['hint_order'].': '.$hint['hint_text'];
    }
  } else {
    $flag=trim((string)($_POST['flag']??'')); $note=trim((string)($_POST['note']??''));
    $q=db()->prepare("SELECT * FROM flag_catalog WHERE flag_value=?"); $q->execute([$flag]); $cat=$q->fetch();
    if(!$cat){$err='La bandera no es válida.'; log_activity('FLAG_REJECTED','Intento de bandera rechazado','Se intentó registrar una bandera que no pertenece al catálogo.',['submitted'=>$flag]);}
    else{
      $dup=db()->prepare("SELECT id,created_at FROM flag_submissions WHERE attempt_id=? AND flag_number=? AND status='accepted' LIMIT 1"); $dup->execute([current_attempt_id(),$cat['flag_number']]); $d=$dup->fetch();
      if($d){$err='La bandera '.sprintf('%02d',$cat['flag_number']).' ya fue registrada el '.$d['created_at'].'.'; log_activity('FLAG_DUPLICATE','Bandera duplicada','Se volvió a enviar una bandera ya acreditada.',['flag_number'=>(int)$cat['flag_number']]);}
      else{
       db()->beginTransaction();
       try{
         $ins=db()->prepare("INSERT INTO flag_submissions(attempt_id,flag_number,flag_value,status,note,submitted_by) VALUES(?,?,?,'accepted',?,?)");
         $student=current_student(); $ins->execute([current_attempt_id(),$cat['flag_number'],$flag,$note,$student['matricula']??'']);
         $ev=db()->prepare("INSERT IGNORE INTO scenario_events(attempt_id,event_code,flag_number,source,detail) VALUES(?,?,?,?,?)");
         $ev->execute([current_attempt_id(),'FLAG_'.sprintf('%02d',$cat['flag_number']),(int)$cat['flag_number'],'auditor-portal','Bandera validada desde Portal del Auditor']);
         $allQ=db()->prepare("SELECT COUNT(DISTINCT flag_number) FROM flag_submissions WHERE attempt_id=? AND status='accepted'"); $allQ->execute([current_attempt_id()]);
         if((int)$allQ->fetchColumn()>=20){ $ev->execute([current_attempt_id(),'ALL_FLAGS',null,'auditor-portal','Las 20 banderas del laboratorio fueron acreditadas']); }
         db()->commit();
         log_activity('FLAG_ACCEPTED','Bandera acreditada','Bandera '.sprintf('%02d',$cat['flag_number']).' registrada correctamente.',['flag_number'=>(int)$cat['flag_number'],'code_name'=>$cat['code_name'],'difficulty'=>(int)$cat['difficulty'],'weight'=>(int)$cat['weight']]);
         $msg='Bandera '.sprintf('%02d',$cat['flag_number']).' validada. El escenario fue actualizado automáticamente.';
       }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();$err='No fue posible registrar la bandera.';log_activity('FLAG_ERROR','Error al registrar bandera',$e->getMessage());}
      }
    }
  }
 }
}
$q=db()->prepare("SELECT c.*,s.created_at AS obtained_at,s.note FROM flag_submissions s JOIN flag_catalog c ON c.flag_number=s.flag_number WHERE s.attempt_id=? AND s.status='accepted' ORDER BY s.created_at,c.flag_number");$q->execute([current_attempt_id()]);$obtained=$q->fetchAll();
$q=db()->prepare("SELECT u.flag_number,u.hint_order,h.hint_text,u.created_at FROM lab_hint_usage u JOIN flag_hints h ON h.flag_number=u.flag_number AND h.hint_order=u.hint_order WHERE u.attempt_id=? ORDER BY u.created_at DESC,u.id DESC");$q->execute([current_attempt_id()]);$usedHints=$q->fetchAll();
$q=db()->prepare("SELECT flag_number,item_index,is_checked FROM lab_checklist_state WHERE attempt_id=?");$q->execute([current_attempt_id()]);$checkRows=$q->fetchAll(); $checkState=[]; foreach($checkRows as $cr){$checkState[(int)$cr['flag_number']][(int)$cr['item_index']]=(int)$cr['is_checked']===1;}

$challengeChecklist=[
1=>['Parte de una función pública normal del portal y observa cómo están organizados sus recursos.','Comprueba si el servidor permite explorar el directorio que contiene recursos legítimos.','Distingue los documentos esperados de cualquier artefacto residual de operación o despliegue.'],
2=>['Inspecciona lo que el navegador recibe además de lo que muestra.','Busca referencias, rutas o comentarios que aporten contexto adicional.','Conserva evidencia del recurso del lado cliente donde aparezca el hallazgo.'],
3=>['Compara objetos similares dentro de una misma función.','Verifica si cambiar un identificador modifica el recurso consultado.','Confirma si el servidor valida que el objeto pertenece al usuario autenticado.'],
4=>['Revisa rastros de versiones anteriores y archivos residuales.','Comprueba si copias o respaldos quedaron accesibles.','Explica qué información adicional aporta el artefacto encontrado.'],
5=>['Prueba entradas inesperadas de manera controlada.','Observa diferencias entre respuestas normales y respuestas de error.','Registra cualquier detalle técnico que no debería exponerse al usuario final.'],
6=>['Compara funciones disponibles entre perfiles o contextos distintos.','No asumas que un botón oculto equivale a una autorización real.','Valida la autorización en el servidor para la acción observada.'],
7=>['Compara respuestas ante condiciones controladas y repetibles.','Busca diferencias pequeñas aunque la aplicación no muestre errores.','Documenta cómo confirmaste el comportamiento sin depender de una sola respuesta.'],
8=>['Identifica entradas de usuario que vuelvan a mostrarse más adelante.','Comprueba si el contenido se almacena y reaparece en otra vista.','Diferencia entre texto mostrado y contenido interpretado por el navegador.'],
9=>['Revisa cómo se construyen las solicitudes de descarga o lectura de archivos.','Comprueba si la aplicación limita correctamente la ubicación del recurso solicitado.','Conserva la petición y respuesta que demuestren el límite o su ausencia.'],
10=>['Observa el ciclo completo de una sesión: inicio, uso y cierre.','Comprueba qué identificadores persisten después de cambiar de estado.','Valida si una sesión deja de ser útil cuando debería invalidarse.'],
11=>['Revisa scripts, rutas y patrones que sugieran servicios no visibles en el menú.','Comprueba si esas rutas responden y con qué controles.','Documenta el propósito aparente del servicio descubierto.'],
12=>['Compara respuestas de la API al variar objetos equivalentes.','Verifica si el servidor aplica propiedad y autorización por objeto.','Registra únicamente datos del laboratorio necesarios para demostrar el hallazgo.'],
13=>['Sigue el flujo completo de una transacción de principio a fin.','Anota qué valores viajan entre etapas y cuáles vuelve a validar el servidor.','Comprueba si alterar una etapa cambia el resultado final.'],
14=>['Relaciona proveedor, contrato y usuario como entidades separadas.','Comprueba qué autorización existe al modificar vínculos entre registros.','Documenta qué control debería existir en el servidor.'],
15=>['Busca referencias a infraestructura que un usuario común no necesita conocer.','Relaciona nombres de servicios, hosts o rutas internas con lo descubierto antes.','Construye un pequeño mapa de dependencias con evidencia.'],
16=>['Determina dónde termina la aplicación pública y comienza el entorno operacional.','Busca el componente que actúa como puente entre ambos entornos.','Documenta qué control impide o permite cruzar esa frontera.'],
17=>['Comprueba qué exige la interfaz de supervisión antes de mostrar datos.','Distingue funciones de lectura de funciones de control.','Registra variables operacionales observadas sin alterar todavía el proceso.'],
18=>['Analiza la estructura de un paquete de actualización del laboratorio.','Identifica qué propiedades valida el actualizador y cuáles da por supuestas.','Documenta por separado integridad, autenticidad y resultado de la instalación.'],
19=>['Relaciona hallazgos previos en lugar de buscar una vulnerabilidad aislada.','Construye la secuencia de pasos que conecta web, arquitectura y entorno operacional.','Conserva evidencia de cada salto de la cadena.'],
20=>['Define primero cuál es el impacto operacional simulado que debes demostrar.','Comprueba qué prerrequisitos de la cadena anterior habilitan la acción final.','Registra estado antes/después, alarmas y evidencia de recuperación del simulador.']
];
$pageTitle='Banderas'; include __DIR__.'/includes/header.php';
?>
<section class="hero compact"><div><p class="eyebrow">Captura y validación</p><h1>Banderas descubiertas</h1><p>El catálogo permanece oculto. Aquí puedes validar banderas, seguir una ruta de comprobación por reto y solicitar pistas progresivas sin revelar la solución.</p></div></section>
<?php if($msg):?><div class="alert success"><?=h($msg)?></div><?php endif;?><?php if($err):?><div class="alert error"><?=h($err)?></div><?php endif;?><?php if($hintMsg):?><div class="alert hint-alert"><?=h($hintMsg)?></div><?php endif;?>
<?php if(attempt_is_active()):?><section class="grid two flag-tools"><article class="panel capture"><div class="panel-head"><div><p class="eyebrow">Validación</p><h2>Registrar bandera</h2></div></div><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="flag"><label>Bandera<input name="flag" placeholder="UPSLP_CNOIV-..." autocomplete="off" required></label><label>Nota <span class="optional">opcional</span><textarea name="note" rows="2" placeholder="Observación o referencia del hallazgo"></textarea></label><button class="primary" type="submit">Validar y registrar</button></form></article>
<article class="panel hint-panel"><div class="panel-head"><div><p class="eyebrow">Apoyo opcional</p><h2>Solicitar una pista</h2></div></div><p class="muted">Las pistas son breves y progresivas. No muestran la bandera ni la solución. Cada consulta queda registrada en tu bitácora.</p><form method="post" class="hint-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="hint"><label>Número de reto<select name="hint_flag" required><?php for($i=1;$i<=20;$i++):?><option value="<?=$i?>" <?=$selectedHintFlag===$i?'selected':''?>>Reto <?=sprintf('%02d',$i)?></option><?php endfor;?></select></label><button class="secondary" type="submit">Mostrar siguiente pista</button></form></article></section><?php endif;?>
<section class="panel guide-panel"><div class="panel-head"><div><p class="eyebrow">Ruta de validación</p><h2>Checklist de trabajo por reto</h2></div></div><p class="muted">No es un solucionario. Marca cada comprobación conforme la realices. El avance se guarda automáticamente en tu intento y permanece al volver a ingresar.</p><div class="challenge-guide-grid"><?php foreach($challengeChecklist as $no=>$items): $done=false; foreach($obtained as $of){if((int)$of['flag_number']===$no){$done=true;break;}} ?><article class="challenge-guide <?=$done?'done':''?>"><div class="guide-head"><b>Reto <?=sprintf('%02d',$no)?></b><span><?=$done?'Acreditado':'Pendiente'?></span></div><div class="checklist-items"><?php foreach($items as $idx=>$item): $checked=!empty($checkState[$no][$idx]); ?><label class="checklist-row <?=$checked?'checked':''?>"><input type="checkbox" class="challenge-check" data-flag="<?=$no?>" data-item="<?=$idx?>" <?=$checked?'checked':''?>> <span><?=h($item)?></span></label><?php endforeach;?></div></article><?php endforeach;?></div></section><script>window.INTERAFAS_CSRF=<?=json_encode(csrf_token())?>;</script>
<section class="panel"><div class="panel-head"><div><p class="eyebrow">Hallazgos acreditados</p><h2><?=count($obtained)?> banderas descubiertas</h2></div></div>
<?php if(!$obtained):?><div class="empty">Todavía no has acreditado ninguna bandera.</div><?php else:?><div class="flag-grid discovered"><?php foreach($obtained as $f):?><article class="flag-card done"><div class="flag-no"><?=sprintf('%02d',$f['flag_number'])?></div><div><span class="pill"><?=str_repeat('★',(int)$f['difficulty'])?> · <?=$f['weight']?> pt</span><h3><?=h($f['code_name'])?></h3><p><?=h($f['title'])?></p><small><?=h($f['category'])?><?= $f['triggers_phase'] ? ' · disparó F'.$f['triggers_phase'] : '' ?></small><b class="obtained">✓ <?=h($f['obtained_at'])?></b></div></article><?php endforeach;?></div><?php endif;?></section>
<section class="panel hint-history"><div class="panel-head"><div><p class="eyebrow">Archivo de apoyo</p><h2>Pistas solicitadas por reto</h2></div><span class="pill"><?=count($usedHints)?> consultas</span></div><?php if(!$usedHints):?><div class="empty">Aún no has solicitado pistas. Cuando lo hagas, quedarán almacenadas aquí con su reto, nivel y fecha.</div><?php else:?><?php $grouped=[]; foreach($usedHints as $uh){$grouped[(int)$uh['flag_number']][]=$uh;} ksort($grouped); ?><div class="hint-groups"><?php foreach($grouped as $flagNo=>$rows):?><article class="hint-group"><div class="hint-group-title"><b>Reto <?=sprintf('%02d',$flagNo)?></b><span><?=count($rows)?> pista<?=count($rows)===1?'':'s'?></span></div><?php foreach(array_reverse($rows) as $h):?><div class="hint-entry"><small><?=h($h['created_at'])?> · Pista <?= (int)$h['hint_order']?></small><p><?=h($h['hint_text'])?></p></div><?php endforeach;?></article><?php endforeach;?></div><?php endif;?></section>
<?php include __DIR__.'/includes/footer.php'; ?>
