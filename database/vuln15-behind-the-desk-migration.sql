-- VULN 15 · BEHIND-THE-DESK
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(15,1,'En Infraestructura estratégica hay un indicador que actualiza automáticamente el estado de integración del Centro de Operaciones. Revisa en Red qué solicitud HTTP genera esa actualización y no te limites al texto resumido que aparece en pantalla.'),
(15,2,'Inspecciona la respuesta JSON completa de esa solicitud. Si aparecen campos de diagnóstico con nombres de componentes, fronteras de confianza o rutas internas, distingue entre un nombre sólo resoluble dentro de la red del laboratorio y una ruta de aplicación que sí pueda abrirse desde localhost.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
