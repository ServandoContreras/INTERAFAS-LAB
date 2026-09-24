-- VULN 12 · SECOND-ACCOUNT
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(12,1,'Autenticación y autorización no son lo mismo. Retoma el endpoint histórico del reto anterior y observa qué valor de la URL decide qué cuenta se consulta.'),
(12,2,'Mantén exactamente la misma sesión y cambia sólo el parámetro account por otro número de cuenta válido del laboratorio. Si una cuenta distinta devuelve HTTP 200 y datos que no pertenecen a tu usuario, compara la respuesta y sus encabezados con los de tu propia cuenta.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
