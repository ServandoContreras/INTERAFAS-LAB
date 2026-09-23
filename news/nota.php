<?php
$slug=$_GET['slug']??''; require_once __DIR__.'/includes/config.php'; [$s,$args]=visible_where();
$a=article_is_visible($slug);
if(!$a){http_response_code(404);$pageTitle='Nota no disponible';include __DIR__.'/includes/header.php';echo '<section class="wrap article"><h1>La nota todavía no está disponible.</h1><p>La cobertura se actualiza conforme se confirma nueva información.</p></section>';include __DIR__.'/includes/footer.php';exit;}
$pageTitle=$a['headline']; $media=media_info($a['hero_asset']); $points=coverage_points((int)$a['phase_required']);
$allVisible=visible_articles($a['category']); $related=array_values(array_filter($allVisible,fn($x)=>(int)$x['id']!==(int)$a['id'])); $related=array_slice($related,0,3);
$timelineItems=array_slice(array_values(array_filter(visible_articles(),fn($x)=>(int)$x['phase_required']>=max(0,(int)$a['phase_required']-1))),0,6);
include __DIR__.'/includes/header.php'; ?>
<?php if($a['breaking']): ?><div class="breaking"><div class="wrap"><b>ÚLTIMA HORA</b><span>Cobertura en desarrollo</span><?php if(special_interafas_unlocked()):?><a href="/especial.php">Ver especial →</a><?php endif;?></div></div><?php endif; ?>
<article class="article-shell">
<header class="article-head wrap"><span class="kicker"><?= h($a['category']) ?></span><h1><?= h($a['headline']) ?></h1><p class="dek"><?= h($a['subheadline']) ?></p><div class="byline"><b>Por <?= h($a['author']) ?></b><span>Actualizado <?= article_time($a) ?> · Cobertura metropolitana</span></div></header>
<div class="article-media wrap"><img src="<?= h($media['src']) ?>" onerror="this.onerror=null;this.src='<?= h($media['fallback']) ?>'" alt=""><div class="caption"><?= h($media['caption']) ?> <a href="<?= h($media['source']) ?>" target="_blank" rel="noopener"><?= h($media['credit']) ?></a></div></div>
<div class="wrap article-grid">
  <div class="article-body">
    <?php $paras=preg_split('/\n\n+/',trim($a['body'])); foreach($paras as $i=>$p): ?>
      <?php if($i===2): ?><div class="inline-update"><b>EN DESARROLLO</b><span>Pulso Metropolitano mantiene abierta esta cobertura y actualizará la nota conforme existan nuevos datos confirmados.</span></div><?php endif; ?>
      <p<?= $i===0?' class="first"':'' ?>><?= h($p) ?></p>
    <?php endforeach; ?>
    <div class="reporting-box"><span>COBERTURA PERIODÍSTICA</span><h3>Qué estamos siguiendo</h3><p>La redacción contrasta reportes ciudadanos, información institucional y datos operativos publicados durante la contingencia. Los datos que cambian con el desarrollo del incidente se identifican como información en actualización.</p></div>
  </div>
  <aside class="story-aside">
    <section><div class="aside-title">LO QUE SABEMOS</div><ul><?php foreach($points as $p): ?><li><?= h($p) ?></li><?php endforeach; ?></ul></section>
    <section><div class="aside-title">ESTADO AHORA</div><div class="phase-card"><b><?= h($state['phase_name']) ?></b><span>Fase <?= (int)$state['phase'] ?> del seguimiento</span></div></section>
    <section><div class="aside-title">CRONOLOGÍA</div><div class="mini-timeline"><?php foreach($timelineItems as $t): ?><a href="/nota.php?slug=<?= h($t['slug']) ?>"><time><?= article_time($t) ?></time><span><?= h($t['headline']) ?></span></a><?php endforeach; ?></div></section>
  </aside>
</div>
<?php if($related): ?><section class="wrap related"><div class="section-title"><h2>Más sobre este tema</h2></div><div class="cards compact"><?php foreach($related as $r): $rm=media_info($r['hero_asset']); ?><article><a href="/nota.php?slug=<?= h($r['slug']) ?>"><img src="<?= h($rm['src']) ?>" onerror="this.onerror=null;this.src='<?= h($rm['fallback']) ?>'" alt=""><div class="card-copy"><div class="card-time"><?= article_time($r) ?></div><h3><?= h($r['headline']) ?></h3></div></a></article><?php endforeach; ?></div></section><?php endif; ?>
</article><script>
try{const k='pulso_read_news_ids_v1';let r=JSON.parse(localStorage.getItem(k)||'[]');const id=String(<?= (int)$a['id'] ?>);if(!r.includes(id)){r.push(id);localStorage.setItem(k,JSON.stringify(r.slice(-500)));}}catch(e){}
window.PULSO_LIVE=true;</script><?php include __DIR__.'/includes/footer.php'; ?>
