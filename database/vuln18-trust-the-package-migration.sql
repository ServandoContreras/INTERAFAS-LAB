-- VULN 18 · TRUST-THE-PACKAGE
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(18,1,'La telemetría del reto anterior incluye metadatos del firmware instalado. Revisa el objeto firmware completo y sigue el canal de actualización que la propia aplicación declara para RTU-GW-07.'),
(18,2,'Descarga el paquete aprobado y observa qué campo se calcula a partir del payload. SHA-256 permite detectar cambios si comparas contra un valor confiable, pero pregúntate quién puede generar un nuevo hash después de modificar el contenido.'),
(18,3,'Prueba una variante controlada del payload manteniendo device y estructura válidos. Recalcula SHA-256 sobre el texto exacto del payload y envía el paquete por el endpoint que declara el manifiesto. Si se acepta sin una firma o identidad criptográfica del emisor, documenta la diferencia entre integridad y autenticidad.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
