<?php
require __DIR__.'/includes/config.php';

$id=max(1,(int)($_GET['id']??0));
$st=db()->prepare('SELECT * FROM reportes_ciudadanos WHERE id=? LIMIT 1');
$st->execute([$id]);
$r=$st->fetch();

if(!$r){
    http_response_code(404);
    exit('Reporte no localizado');
}

$pageTitle='Seguimiento de reporte';
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="wrap article">
<div class="eyebrow">Seguimiento ciudadano</div>
<h1><?=htmlspecialchars($r['folio'])?></h1>
<p class="section-intro">Consulta la información registrada y el estado actual del reporte.</p>

<div class="detail-grid section-spacer">
  <div><dt>Categoría</dt><dd><?=htmlspecialchars($r['categoria'])?></dd></div>
  <div><dt>Municipio</dt><dd><?=htmlspecialchars($r['municipio'])?></dd></div>
  <div><dt>Estado</dt><dd><?=htmlspecialchars($r['estado'])?></dd></div>
  <div><dt>Registrado</dt><dd><?=htmlspecialchars($r['creado'])?></dd></div>
</div>

<div class="form-card section-spacer">
  <div class="eyebrow">Descripción registrada</div>
  <div class="report-description"><?=$r['descripcion']?></div>
</div>

<div id="xss-proof-result" class="section-spacer"></div>
</div></section>

<script>
window.INTERAFAS_REPORT_CONTEXT = <?=json_encode([
  'reportId'=>(int)$r['id'],
  'proof'=>(string)$r['proof_token']
], JSON_UNESCAPED_SLASHES)?>;

window.validateStoredContentExecution = async function(){
  const ctx=window.INTERAFAS_REPORT_CONTEXT;
  const response=await fetch('/validate-stored-content.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({report_id:ctx.reportId,proof:ctx.proof})
  });
  const data=await response.json();
  if(data.ok){
    document.getElementById('xss-proof-result').innerHTML =
      '<div class="success-banner">Ejecución persistente confirmada · <code>'+data.flag+'</code></div>';
  }
  return data;
};
</script>
<?php include __DIR__.'/includes/footer.php'; ?>
