-- VULN 19 · CHAIN-REACTION
-- El reto no agrega una vulnerabilidad aislada: acredita el encadenamiento
-- de los hallazgos que ya cruzaron web, red operacional, telemetría y firmware.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(19,1,'No busques un endpoint nuevo al azar. Después de obtener una sesión de mantenimiento mediante el estado SERVICE, vuelve al HMI y observa qué solicitudes adicionales realiza el cliente para construir el contexto de la sesión.'),
(19,2,'El reto exige correlacionar la misma tentativa. Conserva evidencia de los eventos que demostraron el cruce del gateway, la telemetría sin sesión y la aceptación del paquete de firmware no auténtico.'),
(19,3,'Inspecciona la respuesta y los encabezados del servicio de contexto cargado por el HMI. La validación sólo aparece cuando la sesión actual proviene del canal de mantenimiento y la bitácora de la tentativa contiene los saltos previos de la cadena.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
