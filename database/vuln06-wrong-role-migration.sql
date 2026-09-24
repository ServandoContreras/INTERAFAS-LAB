-- VULN 06 · WRONG-ROLE
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(6,1,'Busca una función del portal que esté identificada para personal autorizado. Accede con una sesión ciudadana y compara el rol actual con el rol que la función dice requerir.'),
(6,2,'Estar autenticado no implica estar autorizado. Comprueba si una cuenta con rol citizen puede ejecutar una operación de gestión reservada a otro rol.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
