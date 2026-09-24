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
(7,1,'Si puedes distinguir una condición verdadera de una falsa, ya tienes un canal de comunicación con la base de datos aunque la aplicación no muestre resultados ni errores.'),
(7,2,'MySQL/MariaDB mantiene metadatos sobre bases, tablas y columnas. Investiga DATABASE() e information_schema antes de intentar localizar datos concretos.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
