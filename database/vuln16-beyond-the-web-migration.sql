-- VULN 16 · BEYOND-THE-WEB
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(16,1,'El gateway descubierto antes responde 403 e indica que sólo acepta solicitudes de la red interna. Revisa tanto el cuerpo como los encabezados de esa respuesta y piensa qué dato podría usar una aplicación situada detrás de un proxy para identificar la IP original del cliente.'),
(16,2,'Investiga X-Forwarded-For. Repite la petición al mismo gateway añadiendo ese encabezado con una dirección de loopback, por ejemplo 127.0.0.1. No cambies la ruta ni intentes acceder directamente al servicio internal-only.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
