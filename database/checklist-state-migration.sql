CREATE TABLE IF NOT EXISTS lab_checklist_state (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  student_id INT NOT NULL,
  flag_number INT NOT NULL,
  item_index INT NOT NULL,
  is_checked TINYINT(1) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_checklist_attempt_item (attempt_id, flag_number, item_index),
  KEY idx_checklist_student (student_id),
  CONSTRAINT fk_checklist_attempt FOREIGN KEY (attempt_id) REFERENCES lab_attempts(id) ON DELETE CASCADE,
  CONSTRAINT fk_checklist_student FOREIGN KEY (student_id) REFERENCES lab_students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
