-- v0.5.1 · Auditor support + progressive INTERAFAS news gating
ALTER TABLE news_articles ADD COLUMN IF NOT EXISTS interafas_related TINYINT(1) NOT NULL DEFAULT 0 AFTER breaking;
UPDATE news_articles SET interafas_related=1
WHERE event_required IS NOT NULL
   OR headline LIKE '%INTERAFAS%'
   OR subheadline LIKE '%INTERAFAS%'
   OR body LIKE '%INTERAFAS%';
UPDATE news_articles SET interafas_related=0
WHERE event_required IS NULL
  AND headline NOT LIKE '%INTERAFAS%'
  AND subheadline NOT LIKE '%INTERAFAS%'
  AND body NOT LIKE '%INTERAFAS%';
