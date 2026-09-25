-- VULN 17 · EYES-ON-THE-PLANT
-- Crea el dominio de identidad operacional y actualiza las pistas del reto.

CREATE TABLE IF NOT EXISTS operational_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) UNIQUE NOT NULL,
  display_name VARCHAR(140) NOT NULL,
  role VARCHAR(40) NOT NULL DEFAULT 'viewer',
  password_hash VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO operational_users(username,display_name,role,password_hash,active)
VALUES ('operador01','Operador de Turno','operator','$2y$12$nHNOwd69TufUXU2.TaGtneAS4zQx11rW.eX3OSKADBZfDd1esA2.O',1)
ON DUPLICATE KEY UPDATE
  display_name=VALUES(display_name),
  role=VALUES(role),
  password_hash=VALUES(password_hash),
  active=VALUES(active);

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(17,1,'VULN 16 revela únicamente la puerta de entrada legítima del Centro de Operaciones. Ábrela con la misma condición de red operacional y utiliza las herramientas de desarrollador para inventariar los recursos que carga la página; evita adivinar URLs.'),
(17,2,'Inspecciona los scripts cargados por el acceso operacional y busca patrones habituales de aplicaciones dinámicas como fetch, endpoint, api o rutas /operations/. Identifica qué servicio utilizaría el HMI para actualizar datos sin recargar toda la interfaz.'),(17,3,'Conserva la misma condición de red operacional pero no inicies sesión como operador. Compara la respuesta de /operations/ con la del servicio descubierto: si uno exige identidad y el otro entrega datos de proceso, revisa los encabezados de ambos y documenta la diferencia.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
