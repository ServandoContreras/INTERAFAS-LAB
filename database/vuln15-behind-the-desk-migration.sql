-- VULN 15 · BEHIND-THE-DESK
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(15,1,'Ya encontraste antes una configuración de ejecución del frontend. Revisa nuevamente el valor buildManifest y consulta ese recurso completo; los manifiestos de despliegue pueden conservar referencias de soporte que la interfaz nunca muestra.'),
(15,2,'Dentro del manifiesto sigue cualquier referencia relacionada con soporte o diagnóstico de despliegue. Si el recurso resultante menciona hosts, puertos, rutas internas o un conector operacional, ordénalos como dependencias y revisa también los encabezados de la respuesta.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
