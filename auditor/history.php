<?php
require_once __DIR__.'/includes/config.php'; require_auth(); log_page_view('history.php','Historial del ejercicio');
$aid=current_attempt_id();
$q=db()->prepare("SELECT created_at,'flag' kind, CONCAT('FLAG_',LPAD(c.flag_number,2,'0')) code, CONCAT('Bandera ',LPAD(c.flag_number,2,'0'),' acreditada') title, c.title detail, c.triggers_phase phase FROM flag_submissions s JOIN flag_catalog c ON c.flag_number=s.flag_number WHERE s.attempt_id=? AND s.status='accepted' UNION ALL SELECT e.created_at,'event' kind,e.event_code code,COALESCE(c.title,CASE WHEN e.event_code='RECOVERY_STARTED' THEN 'Inicio de recuperación' WHEN e.event_code='ALL_FLAGS' THEN 'Todas las banderas acreditadas' ELSE 'Evento del escenario' END) title,COALESCE(e.detail,'Evento registrado') detail,c.triggers_phase phase FROM scenario_events e LEFT JOIN flag_catalog c ON c.flag_number=e.flag_number WHERE e.attempt_id=? ORDER BY created_at DESC");
$q->execute([$aid,$aid]); $rows=$q->fetchAll();
$q=db()->prepare("SELECT COUNT(*) FROM flag_submissions WHERE attempt_id=? AND status='accepted'");$q->execute([$aid]);$flags=(int)$q->fetchColumn();
$q=db()->prepare("SELECT COUNT(*) FROM scenario_events WHERE attempt_id=?");$q->execute([$aid]);$events=(int)$q->fetchColumn();
$state=scenario_state();
$pageTitle='Historial'; include __DIR__.'/includes/header.php'; ?>
<section class="hero compact"><div><p class="eyebrow">Trazabilidad resumida</p><h1>Historial del ejercicio</h1><p>Una sola línea de tiempo reúne las banderas acreditadas y los hitos que fueron modificando el escenario.</p></div><div class="phase-card"><span>Fase actual</span><strong>F<?= (int)$state['phase']?> · <?=h($state['phase_name'])?></strong><small><?=$flags?> banderas · <?=$events?> eventos</small></div></section>
<section class="panel"><div class="timeline unified-history"><?php if(!$rows):?><div class="empty">Todavía no existen hallazgos o eventos del escenario.</div><?php endif;?><?php foreach($rows as $r):?><article class="history-<?=h($r['kind'])?>"><div class="dot"></div><div class="time"><?=h($r['created_at'])?></div><div class="event"><span class="pill"><?=h($r['code'])?></span><h3><?=h($r['title'])?></h3><p><?=h($r['detail'])?></p><?php if($r['phase']):?><small>Relacionado con F<?= (int)$r['phase']?>.</small><?php endif;?></div></article><?php endforeach;?></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
