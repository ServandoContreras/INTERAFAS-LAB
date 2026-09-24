-- VULN 03 · NOT-YOURS
-- Referencias administrativas de validación + escenario IDOR.
ALTER TABLE expedientes
  ADD COLUMN IF NOT EXISTS referencia_validacion VARCHAR(120) NULL AFTER observaciones;

UPDATE expedientes
SET referencia_validacion=CONCAT('INT-EXP-', LPAD(id,5,'0'))
WHERE referencia_validacion IS NULL OR referencia_validacion='';

UPDATE expedientes
SET referencia_validacion='UPSLP_CNOIV-NOT-YOURS-03'
WHERE id=2;

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(3,1,'Observa qué identificador utiliza el portal cuando abre tu expediente.'),
(3,2,'Estar autenticado no significa que el servidor haya comprobado que el objeto solicitado te pertenece.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
