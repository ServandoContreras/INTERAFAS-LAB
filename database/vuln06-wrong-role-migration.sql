-- VULN 06 · WRONG-ROLE
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(6,1,'Accede a la función reservada con tu sesión ciudadana y analiza la transacción HTTP completa. Además de lo que envía el navegador, revisa con atención la respuesta del servidor.'),
(6,2,'Si algún valor controlado por el cliente parece indicar el rol o privilegio de la sesión, prueba a modificar únicamente ese valor y repite la solicitud.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
