-- VULN 06 · WRONG-ROLE
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(6,1,'Accede a la función reservada con tu sesión ciudadana y analiza la solicitud HTTP completa. No revises solo la URL: observa también los datos que el navegador envía automáticamente.'),
(6,2,'Si algún valor controlado por el cliente parece indicar el rol o privilegio de la sesión, prueba a modificar únicamente ese valor y repite la solicitud.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
