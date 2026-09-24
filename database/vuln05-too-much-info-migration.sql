-- VULN 05 · TOO-MUCH-INFO
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(5,1,'Busca una función pública que acepte un parámetro y prueba un valor fuera de los que la interfaz utiliza normalmente.'),
(5,2,'Un error de producción debería ser genérico. Si la respuesta empieza a hablar de archivos, líneas, excepciones o servicios internos, observa todo lo que revela.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
