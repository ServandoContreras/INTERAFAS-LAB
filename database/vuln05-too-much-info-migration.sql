-- VULN 05 · TOO-MUCH-INFO
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(5,1,'Las funciones de exportación suelen recibir parámetros para decidir qué conjunto de datos entregar. Revisa la solicitud que genera el botón CSV y observa qué valor controla el contenido solicitado.'),
(5,2,'Un error de producción debería ser genérico. Si la respuesta empieza a hablar de archivos, líneas, excepciones o servicios internos, observa todo lo que revela.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
