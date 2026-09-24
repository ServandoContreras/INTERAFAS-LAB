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
         $autoCheck=db()->prepare("INSERT INTO lab_checklist_state(attempt_id,student_id,flag_number,item_index,is_checked,updated_at) VALUES(?,?,?,?,1,NOW()) ON DUPLICATE KEY UPDATE is_checked=1,updated_at=NOW()");
         foreach([0,1,2] as $autoItem){$autoCheck->execute([current_attempt_id(),current_student_id(),(int)$cat['flag_number'],$autoItem]);}
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
$q=db()->prepare("SELECT u.flag_number,u.hint_order,h.hint_text,u.created_at FROM lab_hint_usage u JOIN flag_hints h ON h.flag_number=u.flag_number AND h.hint_order=u.hint_order WHERE u.attempt_id=? ORDER BY u.created_at DESC,u.id DESC");$q->execute([current_attempt_id()]);$usedHints=$q->fetchAll(); $usedHintsByFlag=[]; foreach($usedHints as $uh){$usedHintsByFlag[(int)$uh['flag_number']][]=$uh;}
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

$challengeResources=[
1=>[
 ['type'=>'MDN','title'=>'robots.txt configuration','url'=>'https://developer.mozilla.org/en-US/docs/Web/Security/Practical_implementation_guides/Robots_txt','desc'=>'Qué es robots.txt, dónde se publica y para qué sirve realmente dentro de un sitio web.'],
 ['type'=>'RFC 9309','title'=>'Robots Exclusion Protocol','url'=>'https://www.rfc-editor.org/rfc/rfc9309.html','desc'=>'Estándar del protocolo. Su sección de seguridad explica por qué robots.txt no sustituye un control de acceso.'],
 ['type'=>'OWASP','title'=>'Old Backup and Unreferenced Files','url'=>'https://owasp.github.io/www-project-web-security-testing-guide/latest/4-Web_Application_Security_Testing/02-Configuration_and_Deployment_Management/04-Review_Old_Backup_and_Unreferenced_Files_for_Sensitive_Information','desc'=>'Metodología para localizar archivos residuales y directorios no enlazados; incluye robots.txt como fuente de pistas.']
],
2=>[
 ['type'=>'OWASP','title'=>'Review Web Page Content for Information Leakage','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/01-Information_Gathering/05-Review_Web_Page_Content_for_Information_Leakage/','desc'=>'Revisión de HTML, JavaScript y recursos recibidos por el navegador para detectar información expuesta.'],
 ['type'=>'MDN','title'=>'Source maps y recursos del frontend','url'=>'https://developer.mozilla.org/en-US/docs/Glossary/Source_map','desc'=>'Concepto de artefactos auxiliares del frontend y por qué pueden revelar detalles que no son visibles en la interfaz.'],
 ['type'=>'PortSwigger','title'=>'Information disclosure','url'=>'https://portswigger.net/web-security/information-disclosure','desc'=>'Casos prácticos de información sensible expuesta por configuración, archivos o código del cliente.']
],
3=>[
 ['type'=>'OWASP','title'=>'Insecure Direct Object References (IDOR)','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/05-Authorization/04-Insecure_Direct_Object_References/','desc'=>'Guía de pruebas para detectar referencias directas a objetos sin una comprobación adecuada de autorización.'],
 ['type'=>'OWASP Cheat Sheet','title'=>'IDOR Prevention Cheat Sheet','url'=>'https://cheatsheetseries.owasp.org/cheatsheets/Insecure_Direct_Object_Reference_Prevention_Cheat_Sheet.html','desc'=>'Explica por qué cambiar identificadores puede permitir consultar objetos de otro usuario y cómo debería prevenirse.'],
 ['type'=>'PortSwigger','title'=>'Insecure direct object references (IDOR)','url'=>'https://portswigger.net/web-security/access-control/idor','desc'=>'Explicación práctica de IDOR y escalamiento horizontal mediante identificadores controlados por el usuario.']
],
4=>[
 ['type'=>'OWASP','title'=>'Old Backup and Unreferenced Files','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/02-Configuration_and_Deployment_Management/04-Review_Old_Backup_and_Unreferenced_Files_for_Sensitive_Information/','desc'=>'Cómo localizar copias .bak, .old, archivos terminados en ~ y otros artefactos residuales que el servidor puede exponer.'],
 ['type'=>'PortSwigger','title'=>'Source code disclosure via backup files','url'=>'https://portswigger.net/web-security/information-disclosure/exploiting','desc'=>'Explica por qué una copia con otra extensión puede devolver el código fuente en lugar de ejecutarlo.'],
 ['type'=>'PortSwigger KB','title'=>'Backup file','url'=>'https://portswigger.net/kb/issues/006000d8_backup-file','desc'=>'Referencia específica sobre riesgos de archivos de respaldo expuestos dentro del web root.']
],
5=>[
 ['type'=>'OWASP','title'=>'Testing for Improper Error Handling','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/08-Testing_for_Error_Handling/01-Testing_For_Improper_Error_Handling/','desc'=>'Cómo provocar errores controlados y detectar rutas internas, excepciones, componentes y otros detalles expuestos.'],
 ['type'=>'PortSwigger','title'=>'Information disclosure in error messages','url'=>'https://portswigger.net/web-security/information-disclosure/exploiting/lab-infoleak-in-error-messages','desc'=>'Laboratorio específico sobre divulgación de información a través de respuestas de error demasiado detalladas.'],
 ['type'=>'PortSwigger','title'=>'Information disclosure vulnerabilities','url'=>'https://portswigger.net/web-security/information-disclosure','desc'=>'Contexto general sobre por qué mensajes de error y respuestas distintas pueden convertirse en una fuente de inteligencia técnica.']
],
6=>[
 ['type'=>'OWASP','title'=>'Testing for Bypassing Authorization Schema','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/05-Authorization_Testing/02-Testing_for_Bypassing_Authorization_Schema/','desc'=>'Cómo comprobar si una función está realmente protegida por el servidor.'],
 ['type'=>'OWASP API','title'=>'Broken Function Level Authorization','url'=>'https://owasp.org/API-Security/editions/2023/en/0xa5-broken-function-level-authorization/','desc'=>'Diferencia entre ocultar funciones y aplicar autorización efectiva.']
],
7=>[
 ['type'=>'PortSwigger','title'=>'Blind SQL injection','url'=>'https://portswigger.net/web-security/sql-injection/blind','desc'=>'Inferencia mediante diferencias de respuesta cuando la aplicación no muestra resultados ni errores SQL.'],
 ['type'=>'OWASP','title'=>'Testing for SQL Injection','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/05-Testing_for_SQL_Injection/','desc'=>'Fundamentos y metodología de pruebas de inyección SQL.']
],
8=>[
 ['type'=>'OWASP','title'=>'Testing for Stored XSS','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/02-Testing_for_Stored_Cross_Site_Scripting/','desc'=>'Cómo identificar entradas persistentes que después interpreta otro navegador.'],
 ['type'=>'PortSwigger','title'=>'Cross-site scripting','url'=>'https://portswigger.net/web-security/cross-site-scripting','desc'=>'Conceptos, contextos de ejecución y metodología práctica para XSS.']
],
9=>[
 ['type'=>'PortSwigger','title'=>'Path traversal','url'=>'https://portswigger.net/web-security/file-path-traversal','desc'=>'Cómo parámetros de archivo mal validados pueden escapar del directorio previsto.'],
 ['type'=>'OWASP','title'=>'Testing Directory Traversal File Include','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/07-Input_Validation_Testing/11.1-Testing_for_File_Inclusion/','desc'=>'Metodología de pruebas para inclusión y manipulación de rutas de archivos.']
],
10=>[
 ['type'=>'OWASP','title'=>'Session Management Testing','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/06-Session_Management_Testing/','desc'=>'Qué revisar durante creación, uso, renovación e invalidación de una sesión.'],
 ['type'=>'OWASP','title'=>'Session Management Cheat Sheet','url'=>'https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html','desc'=>'Propiedades esperadas de cookies, tokens e invalidación de sesiones.']
],
11=>[
 ['type'=>'OWASP','title'=>'Identify Application Entry Points','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/01-Information_Gathering/06-Identify_Application_Entry_Points/','desc'=>'Cómo construir el mapa real de endpoints más allá de los enlaces visibles.'],
 ['type'=>'PortSwigger','title'=>'API testing','url'=>'https://portswigger.net/web-security/api-testing','desc'=>'Descubrimiento de endpoints, documentación y superficies API no evidentes.']
],
12=>[
 ['type'=>'OWASP API','title'=>'Broken Object Level Authorization','url'=>'https://owasp.org/API-Security/editions/2023/en/0xa1-broken-object-level-authorization/','desc'=>'Por qué cada objeto solicitado por una API debe comprobar autorización.'],
 ['type'=>'OWASP WSTG','title'=>'API BOLA Testing','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/12-API_Testing/02-API_Broken_Object_Level_Authorization/','desc'=>'Metodología específica para comprobar BOLA en APIs.']
],
13=>[
 ['type'=>'OWASP','title'=>'Business Logic Security','url'=>'https://cheatsheetseries.owasp.org/cheatsheets/Business_Logic_Security_Cheat_Sheet.html','desc'=>'Cómo analizar supuestos del flujo y controles que el servidor debe volver a validar.'],
 ['type'=>'PortSwigger','title'=>'Business logic vulnerabilities','url'=>'https://portswigger.net/web-security/logic-flaws','desc'=>'Ejemplos y metodología para detectar abusos de flujo y validaciones insuficientes.']
],
14=>[
 ['type'=>'OWASP','title'=>'Business Logic Testing','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/10-Business_Logic_Testing/','desc'=>'Pruebas sobre reglas de negocio, relaciones entre entidades y secuencias previstas.'],
 ['type'=>'OWASP','title'=>'Authorization Testing Automation','url'=>'https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Testing_Automation_Cheat_Sheet.html','desc'=>'Cómo pensar autorización como actor, recurso y acción.']
],
15=>[
 ['type'=>'OWASP','title'=>'Information Leakage','url'=>'https://wstg.owasp.org/latest/4-Web_Application_Security_Testing/01-Information_Gathering/05-Review_Web_Page_Content_for_Information_Leakage/','desc'=>'Identificación de nombres internos, rutas, comentarios y configuración expuesta.'],
 ['type'=>'PortSwigger','title'=>'Information disclosure','url'=>'https://portswigger.net/web-security/information-disclosure','desc'=>'Cómo pequeños datos técnicos pueden combinarse para revelar arquitectura interna.']
],
16=>[
 ['type'=>'MITRE ATT&CK ICS','title'=>'Remote Services','url'=>'https://attack.mitre.org/techniques/T0886/','desc'=>'Contexto sobre servicios remotos y cruces de frontera hacia entornos ICS/OT.'],
 ['type'=>'MITRE ATT&CK ICS','title'=>'ICS Matrix','url'=>'https://attack.mitre.org/matrices/ics/','desc'=>'Mapa general de tácticas y técnicas relevantes en ambientes industriales.']
],
17=>[
 ['type'=>'MITRE ATT&CK ICS','title'=>'ICS Matrix','url'=>'https://attack.mitre.org/matrices/ics/','desc'=>'Referencia para comprender supervisión, operación y acciones adversarias en ICS.'],
 ['type'=>'CISA','title'=>'ICS Recommended Practices','url'=>'https://www.cisa.gov/topics/industrial-control-systems','desc'=>'Contexto defensivo sobre segmentación, acceso remoto y protección de sistemas de control.']
],
18=>[
 ['type'=>'MITRE ATT&CK ICS','title'=>'Modify Program / Firmware concepts','url'=>'https://attack.mitre.org/techniques/ics/','desc'=>'Técnicas ICS relacionadas con modificación de software, lógica y firmware.'],
 ['type'=>'CWE','title'=>'CWE-494: Download of Code Without Integrity Check','url'=>'https://cwe.mitre.org/data/definitions/494.html','desc'=>'Por qué una actualización debe verificar integridad y autenticidad antes de aceptarse.']
],
19=>[
 ['type'=>'MITRE ATT&CK ICS','title'=>'ICS Matrix','url'=>'https://attack.mitre.org/matrices/ics/','desc'=>'Úsala para ordenar el encadenamiento entre acceso, descubrimiento, movimiento e impacto.'],
 ['type'=>'OWASP','title'=>'Web Security Testing Guide','url'=>'https://wstg.owasp.org/','desc'=>'Referencia para relacionar hallazgos web individuales dentro de una evaluación completa.']
],
20=>[
 ['type'=>'MITRE ATT&CK ICS','title'=>'Impact','url'=>'https://attack.mitre.org/tactics/TA0105/','desc'=>'Marco para comprender qué significa impacto sobre un proceso industrial sin confundirlo con simple acceso.'],
 ['type'=>'MITRE ATT&CK ICS','title'=>'Inhibit Response Function','url'=>'https://attack.mitre.org/tactics/TA0107/','desc'=>'Contexto sobre acciones que afectan supervisión, respuesta y funciones de protección en ICS.']
]
];
$pageTitle='Banderas'; include __DIR__.'/includes/header.php';
?>
<section class="hero compact"><div><p class="eyebrow">Captura y validación</p><h1>Banderas descubiertas</h1><p>El catálogo permanece oculto. Aquí puedes validar banderas, seguir una ruta de comprobación por reto y solicitar pistas progresivas sin revelar la solución.</p></div></section>
<?php if($msg):?><div class="alert success"><?=h($msg)?></div><?php endif;?><?php if($err):?><div class="alert error"><?=h($err)?></div><?php endif;?><?php if($hintMsg):?><div class="alert hint-alert"><?=h($hintMsg)?></div><?php endif;?>
<?php if(attempt_is_active()):?><section class="flag-tools"><article class="panel capture"><div class="panel-head"><div><p class="eyebrow">Validación</p><h2>Registrar bandera</h2></div></div><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="flag"><label>Bandera<input name="flag" placeholder="UPSLP_CNOIV-..." autocomplete="off" required></label><label>Nota <span class="optional">opcional</span><textarea name="note" rows="2" placeholder="Observación o referencia del hallazgo"></textarea></label><button class="primary" type="submit">Validar y registrar</button></form></article></section><?php endif;?>
<section class="panel guide-panel"><div class="panel-head"><div><p class="eyebrow">Ruta de validación</p><h2>Checklist de trabajo por reto</h2></div></div><p class="muted">No es un solucionario. Marca cada comprobación conforme la realices. El avance se guarda automáticamente en tu intento y permanece al volver a ingresar.</p><div class="challenge-guide-grid"><?php foreach($challengeChecklist as $no=>$items): $done=false; foreach($obtained as $of){if((int)$of['flag_number']===$no){$done=true;break;}} ?><article class="challenge-guide <?=$done?'done':''?>" id="reto-<?=sprintf('%02d',$no)?>"><div class="guide-head"><b>Reto <?=sprintf('%02d',$no)?></b><span><?=$done?'Acreditado':'Pendiente'?></span></div><div class="checklist-items"><?php foreach($items as $idx=>$item): $checked=$done || !empty($checkState[$no][$idx]); ?><label class="checklist-row <?=$checked?'checked':''?>"><input type="checkbox" class="challenge-check" data-flag="<?=$no?>" data-item="<?=$idx?>" <?=$checked?'checked':''?> <?=$done?'disabled':''?>> <span><?=h($item)?></span></label><?php endforeach;?></div><?php if(!empty($challengeResources[$no])):?><div class="research-resources"><div class="research-title"><span>⌕</span><b>Recursos para investigar</b></div><p>No contienen la solución de INTERAFAS. Úsalos para comprender el concepto y decidir qué comprobar.</p><?php foreach($challengeResources[$no] as $res):?><a class="research-link" href="<?=h($res['url'])?>" target="_blank" rel="noopener noreferrer"><span class="resource-type"><?=h($res['type'])?></span><span><b><?=h($res['title'])?></b><small><?=h($res['desc'])?></small></span><span class="external-mark">↗</span></a><?php endforeach;?></div><?php endif;?><div class="challenge-hints"><div class="research-title"><span>?</span><b>Pistas de este reto</b></div><?php if(!empty($usedHintsByFlag[$no])): ?><div class="challenge-hint-list"><?php foreach(array_reverse($usedHintsByFlag[$no]) as $hintRow): ?><div class="challenge-hint-entry"><small>Pista <?= (int)$hintRow['hint_order'] ?> · <?=h($hintRow['created_at'])?></small><p><?=h($hintRow['hint_text'])?></p></div><?php endforeach;?></div><?php else:?><p class="challenge-hint-empty">Todavía no has solicitado pistas para este reto.</p><?php endif;?><?php if(attempt_is_active() && !$done): ?><form method="post" action="/flags.php#reto-<?=sprintf('%02d',$no)?>" class="challenge-hint-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="hint"><input type="hidden" name="hint_flag" value="<?=$no?>"><button class="secondary" type="submit">Mostrar siguiente pista</button></form><?php elseif($done): ?><div class="challenge-hint-locked">Reto acreditado · no se requieren más pistas.</div><?php endif;?></div></article><?php endforeach;?></div></section><script>window.INTERAFAS_CSRF=<?=json_encode(csrf_token())?>;</script>
<section class="panel"><div class="panel-head"><div><p class="eyebrow">Hallazgos acreditados</p><h2><?=count($obtained)?> banderas descubiertas</h2></div></div>
<?php if(!$obtained):?><div class="empty">Todavía no has acreditado ninguna bandera.</div><?php else:?><div class="flag-grid discovered"><?php foreach($obtained as $f):?><article class="flag-card done"><div class="flag-no"><?=sprintf('%02d',$f['flag_number'])?></div><div><span class="pill"><?=str_repeat('★',(int)$f['difficulty'])?> · <?=$f['weight']?> pt</span><h3><?=h($f['code_name'])?></h3><p><?=h($f['title'])?></p><small><?=h($f['category'])?><?= $f['triggers_phase'] ? ' · disparó F'.$f['triggers_phase'] : '' ?></small><b class="obtained">✓ <?=h($f['obtained_at'])?></b></div></article><?php endforeach;?></div><?php endif;?></section>
<section class="panel hint-history"><div class="panel-head"><div><p class="eyebrow">Historial global</p><h2>Registro de pistas solicitadas</h2></div><span class="pill"><?=count($usedHints)?> consultas</span></div><?php if(!$usedHints):?><div class="empty">Aún no has solicitado pistas. Cuando lo hagas, quedarán almacenadas aquí con su reto, nivel y fecha.</div><?php else:?><?php $grouped=[]; foreach($usedHints as $uh){$grouped[(int)$uh['flag_number']][]=$uh;} ksort($grouped); ?><div class="hint-groups"><?php foreach($grouped as $flagNo=>$rows):?><article class="hint-group"><div class="hint-group-title"><b>Reto <?=sprintf('%02d',$flagNo)?></b><span><?=count($rows)?> pista<?=count($rows)===1?'':'s'?></span></div><?php foreach(array_reverse($rows) as $h):?><div class="hint-entry"><small><?=h($h['created_at'])?> · Pista <?= (int)$h['hint_order']?></small><p><?=h($h['hint_text'])?></p></div><?php endforeach;?></article><?php endforeach;?></div><?php endif;?></section>
<?php include __DIR__.'/includes/footer.php'; ?>
