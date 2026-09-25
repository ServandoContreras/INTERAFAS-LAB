-- VULN 16 · BEYOND-THE-WEB
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(16,1,'El gateway descubierto antes responde 403 y declara una política asociada al segmento operacional que ya documentaste. Revisa tanto el cuerpo como los encabezados de esa respuesta y piensa qué dato podría usar una aplicación situada detrás de un proxy para identificar la dirección original del cliente.'),
(16,2,'Investiga X-Forwarded-For y el uso de rangos privados para segmentación. Repite la petición al mismo gateway declarando una dirección válida dentro del CIDR operacional que obtuviste en el reto anterior. No cambies la ruta ni intentes acceder directamente al servicio internal-only.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
