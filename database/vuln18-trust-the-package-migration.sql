-- VULN 18 · TRUST-THE-PACKAGE
-- Actualiza las pistas progresivas para instalaciones existentes.

CREATE TABLE IF NOT EXISTS firmware_support_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) UNIQUE NOT NULL,
  display_name VARCHAR(140) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'maintenance',
  password_hash VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO firmware_support_users(username,display_name,role,password_hash,active)
VALUES ('soporte.ot','Mesa de Soporte OT','maintenance','$2y$12$7kAHq4cXpLQG6fH8GuHKhOCzyw7QXZrUJBV5iyfsw190sWEIfTa6y',1)
ON DUPLICATE KEY UPDATE
  display_name=VALUES(display_name),
  role=VALUES(role),
  password_hash=VALUES(password_hash),
  active=VALUES(active);

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(18,1,'La telemetría indica que existe una actualización disponible, pero no expone directamente el canal. Revisa el cliente web compartido, identifica qué servicio consulta el HMI para obtener detalles de firmware y sigue únicamente las rutas declaradas hasta el manifiesto.'),
(18,2,'Examina release y support del manifiesto. Si encuentras una referencia de verificación heredada, conserva el valor original y clasifícalo antes de atacarlo: prefijo, longitud, caracteres, posible salt y algoritmo probable. Apóyate en hashID o hash-identifier; no asumas el tipo sólo por la longitud.'),
(18,3,'Para un SHA-1 crudo, Hashcat utiliza el modo 100. Guarda únicamente el digest hexadecimal en hash.txt y prueba primero un diccionario controlado: hashcat -m 100 -a 0 hash.txt candidatos.txt. Consulta los resultados con hashcat -m 100 hash.txt --show. John ofrece una alternativa con el formato raw-sha1.'),
(18,4,'Si el diccionario básico no resuelve el verificador, construye candidatos a partir del contexto observado: función del usuario, dispositivo, versión, build y números asociados. Después aplica reglas de mutación en lugar de saltar directamente a fuerza bruta exhaustiva. Valida cualquier candidato recalculando SHA-1 antes de usarlo.'),
(18,5,'Valida el candidato recalculando SHA-1 y úsalo únicamente contra el canal de mantenimiento declarado en el manifiesto. Respeta el esquema de autenticación indicado por support y no intentes reutilizar la identidad en el HMI. Después modifica sólo el payload del paquete, recalcula SHA-256 y comprueba si el instalador acepta contenido no aprobado sin verificar una firma del emisor.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
