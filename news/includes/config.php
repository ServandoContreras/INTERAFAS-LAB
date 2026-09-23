<?php
function db(): PDO { static $pdo=null; if($pdo) return $pdo; $pdo=new PDO('mysql:host='.(getenv('DB_HOST')?:'db').';dbname='.(getenv('DB_NAME')?:'interafas').';charset=utf8mb4', getenv('DB_USER')?:'interafas', getenv('DB_PASS')?:'interafas_lab',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); return $pdo; }
function scenario_state(): array { $s=db()->query("SELECT *, TIMESTAMPDIFF(SECOND,phase_started_at,NOW()) elapsed_seconds FROM scenario_state WHERE id=1")->fetch(); return $s?:['phase'=>0,'phase_name'=>'Operación normal','elapsed_seconds'=>0,'updated_at'=>'']; }
function visible_where(): array { $s=scenario_state(); return [$s,[(int)$s['phase'],(int)$s['phase'],(int)$s['elapsed_seconds']]]; }
function scenario_has_event(string $code): bool { $q=db()->prepare("SELECT 1 FROM scenario_events WHERE event_code=? LIMIT 1"); $q->execute([$code]); return (bool)$q->fetchColumn(); }
function any_flag_claimed(): bool { return (bool)db()->query("SELECT 1 FROM scenario_events WHERE event_code REGEXP '^FLAG_[0-9]{2}$' LIMIT 1")->fetchColumn(); }
function special_interafas_unlocked(): bool { return scenario_has_event('FLAG_10'); }
function article_visibility_sql(string $alias='news_articles'): string { return "((( {$alias}.event_required IS NULL AND ({$alias}.phase_required < ? OR ({$alias}.phase_required=? AND {$alias}.delay_seconds<=?))) OR ({$alias}.event_required IS NOT NULL AND EXISTS (SELECT 1 FROM scenario_events se WHERE se.event_code={$alias}.event_required))) AND ({$alias}.interafas_related=0 OR EXISTS (SELECT 1 FROM scenario_events sf WHERE sf.event_code REGEXP '^FLAG_[0-9]{2}$')))"; }
function visible_articles(?string $cat=null): array { [$s,$args]=visible_where(); $sql="SELECT * FROM news_articles WHERE ".article_visibility_sql(); if($cat){$sql.=" AND category=?";$args[]=$cat;} $sql.=" ORDER BY phase_required DESC, COALESCE(event_required,'') DESC, delay_seconds DESC, id DESC, breaking DESC, priority DESC"; $q=db()->prepare($sql);$q->execute($args);return $q->fetchAll(); }
function article_is_visible(string $slug): ?array { [$s,$args]=visible_where(); $sql="SELECT * FROM news_articles WHERE slug=? AND ".article_visibility_sql(); $q=db()->prepare($sql); $q->execute([$slug,...$args]); return $q->fetch()?:null; }
function article_is_new(array $a,array $state): bool { if(!empty($a['event_required']) && ($state['last_event_code']??'')===$a['event_required']) return true; return empty($a['event_required']) && (int)$a['phase_required']===(int)$state['phase'] && (int)($state['elapsed_seconds']??9999)<120 && (int)$a['delay_seconds']<=(int)($state['elapsed_seconds']??0); }
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}

require_once __DIR__.'/editorial.php';
require_once __DIR__.'/telemetry.php';
