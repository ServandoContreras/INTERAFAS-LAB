-- VULN 08 · STORED-WORDS
CREATE TABLE IF NOT EXISTS reportes_ciudadanos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(40) UNIQUE NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  descripcion TEXT NOT NULL,
  correo VARCHAR(160) NULL,
  estado VARCHAR(40) NOT NULL DEFAULT 'Recibido',
  proof_token VARCHAR(96) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(8,1,'Busca una entrada ciudadana que no solo se envíe, sino que quede almacenada y pueda consultarse después en otra vista.'),
(8,2,'Si el contenido reaparece dentro del HTML, comprueba si el navegador lo trata como texto o como marcado ejecutable. Una ejecución JavaScript inocua es suficiente para demostrar el fallo.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
