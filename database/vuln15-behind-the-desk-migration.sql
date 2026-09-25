-- VULN 15 · BEHIND-THE-DESK
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(15,1,'En Infraestructura estratégica hay un indicador que actualiza automáticamente el estado de integración del Centro de Operaciones. Revisa en Red qué solicitud HTTP genera esa actualización y no te limites al texto resumido que aparece en pantalla.'),
(15,2,'Inspecciona la respuesta JSON completa de esa solicitud. Conserva también cualquier zona o segmento CIDR que aparezca en los diagnósticos: forma parte del mapa de confianza. El nombre marcado como internal-only describe un servicio de la red interna y no se abre desde el navegador; application_route sí corresponde a una ruta del portal.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
