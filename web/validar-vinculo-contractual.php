<?php
require __DIR__.'/includes/config.php';
lab_sensitive_page('validar-vinculo-contractual.php','Validar vínculo contractual','notice');

$pdo=db();
$contractId=max(0,(int)($_GET['contract_id']??0));
$providerId=max(0,(int)($_GET['provider_id']??0));
$checked=$contractId>0 && $providerId>0;
$contract=null;
$provider=null;
$valid=false;
$mismatch=false;

if($checked){
    $q=$pdo->prepare('SELECT id,numero,proveedor_id,objeto,estado FROM contratos WHERE id=? LIMIT 1');
    $q->execute([$contractId]);
    $contract=$q->fetch();

    $q=$pdo->prepare('SELECT id,nombre,servicio,estado FROM proveedores WHERE id=? LIMIT 1');
    $q->execute([$providerId]);
    $provider=$q->fetch();

    // El verificador comprueba cada registro por separado.
    $valid=(bool)$contract && (bool)$provider
        && $contract['estado']==='Vigente'
        && $provider['estado']==='Vigente';

    if($valid && (int)$contract['proveedor_id']!==$providerId){
        $mismatch=true;
        header('X-INTERAFAS-Relationship-Control: relationship-not-checked');
        header('X-INTERAFAS-Validation: UPSLP_CNOIV-TRUSTED-SUPPLIER-14');

        lab_event(
            'VULN14_RELATIONSHIP_VALIDATION_BYPASS',
            'Vínculo proveedor-contrato validado sin comprobar asociación',
            (string)$contract['numero'],
            [
                'challenge'=>14,
                'contract_id'=>(int)$contract['id'],
                'actual_provider_id'=>(int)$contract['proveedor_id'],
                'submitted_provider_id'=>$providerId
            ],
            'interafas-web',
            'warning',
            0
        );
    } elseif($checked){
        lab_event(
            'CONTRACT_RELATIONSHIP_CHECK',
            'Validación pública de vínculo contractual',
            $contract ? (string)$contract['numero'] : 'Contrato no localizado',
            [
                'contract_id'=>$contractId,
                'provider_id'=>$providerId,
                'result'=>$valid
            ],
            'interafas-web',
            'info',
            0
        );
    }
}

$pageTitle='Validar vínculo contractual';
include __DIR__.'/includes/header.php';
?>
<section class="section"><div class="wrap form-layout">
<div>
  <div class="eyebrow">Transparencia contractual</div>
  <h1>Validar vínculo proveedor–contrato</h1>
  <p class="section-intro">Comprueba la vigencia de los registros asociados a una contratación publicada por INTERAFAS.</p>
  <div class="info section-spacer"><b>Uso:</b> selecciona un contrato desde el listado público o introduce los identificadores que aparecen en el padrón y en la consulta contractual.</div>
</div>
<div class="form-card transaction-form">
  <form method="get">
    <div class="form-grid">
      <div class="field">
        <label for="contract_id">ID de contrato</label>
        <input id="contract_id" name="contract_id" type="number" min="1" value="<?= $contractId?:'' ?>" required>
      </div>
      <div class="field">
        <label for="provider_id">ID de proveedor</label>
        <input id="provider_id" name="provider_id" type="number" min="1" value="<?= $providerId?:'' ?>" required>
      </div>
    </div>
    <button class="btn btn-full" type="submit">Validar vínculo</button>
  </form>

  <?php if($checked): ?>
    <?php if($valid): ?>
      <div class="success-panel section-spacer">
        <span>✓</span>
        <h2>Vínculo vigente</h2>
        <p>Los registros consultados se encuentran vigentes para efectos de la verificación pública.</p>
        <?php if($contract): ?><p><b><?= htmlspecialchars($contract['numero']) ?></b><br><?= htmlspecialchars($contract['objeto']) ?></p><?php endif; ?>
        <?php if($provider): ?><p><b><?= htmlspecialchars($provider['nombre']) ?></b><br><?= htmlspecialchars($provider['servicio']) ?></p><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="notice section-spacer">
        <b>Vínculo no verificable</b><br>
        Uno o ambos registros no existen o no se encuentran vigentes.
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if($mismatch): ?>
    <div class="form-card section-spacer">
      <div class="eyebrow">Resultado de validación</div>
      <h2>Relación inconsistente aceptada</h2>
      <p>El servicio validó dos registros vigentes sin comprobar que el contrato estuviera asociado al proveedor indicado.</p>
      <p><b>Referencia:</b><br><code>UPSLP_CNOIV-TRUSTED-SUPPLIER-14</code></p>
    </div>
  <?php endif; ?>
</div>
</div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
