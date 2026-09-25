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
(17,1,'VULN 16 revela únicamente la puerta de entrada legítima del Centro de Operaciones. Ábrela con la misma condición de red interna y revisa qué recursos JavaScript carga la página antes de intentar adivinar otras rutas.'),
(17,2,'El cliente web compartido contiene la configuración que utiliza el HMI para refrescar datos de proceso. Localiza el endpoint de telemetría en ese recurso y compara su respuesta sin sesión operacional con la protección aplicada a /operations/.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
