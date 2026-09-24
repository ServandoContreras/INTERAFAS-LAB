-- v0.5.4 · progresión editorial + sincronización automática de checklist

-- La sección Opinión comienza vacía y se alimenta conforme progresa el ejercicio.
UPDATE news_articles SET event_required='FLAG_03'
WHERE slug='entrevista-experta-infraestructura-no-se-protege-sola';

UPDATE news_articles SET event_required='FLAG_06'
WHERE slug='voces-calle-servicio-publico-confianza';

UPDATE news_articles SET event_required='FLAG_09'
WHERE slug='pingo-la-nube-no-arregla-tuberias';

UPDATE news_articles SET event_required='FLAG_12'
WHERE slug='reporte-especial-ciudad-dependencias-invisibles';

-- Los checklist de banderas ya acreditadas se consideran cumplidos.
INSERT INTO lab_checklist_state(attempt_id,student_id,flag_number,item_index,is_checked,updated_at)
SELECT s.attempt_id,a.student_id,s.flag_number,i.item_index,1,NOW()
FROM flag_submissions s
JOIN lab_attempts a ON a.id=s.attempt_id
JOIN (
  SELECT 0 AS item_index
  UNION ALL SELECT 1
  UNION ALL SELECT 2
) i
WHERE s.status='accepted' AND s.attempt_id IS NOT NULL
ON DUPLICATE KEY UPDATE is_checked=1,updated_at=NOW();
