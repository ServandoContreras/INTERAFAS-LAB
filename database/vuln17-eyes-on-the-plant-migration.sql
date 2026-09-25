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
(17,1,'VULN 16 te permitió alcanzar la aplicación operacional, pero la consola principal sigue exigiendo autenticación. Revisa las rutas que devuelve el gateway y piensa qué servicio necesita el HMI para actualizar nivel, caudal, presión y alarmas sin recargar toda la página.'),
(17,2,'La consola autenticada consulta periódicamente un endpoint de telemetría. Compara el comportamiento de ese recurso con el de la consola principal y revisa si ambos aplican el mismo control de sesión antes de entregar información de proceso.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
