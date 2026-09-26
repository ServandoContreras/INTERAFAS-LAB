-- VULN 20 · NOW-YOU-CONTROL
-- Cierre del laboratorio: parámetro de firmware manipulado -> cascada operacional simulada.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(20,1,'VULN-20 sólo se habilita después de completar correctamente la cadena 6/6 del reto anterior. Cuando la secuencia quede acreditada, observa qué cambia en el menú de Ingeniería del HMI.'),
(20,2,'Entra a Firmware y descarga BACKUP. Ese archivo muestra la estructura exacta de la configuración aceptada por RTU-GW-07. Conserva el original y trabaja sobre una copia.'),
(20,3,'Busca en el backup un parámetro que represente directamente una condición física del proceso. pressure_setpoint_bar tiene un valor normal de ingeniería de 4.2 bar. No necesitas cambiar device, version, mode ni diagnostic.'),
(20,4,'Modifica únicamente pressure_setpoint_bar por un valor distinto del baseline y carga el JSON resultante. El problema que se demuestra es que el firmware acepta un setpoint crítico sin una validación adecuada de límites de ingeniería.'),
(20,5,'Regresa al HMI inmediatamente. La degradación ocurre de forma progresiva cada pocos segundos: crecen presión, caudal, alarmas y criticidad mientras cae la disponibilidad hasta que toda la interfaz entra en estado rojo.'),
(20,6,'Cuando aparezca CATASTROPHIC STATE no necesitas realizar ninguna otra acción. La bandera final se mostrará automáticamente, en negritas, junto al nombre INTERAFAS en la cabecera del sistema.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);


-- VULN-20 · Snapshot operacional publicado en Pulso Metropolitano.
CREATE TABLE IF NOT EXISTS scenario_snapshots (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  snapshot_key VARCHAR(100) NOT NULL UNIQUE,
  attempt_id INT NULL,
  mime_type VARCHAR(40) NOT NULL DEFAULT 'image/jpeg',
  image_blob MEDIUMBLOB NOT NULL,
  width INT NOT NULL,
  height INT NOT NULL,
  metadata_json MEDIUMTEXT NULL,
  captured_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_scenario_snapshot_attempt (attempt_id),
  KEY idx_scenario_snapshot_time (captured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
VALUES
(4,'VULN20_SNAPSHOT',0,'ultima','captura-hmi-estado-catastrofico',
'Captura interna muestra el HMI de INTERAFAS durante el estado catastrófico',
'La imagen registra miles de alarmas, pérdida de disponibilidad y variables operativas fuera de su línea base durante el punto más crítico del incidente.',
'Una captura obtenida del sistema de supervisión operacional de INTERAFAS muestra el momento en que la consola metropolitana entró en estado crítico durante la contingencia. En la imagen se observan indicadores de presión y caudal fuera de su línea base, un incremento masivo de alarmas y una caída pronunciada de la disponibilidad reportada por el sistema.\n\nLa imagen fue preservada como parte de la secuencia técnica del incidente y posteriormente incorporada a la cobertura de Pulso Metropolitano. Especialistas consultados señalan que una pantalla de supervisión no demuestra por sí sola qué ocurrió físicamente en cada punto de la red, pero sí permite documentar qué información recibían los operadores en ese momento.\n\nINTERAFAS informó que los registros del HMI, los cambios de configuración y la telemetría serán contrastados con evidencia de campo antes de establecer una cronología definitiva. La institución mantiene bajo revisión la modificación de parámetros de control y los mecanismos que permitieron que una configuración fuera aceptada fuera de la línea base de ingeniería.\n\nLa captura se integra a la investigación junto con registros de autenticación, eventos del gateway RTU-GW-07 y datos históricos de presión, caudal y disponibilidad. La revisión busca determinar en qué momento una alteración digital comenzó a producir consecuencias visibles para la operación metropolitana.\n\nPulso Metropolitano mantendrá la imagen como evidencia contextual de la cobertura mientras continúan las verificaciones técnicas y administrativas.',
'Unidad de Investigación · Pulso Metropolitano','snapshot-vuln20',220,0,1,1);
