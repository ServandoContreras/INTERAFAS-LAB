-- ===== Portal del Auditor =====
CREATE TABLE IF NOT EXISTS flag_catalog (
  flag_number INT PRIMARY KEY,
  flag_value VARCHAR(180) NOT NULL UNIQUE,
  code_name VARCHAR(80) NOT NULL,
  title VARCHAR(180) NOT NULL,
  category VARCHAR(80) NOT NULL,
  difficulty TINYINT NOT NULL,
  weight TINYINT NOT NULL,
  triggers_phase TINYINT NULL
);

CREATE TABLE IF NOT EXISTS flag_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  flag_number INT NOT NULL,
  flag_value VARCHAR(180) NOT NULL,
  status ENUM('accepted','rejected') NOT NULL DEFAULT 'accepted',
  note VARCHAR(500) NULL,
  submitted_by VARCHAR(80) NOT NULL DEFAULT 'auditor',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_flag_accepted (flag_number, status),
  CONSTRAINT fk_flag_submission_catalog FOREIGN KEY (flag_number) REFERENCES flag_catalog(flag_number)
);

INSERT INTO flag_catalog (flag_number,flag_value,code_name,title,category,difficulty,weight,triggers_phase) VALUES
(1,'UPSLP_CNOIV-OPEN-DOOR-01','OPEN-DOOR','Archivo público olvidado','Exposición',1,1,NULL),
(2,'UPSLP_CNOIV-LOOK-CLOSER-02','LOOK-CLOSER','Información sensible en recursos del cliente','Reconocimiento',1,1,NULL),
(3,'UPSLP_CNOIV-NOT-YOURS-03','NOT-YOURS','Referencia directa insegura a objetos','Autorización',2,2,NULL),
(4,'UPSLP_CNOIV-OLD-MEMORIES-04','OLD-MEMORIES','Respaldo o archivo histórico expuesto','Exposición',2,2,NULL),
(5,'UPSLP_CNOIV-TOO-MUCH-INFO-05','TOO-MUCH-INFO','Divulgación de información mediante errores','Información',2,2,NULL),
(6,'UPSLP_CNOIV-WRONG-ROLE-06','WRONG-ROLE','Control de acceso por rol deficiente','Autorización',2,2,NULL),
(7,'UPSLP_CNOIV-SILENT-ANSWER-07','SILENT-ANSWER','SQL Injection ciega basada en inferencia','Inyección',4,4,NULL),
(8,'UPSLP_CNOIV-STORED-WORDS-08','STORED-WORDS','Entrada persistente procesada de forma insegura','Cliente',3,3,NULL),
(9,'UPSLP_CNOIV-TOO-DEEP-09','TOO-DEEP','Acceso a rutas fuera del directorio autorizado','Archivos',3,3,NULL),
(10,'UPSLP_CNOIV-WHO-ARE-YOU-10','WHO-ARE-YOU','Gestión débil de sesión','Sesión',3,3,NULL),
(11,'UPSLP_CNOIV-HIDDEN-HISTORY-11','HIDDEN-HISTORY','Descubrimiento de endpoint no documentado','API',3,3,NULL),
(12,'UPSLP_CNOIV-SECOND-ACCOUNT-12','SECOND-ACCOUNT','Autorización de objetos deficiente en API','API / Autorización',4,4,NULL),
(13,'UPSLP_CNOIV-PAYMENT-PATH-13','PAYMENT-PATH','Abuso de lógica de negocio en flujo de pago','Lógica de negocio',4,4,NULL),
(14,'UPSLP_CNOIV-TRUSTED-SUPPLIER-14','TRUSTED-SUPPLIER','Control deficiente de relaciones proveedor-contrato','Lógica de negocio',4,4,NULL),
(15,'UPSLP_CNOIV-BEHIND-THE-DESK-15','BEHIND-THE-DESK','Descubrimiento de configuración y arquitectura interna','Arquitectura',4,4,NULL),
(16,'UPSLP_CNOIV-BEYOND-THE-WEB-16','BEYOND-THE-WEB','Acceso indebido al gateway operacional','IT → OT',4,4,1),
(17,'UPSLP_CNOIV-EYES-ON-THE-PLANT-17','EYES-ON-THE-PLANT','Acceso no autorizado a la vista HMI','OT simulado',4,4,2),
(18,'UPSLP_CNOIV-TRUST-THE-PACKAGE-18','TRUST-THE-PACKAGE','Validación defectuosa de firmware simulado','Firmware simulado',5,5,3),
(19,'UPSLP_CNOIV-CHAIN-REACTION-19','CHAIN-REACTION','Encadenamiento de hallazgos IT → OT','Cadena de ataque',5,5,3),
(20,'UPSLP_CNOIV-NOW-YOU-CONTROL-20','NOW-YOU-CONTROL','Impacto operacional final completamente simulado','OT simulado',5,5,4)
ON DUPLICATE KEY UPDATE flag_value=VALUES(flag_value),code_name=VALUES(code_name),title=VALUES(title),category=VALUES(category),difficulty=VALUES(difficulty),weight=VALUES(weight),triggers_phase=VALUES(triggers_phase);
