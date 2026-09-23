-- ===== v0.4.3 · correlación entre portal del auditor e INTERAFAS =====
ALTER TABLE lab_attempts ADD COLUMN IF NOT EXISTS attempt_token VARCHAR(96) NULL AFTER student_id;
CREATE UNIQUE INDEX IF NOT EXISTS uq_lab_attempt_token ON lab_attempts(attempt_token);

-- Clasificación opcional para facilitar análisis forense.
ALTER TABLE lab_activity ADD COLUMN IF NOT EXISTS source_system VARCHAR(40) NOT NULL DEFAULT 'auditor' AFTER action_label;
ALTER TABLE lab_activity ADD COLUMN IF NOT EXISTS severity VARCHAR(16) NOT NULL DEFAULT 'info' AFTER source_system;
ALTER TABLE lab_activity ADD INDEX IF NOT EXISTS idx_activity_source (attempt_id, source_system, created_at);
