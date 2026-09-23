-- ===== v0.4.2 · Identidad del estudiante, intento y bitácora =====
CREATE TABLE IF NOT EXISTS lab_students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido_paterno VARCHAR(100) NOT NULL,
  apellido_materno VARCHAR(100) NOT NULL,
  matricula VARCHAR(40) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lab_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  status ENUM('active','finalized') NOT NULL DEFAULT 'active',
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finalized_at TIMESTAMP NULL,
  final_reason VARCHAR(80) NULL,
  telegram_status VARCHAR(40) NULL,
  telegram_detail VARCHAR(500) NULL,
  CONSTRAINT fk_attempt_student FOREIGN KEY (student_id) REFERENCES lab_students(id)
);

CREATE TABLE IF NOT EXISTS lab_activity (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  student_id INT NOT NULL,
  action_code VARCHAR(80) NOT NULL,
  action_label VARCHAR(180) NOT NULL,
  detail TEXT NULL,
  metadata_json LONGTEXT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_attempt (attempt_id, created_at),
  CONSTRAINT fk_activity_attempt FOREIGN KEY (attempt_id) REFERENCES lab_attempts(id),
  CONSTRAINT fk_activity_student FOREIGN KEY (student_id) REFERENCES lab_students(id)
);

-- Una misma bandera puede existir una vez por intento, no una sola vez para toda la instalación.
ALTER TABLE flag_submissions ADD COLUMN IF NOT EXISTS attempt_id INT NULL AFTER id;
ALTER TABLE flag_submissions ADD INDEX idx_flag_number_fk (flag_number);
ALTER TABLE flag_submissions DROP INDEX uq_flag_accepted;
ALTER TABLE flag_submissions ADD UNIQUE KEY uq_attempt_flag_status (attempt_id, flag_number, status);
ALTER TABLE flag_submissions ADD INDEX idx_flag_attempt (attempt_id, created_at);

-- Asociar también los eventos narrativos al intento del estudiante.
ALTER TABLE scenario_events ADD COLUMN IF NOT EXISTS attempt_id INT NULL AFTER id;
ALTER TABLE scenario_events DROP INDEX event_code;
ALTER TABLE scenario_events ADD INDEX idx_scenario_attempt (attempt_id, created_at);
ALTER TABLE scenario_events ADD UNIQUE KEY uq_attempt_event (attempt_id, event_code);
