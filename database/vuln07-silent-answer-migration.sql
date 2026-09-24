-- VULN 07 · SILENT-ANSWER
-- Registro interno usado por el laboratorio para demostrar extracción mediante SQLi ciega.
CREATE TABLE IF NOT EXISTS portal_validation_meta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  context_key VARCHAR(80) UNIQUE NOT NULL,
  verification_token VARCHAR(120) NOT NULL
);

INSERT INTO portal_validation_meta(context_key,verification_token)
VALUES ('procurement-reference','UPSLP_CNOIV-SILENT-ANSWER-07')
ON DUPLICATE KEY UPDATE verification_token=VALUES(verification_token);

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(7,1,'En la validación de referencias, compara respuestas ante entradas que hagan que una condición sea siempre verdadera y siempre falsa. La ausencia de errores visibles no descarta una inyección.'),
(7,2,'Si ya lograste controlar una respuesta booleana, ese mismo canal puede servir para inferir información carácter por carácter. Investiga enumeración de esquema y extracción mediante blind SQL injection.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
