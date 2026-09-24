-- VULN 02 · LOOK-CLOSER
-- Refina las pistas del Portal del Auditor para el hallazgo en recursos del cliente.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(2,1,'Mira más allá de lo que la página muestra en pantalla. Revisa también los recursos que el navegador carga para construirla.'),
(2,2,'La configuración de ejecución del frontend puede conservar referencias que el usuario normal nunca necesita abrir.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
