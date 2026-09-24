<?php
require __DIR__.'/includes/config.php';

$ref=trim((string)($_GET['ref']??''));
$checked=false;
$exists=false;

if($ref!==''){
    $checked=true;

    // VULN 07: SQL construido por concatenación directa de entrada pública.
    // Los errores se silencian de cara al usuario, creando un canal booleano.
    $sql="SELECT id FROM licitaciones WHERE numero = '".$ref."' LIMIT 1";

    try{
        $row=db()->query($sql)->fetch();
        $exists=(bool)$row;

        lab_event(
            'VULN07_REFERENCE_CHECK',
            'Consulta pública de referencia',
            $ref,
            [
                'challenge'=>7,
                'boolean_result'=>$exists,
                'query_length'=>strlen($sql)
            ],
            'interafas-web',
            'notice',
            0
        );
    }catch(Throwable $e){
        // Mismo comportamiento externo que una referencia inexistente.
        $exists=false;
        lab_event(
            'VULN07_QUERY_ERROR',
            'Error SQL ocultado en verificación de referencia',
            'Error de consulta no mostrado al cliente',
            [
                'challenge'=>7,
                'exception'=>get_class($e)
            ],
            'interafas-web',
            'notice',
            0
        );
    }
}

$pageTitle='Validar referencia';
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="wrap form-layout">
<div>
  <div class="eyebrow">Contratación pública</div>
  <h1>Validar referencia de procedimiento</h1>
  <p class="section-intro">Comprueba si una referencia corresponde a un procedimiento registrado en el portal de contrataciones.</p>
  <div class="info section-spacer"><b>Formato habitual:</b> LP-INT-000-2026</div>
</div>
<div class="form-card transaction-form">
  <form method="get">
    <div class="field">
      <label for="ref">Referencia</label>
      <input id="ref" name="ref" value="<?=htmlspecialchars($ref)?>" autocomplete="off" placeholder="LP-INT-000-2026">
    </div>
    <button class="btn btn-full" type="submit">Validar referencia</button>
  </form>

  <?php if($checked):?>
    <?php if($exists):?>
      <div class="success-panel section-spacer">
        <span>✓</span>
        <h2>Referencia localizada</h2>
        <p>La referencia consultada corresponde a un registro existente.</p>
      </div>
    <?php else:?>
      <div class="notice section-spacer">
        <b>Sin coincidencias</b><br>
        No se localizó una referencia con los datos proporcionados.
      </div>
    <?php endif;?>
  <?php endif;?>
</div>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
