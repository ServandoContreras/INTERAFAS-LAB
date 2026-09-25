-- VULN 18 · TRUST-THE-PACKAGE
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(18,1,'La telemetría del reto anterior indica que existe una actualización de firmware disponible, pero no expone directamente el canal. Revisa el cliente web compartido y localiza qué servicio consulta el HMI para obtener el estado detallado de firmware.'),
(18,2,'Consulta el servicio de estado de firmware y sigue la ruta al manifiesto que éste publica. Desde el manifiesto descarga el paquete aprobado y observa qué campo se calcula a partir del payload. SHA-256 permite detectar cambios si comparas contra un valor confiable, pero pregúntate quién puede generar un nuevo hash después de modificar el contenido.'),
(18,3,'Prueba una variante controlada del payload manteniendo device y estructura válidos. Recalcula SHA-256 sobre el texto exacto del payload y envía el paquete por el endpoint que declara el manifiesto. Si se acepta sin una firma o identidad criptográfica del emisor, documenta la diferencia entre integridad y autenticidad.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
