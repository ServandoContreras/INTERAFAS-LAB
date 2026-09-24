SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER DATABASE interafas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(80) UNIQUE NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(160) UNIQUE NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  telefono VARCHAR(40) NOT NULL,
  fecha_nacimiento DATE NULL,
  curp VARCHAR(18) NULL,
  domicilio_notificacion VARCHAR(220) NULL,
  codigo_postal VARCHAR(10) NULL,
  rol VARCHAR(40) NOT NULL DEFAULT 'citizen',
  password_hash VARCHAR(255) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS cuentas_servicio (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  cuenta VARCHAR(30) UNIQUE NOT NULL,
  contrato VARCHAR(30) UNIQUE NOT NULL,
  medidor VARCHAR(40) NOT NULL,
  domicilio VARCHAR(220) NOT NULL,
  colonia VARCHAR(120) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  codigo_postal VARCHAR(10) NOT NULL,
  tipo_servicio VARCHAR(80) NOT NULL DEFAULT 'Doméstico',
  tarifa VARCHAR(80) NOT NULL DEFAULT 'Doméstica estándar',
  fecha_alta DATE NOT NULL,
  estatus VARCHAR(40) NOT NULL DEFAULT 'Activo',
  alias VARCHAR(80) NULL,
  sector VARCHAR(100) NULL,
  saldo DECIMAL(12,2) NOT NULL DEFAULT 0,
  lectura_actual DECIMAL(12,2) NOT NULL DEFAULT 0,
  fecha_lectura DATE NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS expedientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  cuenta_id INT NULL,
  folio VARCHAR(40) UNIQUE NOT NULL,
  tipo VARCHAR(120) NOT NULL,
  fecha DATE NOT NULL,
  estatus VARCHAR(80) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  canal VARCHAR(60) NOT NULL DEFAULT 'Portal ciudadano',
  ultima_actualizacion DATE NULL,
  observaciones VARCHAR(255) NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (cuenta_id) REFERENCES cuentas_servicio(id)
);
CREATE TABLE IF NOT EXISTS documentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  nombre VARCHAR(160) NOT NULL,
  tipo VARCHAR(80) NOT NULL,
  fecha DATE NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
CREATE TABLE IF NOT EXISTS facturas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cuenta_id INT NOT NULL,
  folio VARCHAR(40) UNIQUE NOT NULL,
  periodo VARCHAR(20) NOT NULL,
  fecha_emision DATE NOT NULL,
  fecha_limite DATE NOT NULL,
  lectura_anterior DECIMAL(12,2) NOT NULL,
  lectura_actual DECIMAL(12,2) NOT NULL,
  consumo_m3 DECIMAL(10,2) NOT NULL,
  cargo_agua DECIMAL(12,2) NOT NULL,
  cargo_saneamiento DECIMAL(12,2) NOT NULL,
  otros DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL,
  saldo DECIMAL(12,2) NOT NULL,
  estado VARCHAR(40) NOT NULL,
  FOREIGN KEY (cuenta_id) REFERENCES cuentas_servicio(id)
);
CREATE TABLE IF NOT EXISTS pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  cuenta_id INT NOT NULL,
  factura_id INT NULL,
  referencia VARCHAR(40) UNIQUE NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  metodo VARCHAR(60) NOT NULL,
  ultimos4 VARCHAR(8) NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  estado VARCHAR(40) DEFAULT 'Aplicado',
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  FOREIGN KEY (cuenta_id) REFERENCES cuentas_servicio(id),
  FOREIGN KEY (factura_id) REFERENCES facturas(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS consumos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cuenta_id INT NOT NULL,
  periodo VARCHAR(20) NOT NULL,
  consumo_m3 DECIMAL(10,2) NOT NULL,
  lectura DECIMAL(12,2) NOT NULL,
  promedio_zona_m3 DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (cuenta_id) REFERENCES cuentas_servicio(id)
);
CREATE TABLE IF NOT EXISTS notificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  mensaje VARCHAR(300) NOT NULL,
  tipo VARCHAR(40) NOT NULL DEFAULT 'info',
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  leida TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(160) NOT NULL,
  servicio VARCHAR(180) NOT NULL,
  estado VARCHAR(40) NOT NULL
);
CREATE TABLE IF NOT EXISTS contratos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(40) UNIQUE NOT NULL,
  proveedor_id INT NOT NULL,
  objeto VARCHAR(240) NOT NULL,
  modalidad VARCHAR(80) NOT NULL,
  monto DECIMAL(14,2) NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  origen_recurso VARCHAR(140) NOT NULL,
  estado VARCHAR(40) NOT NULL,
  FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);
CREATE TABLE IF NOT EXISTS licitaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(40) UNIQUE NOT NULL,
  tipo VARCHAR(80) NOT NULL,
  objeto VARCHAR(260) NOT NULL,
  area VARCHAR(140) NOT NULL,
  origen_recurso VARCHAR(160) NOT NULL,
  presupuesto DECIMAL(14,2) NOT NULL,
  publicacion DATE NOT NULL,
  visita DATE NULL,
  junta DATE NULL,
  apertura DATE NOT NULL,
  fallo DATE NULL,
  estado VARCHAR(40) NOT NULL,
  bases_archivo VARCHAR(180) NOT NULL
);
CREATE TABLE IF NOT EXISTS portal_validation_meta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  context_key VARCHAR(80) UNIQUE NOT NULL,
  verification_token VARCHAR(120) NOT NULL
);

INSERT INTO portal_validation_meta(context_key,verification_token)
VALUES ('procurement-reference','UPSLP_CNOIV-SILENT-ANSWER-07')
ON DUPLICATE KEY UPDATE verification_token=VALUES(verification_token);

CREATE TABLE IF NOT EXISTS tramites_solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(40) UNIQUE NOT NULL,
  tipo VARCHAR(100) NOT NULL,
  nombre VARCHAR(160) NOT NULL,
  correo VARCHAR(160) NOT NULL,
  telefono VARCHAR(50),
  municipio VARCHAR(100),
  datos TEXT,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  estatus VARCHAR(50) DEFAULT 'Recibido'
);
CREATE TABLE IF NOT EXISTS reportes_ciudadanos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(40) UNIQUE NOT NULL,
  categoria VARCHAR(80) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  descripcion TEXT NOT NULL,
  correo VARCHAR(160) NULL,
  estado VARCHAR(40) NOT NULL DEFAULT 'Recibido',
  proof_token VARCHAR(96) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pagos_simulados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  referencia VARCHAR(40) UNIQUE NOT NULL,
  cuenta VARCHAR(60) NOT NULL,
  monto DECIMAL(12,2) NOT NULL,
  metodo VARCHAR(60) NOT NULL,
  ultimos4 VARCHAR(8) NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  estado VARCHAR(40) DEFAULT 'Aplicado'
);

INSERT INTO usuarios (usuario,nombre,correo,municipio,telefono,fecha_nacimiento,curp,domicilio_notificacion,codigo_postal,rol,password_hash) VALUES
('ciudadano01','Mariana Torres','mariana.torres@example.test','Saint Louis','444-000-0101','1988-04-16','TOMM880416MSPRRN08','Av. de la República 145, Centro','78000','citizen','$2y$12$jCza05fT9OxvOME7ekjN4.LAf0nB6rEC.PWsifzI9N9arUF/u8m4a'),
('ciudadano02','Carlos Rivera','carlos.rivera@example.test','Soledade','444-000-0102','1979-11-03','RIRC791103HSPVVR02','Calle Jacarandas 83, La Estación','78430','citizen','$2y$12$YBJy5ghtxQFoynbADQU1XOqX57P9Yef/YHRgbxD3YRbz/1sxOUb1i'),
('ciudadano03','Alejandra Ruiz','alejandra.ruiz@example.test','Cerro de San Pablo','444-000-0103','1992-06-21','RUAA920621MSPLZL04','Priv. del Lago 17, Los Pinos','78415','citizen','$2y$12$IQrUfwcxfwPx7Oq4BZqQ3eXg9mJU1jLaTWZWI.qkcXka58JLXRW4u'),
('ciudadano04','José Manuel Vega','jose.vega@example.test','Saint Louis','444-000-0104','1984-01-28','VEGJ840128HSPGNS07','Av. Universidad 1208, Jardines','78290','citizen','$2y$12$yB44sexU2iVPQ84rYth8z.jLXAihATyItIz7/A5mtAfU3Ovu1D3Lq'),
('ciudadano05','Fernanda López','fernanda.lopez@example.test','Soledade','444-000-0105','1990-09-12','LOPF900912MSPPRR03','Calle Cedros 214, San Felipe','78433','citizen','$2y$12$h99MNN1x38Ylsnz0bgQE.eAnplB1m7/ODgMVr8ttIDRDqx4eozgCK'),
('ciudadano06','Miguel Ángel Herrera','miguel.herrera@example.test','Saint Louis','444-000-0106','1975-03-07','HEGM750307HSPRNG09','Av. Industrias 2310, Valle Dorado','78399','citizen','$2y$12$gnGs7kE6WRKeNcQizpFxB.cHetS1YpnMiznJGJYPzDZ22r3ygldCq'),
('ciudadano07','Daniela Castro','daniela.castro@example.test','Cerro de San Pablo','444-000-0107','1995-08-30','CASD950830MSPSTN01','Camino Real 442, El Mirador','78418','citizen','$2y$12$ewQb7zkLlQPY3oJ.AmsAB.NCYsi.3ZkSZD63MNa4O9LBd5A17Av5e'),
('ciudadano08','Roberto Medina','roberto.medina@example.test','Saint Louis','444-000-0108','1981-05-14','MEDR810514HSPDNB05','Calle Naranjos 55, Lomas','78210','citizen','$2y$12$PONMWBbwx/wGSaX51uw2tOf0CGuIhUzTFFP5MyBwGP2EwNoAtIyAW'),
('ciudadano09','Patricia Salas','patricia.salas@example.test','Soledade','444-000-0109','1987-12-02','SALP871202MSPLTR06','Av. Libertad 708, San José','78436','citizen','$2y$12$vk05WrbiwdwbKoRbomSF4ucgLA8XYIY6021td1vwcAJqvQqI4bVom'),
('ciudadano10','Luis Alberto Ponce','luis.ponce@example.test','Saint Louis','444-000-0110','1991-02-19','POLA910219HSPNLS08','Calle Reforma 161, Moderna','78233','citizen','$2y$12$qtBtTA/4bbWp8cSEUAjeeuwa0sf6FUQTQewuKpD.sx4SZDjadIsCC'),
('ciudadano11','Sofía Hernández','sofia.hernandez@example.test','Cerro de San Pablo','444-000-0111','1998-10-11','HERS981011MSPRRF01','Priv. Nogales 34, Las Fuentes','78411','citizen','$2y$12$v4gVch5TabsasBCsvwbe3ehBrHZRQ/eU3zT/LII9IM6f5xeRHbmTC'),
('ciudadano12','Raúl Jiménez','raul.jimenez@example.test','Soledade','444-000-0112','1978-07-25','JIMR780725HSPMNL02','Calle Hidalgo 904, Centro','78430','citizen','$2y$12$vO.3o9aL74qipkfpC3JP4.2d3VrBXg.Sbeu2FJdjqEmTiXiGpL1bG'),
('ciudadano13','Mónica Cervantes','monica.cervantes@example.test','Saint Louis','444-000-0113','1986-03-18','CEMM860318MSPRNN04','Av. Chapultepec 820, Colinas','78294','citizen','$2y$12$qQv8RD66678wO6/0aKQK0O/HO0IP1EoHBQGC4I.MpfXqlL8El0utC'),
('ciudadano14','Ernesto Aguilar','ernesto.aguilar@example.test','Soledade','444-000-0114','1983-06-09','AUER830609HSPGNR05','Calle Olivos 126, La Lomita','78435','citizen','$2y$12$1.ak1e31oHy1B0b/wDuOv.vXAudZzRaErN6OeSEnG6v4AaBL6LKsa'),
('ciudadano15','Natalia Romero','natalia.romero@example.test','Saint Louis','444-000-0115','1993-01-15','RORN930115MSPMMT07','Av. Muñoz 1760, Himno Nacional','78280','citizen','$2y$12$EJo.jtgQ7RxtGPcNAKxPruelZSxiZYXnNzYoLGUFwFFq/nW6pIIB6'),
('ciudadano16','Arturo Sánchez','arturo.sanchez@example.test','Cerro de San Pablo','444-000-0116','1976-09-24','SAAA760924HSPNRT03','Calle Encinos 300, Vista Hermosa','78412','citizen','$2y$12$4AI9nULiSndegQW9kGlQF.ag3U2WBhn/xU5gsjamQ46FNWZILtySC'),
('ciudadano17','Gabriela Martínez','gabriela.martinez@example.test','Saint Louis','444-000-0117','1989-05-31','MAGG890531MSPRBR08','Av. Carranza 2145, Tequis','78250','citizen','$2y$12$DmZEfDQRn6p.J192EMgiheg4I.G21VEc3JtNxTtU0ZT4os6.01bXO'),
('ciudadano18','Héctor Morales','hector.morales@example.test','Soledade','444-000-0118','1982-11-17','MORH821117HSPLCR09','Calle Mina 77, San Francisco','78438','citizen','$2y$12$btMOmQPEuV9BbOsqSaTruuOz84rapKwK4olfUuQCwnOr8nIuFW9uK'),
('ciudadano19','Valeria Ortiz','valeria.ortiz@example.test','Cerro de San Pablo','444-000-0119','1997-04-08','OIVV970408MSPRRL00','Priv. Mezquites 19, La Cañada','78416','citizen','$2y$12$1R9ay.BtWIdfHgAkCQWC2.xPydRK6xEnuZ.Ij7qt3MWrx8CI6CxnW'),
('ciudadano20','Ricardo Navarro','ricardo.navarro@example.test','Saint Louis','444-000-0120','1985-08-05','NARR850805HSPLRC05','Av. Salvador Nava 2660, Tangamanga','78269','citizen','$2y$12$QH1hMesSW2/1CLamtlwCJ.EbU9ZCcY4VAy7hFRIFP6H5iD1/7BQXu');

INSERT INTO cuentas_servicio (usuario_id,cuenta,contrato,medidor,domicilio,colonia,municipio,codigo_postal,tipo_servicio,tarifa,fecha_alta,estatus,saldo,lectura_actual,fecha_lectura) VALUES
(1,'40010001','CTR-2026-00001','MTR-SA-2401','Av. de la República 145, Centro','Centro','Saint Louis','78000','Doméstico','Doméstica estándar','2012-02-02','Activo',357.45,1567.3,'2026-09-15'),
(2,'40010002','CTR-2026-00002','MTR-SO-2402','Calle Jacarandas 83, La Estación','San José','Soledade','78430','Doméstico','Doméstica estándar','2013-03-03','Activo',394.90,1684.6,'2026-09-15'),
(3,'40010003','CTR-2026-00003','MTR-CE-2403','Priv. del Lago 17, Los Pinos','Las Fuentes','Cerro de San Pablo','78415','Doméstico','Doméstica estándar','2014-04-04','Activo',432.35,1801.9,'2026-09-15'),
(4,'40010004','CTR-2026-00004','MTR-SA-2404','Av. Universidad 1208, Jardines','Tequis','Saint Louis','78290','Doméstico','Doméstica estándar','2015-05-05','Activo',469.80,1919.2,'2026-09-15'),
(5,'40010005','CTR-2026-00005','MTR-SO-2405','Calle Cedros 214, San Felipe','San Felipe','Soledade','78433','Doméstico','Doméstica estándar','2016-06-06','Activo',507.25,2036.5,'2026-09-15'),
(6,'40010006','CTR-2026-00006','MTR-SA-2406','Av. Industrias 2310, Valle Dorado','Centro','Saint Louis','78399','Doméstico','Doméstica estándar','2017-07-07','Activo',544.70,2153.8,'2026-09-15'),
(7,'40010007','CTR-2026-00007','MTR-CE-2407','Camino Real 442, El Mirador','Las Fuentes','Cerro de San Pablo','78418','Doméstico','Doméstica estándar','2018-08-08','Activo',582.15,2271.1,'2026-09-15'),
(8,'40010008','CTR-2026-00008','MTR-SA-2408','Calle Naranjos 55, Lomas','Lomas','Saint Louis','78210','Doméstico','Doméstica estándar','2019-09-09','Activo',619.60,2388.4,'2026-09-15'),
(9,'40010009','CTR-2026-00009','MTR-SO-2409','Av. Libertad 708, San José','San Felipe','Soledade','78436','Doméstico','Doméstica estándar','2020-10-10','Activo',657.05,2505.7,'2026-09-15'),
(10,'40010010','CTR-2026-00010','MTR-SA-2410','Calle Reforma 161, Moderna','Tangamanga','Saint Louis','78233','Doméstico','Doméstica estándar','2021-11-11','Activo',694.50,2623.0,'2026-09-15'),
(11,'40010011','CTR-2026-00011','MTR-CE-2411','Priv. Nogales 34, Las Fuentes','Las Fuentes','Cerro de San Pablo','78411','Doméstico','Doméstica estándar','2022-12-12','Activo',731.95,2740.3,'2026-09-15'),
(12,'40010012','CTR-2026-00012','MTR-SO-2412','Calle Hidalgo 904, Centro','San Francisco','Soledade','78430','Doméstico','Doméstica estándar','2011-01-13','Activo',769.40,2857.6,'2026-09-15'),
(13,'40010013','CTR-2026-00013','MTR-SA-2413','Av. Chapultepec 820, Colinas','Lomas','Saint Louis','78294','Doméstico','Doméstica estándar','2012-02-14','Activo',806.85,2974.9,'2026-09-15'),
(14,'40010014','CTR-2026-00014','MTR-SO-2414','Calle Olivos 126, La Lomita','San José','Soledade','78435','Doméstico','Doméstica estándar','2013-03-15','Activo',844.30,3092.2,'2026-09-15'),
(15,'40010015','CTR-2026-00015','MTR-SA-2415','Av. Muñoz 1760, Himno Nacional','Tangamanga','Saint Louis','78280','Doméstico','Doméstica estándar','2014-04-16','Activo',881.75,3209.5,'2026-09-15'),
(16,'40010016','CTR-2026-00016','MTR-CE-2416','Calle Encinos 300, Vista Hermosa','Vista Hermosa','Cerro de San Pablo','78412','Doméstico','Doméstica estándar','2015-05-17','Activo',919.20,3326.8,'2026-09-15'),
(17,'40010017','CTR-2026-00017','MTR-SA-2417','Av. Carranza 2145, Tequis','Jardines','Saint Louis','78250','Doméstico','Doméstica estándar','2016-06-18','Activo',956.65,3444.1,'2026-09-15'),
(18,'40010018','CTR-2026-00018','MTR-SO-2418','Calle Mina 77, San Francisco','San José','Soledade','78438','Doméstico','Doméstica estándar','2017-07-19','Activo',994.10,3561.4,'2026-09-15'),
(19,'40010019','CTR-2026-00019','MTR-CE-2419','Priv. Mezquites 19, La Cañada','Las Fuentes','Cerro de San Pablo','78416','Doméstico','Doméstica estándar','2018-08-20','Activo',1031.55,3678.7,'2026-09-15'),
(20,'40010020','CTR-2026-00020','MTR-SA-2420','Av. Salvador Nava 2660, Tangamanga','Tangamanga','Saint Louis','78269','Doméstico','Doméstica estándar','2019-09-21','Activo',1069.00,3796.0,'2026-09-15'),
(NULL,'40010021','CTR-2026-00021','MTR-NEW-2421','Av. Nueva Cuenta 121','Centro','Saint Louis','78000','Doméstico','Doméstica estándar','2024-01-15','Activo',691.50,3149.2,'2026-09-15'),
(NULL,'40010022','CTR-2026-00022','MTR-NEW-2422','Av. Nueva Cuenta 122','San Felipe','Soledade','78000','Doméstico','Doméstica estándar','2024-01-15','Activo',703.00,3194.4,'2026-09-15'),
(NULL,'40010023','CTR-2026-00023','MTR-NEW-2423','Av. Nueva Cuenta 123','Los Pinos','Cerro de San Pablo','78000','Doméstico','Doméstica estándar','2024-01-15','Activo',714.50,3239.6,'2026-09-15'),
(NULL,'40010024','CTR-2026-00024','MTR-NEW-2424','Av. Nueva Cuenta 124','Centro','Saint Louis','78000','Doméstico','Doméstica estándar','2024-01-15','Activo',726.00,3284.8,'2026-09-15'),
(NULL,'40010025','CTR-2026-00025','MTR-NEW-2425','Av. Nueva Cuenta 125','San Felipe','Soledade','78000','Doméstico','Doméstica estándar','2024-01-15','Activo',737.50,3330.0,'2026-09-15');

INSERT INTO expedientes (usuario_id,cuenta_id,folio,tipo,fecha,estatus,municipio,canal,ultima_actualizacion,observaciones) VALUES
(1,1,'EXP-2026-00421','Actualización de datos','2026-03-02','En revisión','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(2,2,'EXP-2026-00422','Aclaración de consumo','2026-04-03','Concluido','Soledade','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(3,3,'EXP-2026-00423','Constancia de servicio','2026-05-04','En validación','Cerro de San Pablo','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(4,4,'EXP-2026-00424','Cambio de titular','2026-06-05','Atendido','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(5,5,'EXP-2026-00425','Revisión de lectura','2026-07-06','En revisión','Soledade','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(6,6,'EXP-2026-00426','Actualización de datos','2026-08-07','Concluido','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(7,7,'EXP-2026-00427','Aclaración de consumo','2026-02-08','En validación','Cerro de San Pablo','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(8,8,'EXP-2026-00428','Constancia de servicio','2026-03-09','Atendido','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(9,9,'EXP-2026-00429','Cambio de titular','2026-04-10','En revisión','Soledade','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(10,10,'EXP-2026-00430','Revisión de lectura','2026-05-11','Concluido','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(11,11,'EXP-2026-00431','Actualización de datos','2026-06-12','En validación','Cerro de San Pablo','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(12,12,'EXP-2026-00432','Aclaración de consumo','2026-07-13','Atendido','Soledade','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(13,13,'EXP-2026-00433','Constancia de servicio','2026-08-14','En revisión','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(14,14,'EXP-2026-00434','Cambio de titular','2026-02-15','Concluido','Soledade','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(15,15,'EXP-2026-00435','Revisión de lectura','2026-03-16','En validación','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(16,16,'EXP-2026-00436','Actualización de datos','2026-04-17','Atendido','Cerro de San Pablo','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(17,17,'EXP-2026-00437','Aclaración de consumo','2026-05-18','En revisión','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(18,18,'EXP-2026-00438','Constancia de servicio','2026-06-19','Concluido','Soledade','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(19,19,'EXP-2026-00439','Cambio de titular','2026-07-20','En validación','Cerro de San Pablo','Portal ciudadano','2026-09-18','Documentación integrada al expediente.'),
(20,20,'EXP-2026-00440','Revisión de lectura','2026-08-21','Atendido','Saint Louis','Portal ciudadano','2026-09-18','Documentación integrada al expediente.');

ALTER TABLE expedientes
  ADD COLUMN IF NOT EXISTS referencia_validacion VARCHAR(120) NULL AFTER observaciones;
UPDATE expedientes
SET referencia_validacion=CONCAT('INT-EXP-', LPAD(id,5,'0'))
WHERE referencia_validacion IS NULL OR referencia_validacion='';
UPDATE expedientes
SET referencia_validacion='UPSLP_CNOIV-NOT-YOURS-03'
WHERE id=2;

INSERT INTO documentos (usuario_id,nombre,tipo,fecha) VALUES
(1,'constancia_servicio_00421.pdf','Constancia de servicio','2026-09-05'),
(1,'constancia_no_adeudo_00421.pdf','Constancia de no adeudo','2026-09-05'),
(1,'constancia_titularidad_00421.pdf','Constancia de titularidad','2026-09-05'),
(1,'acuse_actualizacion_00421.pdf','Acuse','2026-09-02');

INSERT INTO facturas (cuenta_id,folio,periodo,fecha_emision,fecha_limite,lectura_anterior,lectura_actual,consumo_m3,cargo_agua,cargo_saneamiento,otros,total,saldo,estado) VALUES
(1,'FAC-202604-00001','2026-04','2026-04-05','2026-04-22',928.0,943.8,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(1,'FAC-202605-00001','2026-05','2026-05-05','2026-05-22',943.8,961.2,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(1,'FAC-202606-00001','2026-06','2026-06-05','2026-06-22',961.2,980.3,19.1,385.66,69.42,18.50,473.58,0.00,'Pagada'),
(1,'FAC-202607-00001','2026-07','2026-07-05','2026-07-22',980.3,1001.2,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(1,'FAC-202608-00001','2026-08','2026-08-05','2026-08-22',1001.2,1013.5,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(1,'FAC-202609-00001','2026-09','2026-09-05','2026-09-25',1013.5,1027.6,14.1,322.66,58.08,18.50,399.24,399.24,'Pendiente'),
(2,'FAC-202604-00002','2026-04','2026-04-05','2026-04-22',956.0,974.3,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(2,'FAC-202605-00002','2026-05','2026-05-05','2026-05-22',974.3,994.3,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(2,'FAC-202606-00002','2026-06','2026-06-05','2026-06-22',994.3,1005.8,11.5,289.90,52.18,18.50,360.58,0.00,'Pagada'),
(2,'FAC-202607-00002','2026-07','2026-07-05','2026-07-22',1005.8,1019.0,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(2,'FAC-202608-00002','2026-08','2026-08-05','2026-08-22',1019.0,1033.9,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(2,'FAC-202609-00002','2026-09','2026-09-05','2026-09-25',1033.9,1050.5,16.6,354.16,63.75,18.50,436.41,436.41,'Pendiente'),
(3,'FAC-202604-00003','2026-04','2026-04-05','2026-04-22',984.0,1004.9,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(3,'FAC-202605-00003','2026-05','2026-05-05','2026-05-22',1004.9,1017.2,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(3,'FAC-202606-00003','2026-06','2026-06-05','2026-06-22',1017.2,1031.3,14.1,322.66,58.08,18.50,399.24,0.00,'Pagada'),
(3,'FAC-202607-00003','2026-07','2026-07-05','2026-07-22',1031.3,1047.1,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(3,'FAC-202608-00003','2026-08','2026-08-05','2026-08-22',1047.1,1064.5,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(3,'FAC-202609-00003','2026-09','2026-09-05','2026-09-25',1064.5,1083.6,19.1,385.66,69.42,18.50,473.58,473.58,'Pendiente'),
(4,'FAC-202604-00004','2026-04','2026-04-05','2026-04-22',1012.0,1025.2,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(4,'FAC-202605-00004','2026-05','2026-05-05','2026-05-22',1025.2,1040.1,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(4,'FAC-202606-00004','2026-06','2026-06-05','2026-06-22',1040.1,1056.7,16.6,354.16,63.75,18.50,436.41,0.00,'Pagada'),
(4,'FAC-202607-00004','2026-07','2026-07-05','2026-07-22',1056.7,1075.0,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(4,'FAC-202608-00004','2026-08','2026-08-05','2026-08-22',1075.0,1095.0,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(4,'FAC-202609-00004','2026-09','2026-09-05','2026-09-25',1095.0,1106.5,11.5,289.90,52.18,18.50,360.58,360.58,'Pendiente'),
(5,'FAC-202604-00005','2026-04','2026-04-05','2026-04-22',1040.0,1055.8,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(5,'FAC-202605-00005','2026-05','2026-05-05','2026-05-22',1055.8,1073.2,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(5,'FAC-202606-00005','2026-06','2026-06-05','2026-06-22',1073.2,1092.3,19.1,385.66,69.42,18.50,473.58,0.00,'Pagada'),
(5,'FAC-202607-00005','2026-07','2026-07-05','2026-07-22',1092.3,1113.2,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(5,'FAC-202608-00005','2026-08','2026-08-05','2026-08-22',1113.2,1125.5,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(5,'FAC-202609-00005','2026-09','2026-09-05','2026-09-25',1125.5,1139.6,14.1,322.66,58.08,18.50,399.24,399.24,'Pendiente'),
(6,'FAC-202604-00006','2026-04','2026-04-05','2026-04-22',1068.0,1086.3,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(6,'FAC-202605-00006','2026-05','2026-05-05','2026-05-22',1086.3,1106.3,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(6,'FAC-202606-00006','2026-06','2026-06-05','2026-06-22',1106.3,1117.8,11.5,289.90,52.18,18.50,360.58,0.00,'Pagada'),
(6,'FAC-202607-00006','2026-07','2026-07-05','2026-07-22',1117.8,1131.0,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(6,'FAC-202608-00006','2026-08','2026-08-05','2026-08-22',1131.0,1145.9,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(6,'FAC-202609-00006','2026-09','2026-09-05','2026-09-25',1145.9,1162.5,16.6,354.16,63.75,18.50,436.41,436.41,'Pendiente'),
(7,'FAC-202604-00007','2026-04','2026-04-05','2026-04-22',1096.0,1116.9,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(7,'FAC-202605-00007','2026-05','2026-05-05','2026-05-22',1116.9,1129.2,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(7,'FAC-202606-00007','2026-06','2026-06-05','2026-06-22',1129.2,1143.3,14.1,322.66,58.08,18.50,399.24,0.00,'Pagada'),
(7,'FAC-202607-00007','2026-07','2026-07-05','2026-07-22',1143.3,1159.1,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(7,'FAC-202608-00007','2026-08','2026-08-05','2026-08-22',1159.1,1176.5,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(7,'FAC-202609-00007','2026-09','2026-09-05','2026-09-25',1176.5,1195.6,19.1,385.66,69.42,18.50,473.58,473.58,'Pendiente'),
(8,'FAC-202604-00008','2026-04','2026-04-05','2026-04-22',1124.0,1137.2,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(8,'FAC-202605-00008','2026-05','2026-05-05','2026-05-22',1137.2,1152.1,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(8,'FAC-202606-00008','2026-06','2026-06-05','2026-06-22',1152.1,1168.7,16.6,354.16,63.75,18.50,436.41,0.00,'Pagada'),
(8,'FAC-202607-00008','2026-07','2026-07-05','2026-07-22',1168.7,1187.0,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(8,'FAC-202608-00008','2026-08','2026-08-05','2026-08-22',1187.0,1207.0,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(8,'FAC-202609-00008','2026-09','2026-09-05','2026-09-25',1207.0,1218.5,11.5,289.90,52.18,18.50,360.58,360.58,'Pendiente'),
(9,'FAC-202604-00009','2026-04','2026-04-05','2026-04-22',1152.0,1167.8,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(9,'FAC-202605-00009','2026-05','2026-05-05','2026-05-22',1167.8,1185.2,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(9,'FAC-202606-00009','2026-06','2026-06-05','2026-06-22',1185.2,1204.3,19.1,385.66,69.42,18.50,473.58,0.00,'Pagada'),
(9,'FAC-202607-00009','2026-07','2026-07-05','2026-07-22',1204.3,1225.2,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(9,'FAC-202608-00009','2026-08','2026-08-05','2026-08-22',1225.2,1237.5,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(9,'FAC-202609-00009','2026-09','2026-09-05','2026-09-25',1237.5,1251.6,14.1,322.66,58.08,18.50,399.24,399.24,'Pendiente'),
(10,'FAC-202604-00010','2026-04','2026-04-05','2026-04-22',1180.0,1198.3,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(10,'FAC-202605-00010','2026-05','2026-05-05','2026-05-22',1198.3,1218.3,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(10,'FAC-202606-00010','2026-06','2026-06-05','2026-06-22',1218.3,1229.8,11.5,289.90,52.18,18.50,360.58,0.00,'Pagada'),
(10,'FAC-202607-00010','2026-07','2026-07-05','2026-07-22',1229.8,1243.0,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(10,'FAC-202608-00010','2026-08','2026-08-05','2026-08-22',1243.0,1257.9,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(10,'FAC-202609-00010','2026-09','2026-09-05','2026-09-25',1257.9,1274.5,16.6,354.16,63.75,18.50,436.41,436.41,'Pendiente'),
(11,'FAC-202604-00011','2026-04','2026-04-05','2026-04-22',1208.0,1228.9,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(11,'FAC-202605-00011','2026-05','2026-05-05','2026-05-22',1228.9,1241.2,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(11,'FAC-202606-00011','2026-06','2026-06-05','2026-06-22',1241.2,1255.3,14.1,322.66,58.08,18.50,399.24,0.00,'Pagada'),
(11,'FAC-202607-00011','2026-07','2026-07-05','2026-07-22',1255.3,1271.1,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(11,'FAC-202608-00011','2026-08','2026-08-05','2026-08-22',1271.1,1288.5,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(11,'FAC-202609-00011','2026-09','2026-09-05','2026-09-25',1288.5,1307.6,19.1,385.66,69.42,18.50,473.58,473.58,'Pendiente'),
(12,'FAC-202604-00012','2026-04','2026-04-05','2026-04-22',1236.0,1249.2,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(12,'FAC-202605-00012','2026-05','2026-05-05','2026-05-22',1249.2,1264.1,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(12,'FAC-202606-00012','2026-06','2026-06-05','2026-06-22',1264.1,1280.7,16.6,354.16,63.75,18.50,436.41,0.00,'Pagada'),
(12,'FAC-202607-00012','2026-07','2026-07-05','2026-07-22',1280.7,1299.0,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(12,'FAC-202608-00012','2026-08','2026-08-05','2026-08-22',1299.0,1319.0,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(12,'FAC-202609-00012','2026-09','2026-09-05','2026-09-25',1319.0,1330.5,11.5,289.90,52.18,18.50,360.58,360.58,'Pendiente'),
(13,'FAC-202604-00013','2026-04','2026-04-05','2026-04-22',1264.0,1279.8,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(13,'FAC-202605-00013','2026-05','2026-05-05','2026-05-22',1279.8,1297.2,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(13,'FAC-202606-00013','2026-06','2026-06-05','2026-06-22',1297.2,1316.3,19.1,385.66,69.42,18.50,473.58,0.00,'Pagada'),
(13,'FAC-202607-00013','2026-07','2026-07-05','2026-07-22',1316.3,1337.2,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(13,'FAC-202608-00013','2026-08','2026-08-05','2026-08-22',1337.2,1349.5,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(13,'FAC-202609-00013','2026-09','2026-09-05','2026-09-25',1349.5,1363.6,14.1,322.66,58.08,18.50,399.24,399.24,'Pendiente'),
(14,'FAC-202604-00014','2026-04','2026-04-05','2026-04-22',1292.0,1310.3,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(14,'FAC-202605-00014','2026-05','2026-05-05','2026-05-22',1310.3,1330.3,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(14,'FAC-202606-00014','2026-06','2026-06-05','2026-06-22',1330.3,1341.8,11.5,289.90,52.18,18.50,360.58,0.00,'Pagada'),
(14,'FAC-202607-00014','2026-07','2026-07-05','2026-07-22',1341.8,1355.0,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(14,'FAC-202608-00014','2026-08','2026-08-05','2026-08-22',1355.0,1369.9,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(14,'FAC-202609-00014','2026-09','2026-09-05','2026-09-25',1369.9,1386.5,16.6,354.16,63.75,18.50,436.41,436.41,'Pendiente'),
(15,'FAC-202604-00015','2026-04','2026-04-05','2026-04-22',1320.0,1340.9,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(15,'FAC-202605-00015','2026-05','2026-05-05','2026-05-22',1340.9,1353.2,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(15,'FAC-202606-00015','2026-06','2026-06-05','2026-06-22',1353.2,1367.3,14.1,322.66,58.08,18.50,399.24,0.00,'Pagada'),
(15,'FAC-202607-00015','2026-07','2026-07-05','2026-07-22',1367.3,1383.1,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(15,'FAC-202608-00015','2026-08','2026-08-05','2026-08-22',1383.1,1400.5,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(15,'FAC-202609-00015','2026-09','2026-09-05','2026-09-25',1400.5,1419.6,19.1,385.66,69.42,18.50,473.58,473.58,'Pendiente'),
(16,'FAC-202604-00016','2026-04','2026-04-05','2026-04-22',1348.0,1361.2,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(16,'FAC-202605-00016','2026-05','2026-05-05','2026-05-22',1361.2,1376.1,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(16,'FAC-202606-00016','2026-06','2026-06-05','2026-06-22',1376.1,1392.7,16.6,354.16,63.75,18.50,436.41,0.00,'Pagada'),
(16,'FAC-202607-00016','2026-07','2026-07-05','2026-07-22',1392.7,1411.0,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(16,'FAC-202608-00016','2026-08','2026-08-05','2026-08-22',1411.0,1431.0,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(16,'FAC-202609-00016','2026-09','2026-09-05','2026-09-25',1431.0,1442.5,11.5,289.90,52.18,18.50,360.58,360.58,'Pendiente'),
(17,'FAC-202604-00017','2026-04','2026-04-05','2026-04-22',1376.0,1391.8,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(17,'FAC-202605-00017','2026-05','2026-05-05','2026-05-22',1391.8,1409.2,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(17,'FAC-202606-00017','2026-06','2026-06-05','2026-06-22',1409.2,1428.3,19.1,385.66,69.42,18.50,473.58,0.00,'Pagada'),
(17,'FAC-202607-00017','2026-07','2026-07-05','2026-07-22',1428.3,1449.2,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(17,'FAC-202608-00017','2026-08','2026-08-05','2026-08-22',1449.2,1461.5,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(17,'FAC-202609-00017','2026-09','2026-09-05','2026-09-25',1461.5,1475.6,14.1,322.66,58.08,18.50,399.24,399.24,'Pendiente'),
(18,'FAC-202604-00018','2026-04','2026-04-05','2026-04-22',1404.0,1422.3,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(18,'FAC-202605-00018','2026-05','2026-05-05','2026-05-22',1422.3,1442.3,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(18,'FAC-202606-00018','2026-06','2026-06-05','2026-06-22',1442.3,1453.8,11.5,289.90,52.18,18.50,360.58,0.00,'Pagada'),
(18,'FAC-202607-00018','2026-07','2026-07-05','2026-07-22',1453.8,1467.0,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(18,'FAC-202608-00018','2026-08','2026-08-05','2026-08-22',1467.0,1481.9,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(18,'FAC-202609-00018','2026-09','2026-09-05','2026-09-25',1481.9,1498.5,16.6,354.16,63.75,18.50,436.41,436.41,'Pendiente'),
(19,'FAC-202604-00019','2026-04','2026-04-05','2026-04-22',1432.0,1452.9,20.9,408.34,73.50,0.00,481.84,0.00,'Pagada'),
(19,'FAC-202605-00019','2026-05','2026-05-05','2026-05-22',1452.9,1465.2,12.3,299.98,54.00,0.00,353.98,0.00,'Pagada'),
(19,'FAC-202606-00019','2026-06','2026-06-05','2026-06-22',1465.2,1479.3,14.1,322.66,58.08,18.50,399.24,0.00,'Pagada'),
(19,'FAC-202607-00019','2026-07','2026-07-05','2026-07-22',1479.3,1495.1,15.8,344.08,61.93,0.00,406.01,0.00,'Pagada'),
(19,'FAC-202608-00019','2026-08','2026-08-05','2026-08-22',1495.1,1512.5,17.4,364.24,65.56,0.00,429.80,0.00,'Pagada'),
(19,'FAC-202609-00019','2026-09','2026-09-05','2026-09-25',1512.5,1531.6,19.1,385.66,69.42,18.50,473.58,473.58,'Pendiente'),
(20,'FAC-202604-00020','2026-04','2026-04-05','2026-04-22',1460.0,1473.2,13.2,311.32,56.04,0.00,367.36,0.00,'Pagada'),
(20,'FAC-202605-00020','2026-05','2026-05-05','2026-05-22',1473.2,1488.1,14.9,332.74,59.89,0.00,392.63,0.00,'Pagada'),
(20,'FAC-202606-00020','2026-06','2026-06-05','2026-06-22',1488.1,1504.7,16.6,354.16,63.75,18.50,436.41,0.00,'Pagada'),
(20,'FAC-202607-00020','2026-07','2026-07-05','2026-07-22',1504.7,1523.0,18.3,375.58,67.60,0.00,443.18,0.00,'Pagada'),
(20,'FAC-202608-00020','2026-08','2026-08-05','2026-08-22',1523.0,1543.0,20.0,397.00,71.46,0.00,468.46,0.00,'Pagada'),
(20,'FAC-202609-00020','2026-09','2026-09-05','2026-09-25',1543.0,1554.5,11.5,289.90,52.18,18.50,360.58,360.58,'Pendiente');

INSERT INTO consumos (cuenta_id,periodo,consumo_m3,lectura,promedio_zona_m3) VALUES
(1,'2026-04',15.8,943.8,15.4),
(1,'2026-05',17.4,961.2,15.4),
(1,'2026-06',19.1,980.3,15.4),
(1,'2026-07',20.9,1001.2,15.4),
(1,'2026-08',12.3,1013.5,15.4),
(1,'2026-09',14.1,1027.6,15.4),
(2,'2026-04',18.3,974.3,16.0),
(2,'2026-05',20.0,994.3,16.0),
(2,'2026-06',11.5,1005.8,16.0),
(2,'2026-07',13.2,1019.0,16.0),
(2,'2026-08',14.9,1033.9,16.0),
(2,'2026-09',16.6,1050.5,16.0),
(3,'2026-04',20.9,1004.9,16.6),
(3,'2026-05',12.3,1017.2,16.6),
(3,'2026-06',14.1,1031.3,16.6),
(3,'2026-07',15.8,1047.1,16.6),
(3,'2026-08',17.4,1064.5,16.6),
(3,'2026-09',19.1,1083.6,16.6),
(4,'2026-04',13.2,1025.2,17.2),
(4,'2026-05',14.9,1040.1,17.2),
(4,'2026-06',16.6,1056.7,17.2),
(4,'2026-07',18.3,1075.0,17.2),
(4,'2026-08',20.0,1095.0,17.2),
(4,'2026-09',11.5,1106.5,17.2),
(5,'2026-04',15.8,1055.8,14.8),
(5,'2026-05',17.4,1073.2,14.8),
(5,'2026-06',19.1,1092.3,14.8),
(5,'2026-07',20.9,1113.2,14.8),
(5,'2026-08',12.3,1125.5,14.8),
(5,'2026-09',14.1,1139.6,14.8),
(6,'2026-04',18.3,1086.3,15.4),
(6,'2026-05',20.0,1106.3,15.4),
(6,'2026-06',11.5,1117.8,15.4),
(6,'2026-07',13.2,1131.0,15.4),
(6,'2026-08',14.9,1145.9,15.4),
(6,'2026-09',16.6,1162.5,15.4),
(7,'2026-04',20.9,1116.9,16.0),
(7,'2026-05',12.3,1129.2,16.0),
(7,'2026-06',14.1,1143.3,16.0),
(7,'2026-07',15.8,1159.1,16.0),
(7,'2026-08',17.4,1176.5,16.0),
(7,'2026-09',19.1,1195.6,16.0),
(8,'2026-04',13.2,1137.2,16.6),
(8,'2026-05',14.9,1152.1,16.6),
(8,'2026-06',16.6,1168.7,16.6),
(8,'2026-07',18.3,1187.0,16.6),
(8,'2026-08',20.0,1207.0,16.6),
(8,'2026-09',11.5,1218.5,16.6),
(9,'2026-04',15.8,1167.8,17.2),
(9,'2026-05',17.4,1185.2,17.2),
(9,'2026-06',19.1,1204.3,17.2),
(9,'2026-07',20.9,1225.2,17.2),
(9,'2026-08',12.3,1237.5,17.2),
(9,'2026-09',14.1,1251.6,17.2),
(10,'2026-04',18.3,1198.3,14.8),
(10,'2026-05',20.0,1218.3,14.8),
(10,'2026-06',11.5,1229.8,14.8),
(10,'2026-07',13.2,1243.0,14.8),
(10,'2026-08',14.9,1257.9,14.8),
(10,'2026-09',16.6,1274.5,14.8),
(11,'2026-04',20.9,1228.9,15.4),
(11,'2026-05',12.3,1241.2,15.4),
(11,'2026-06',14.1,1255.3,15.4),
(11,'2026-07',15.8,1271.1,15.4),
(11,'2026-08',17.4,1288.5,15.4),
(11,'2026-09',19.1,1307.6,15.4),
(12,'2026-04',13.2,1249.2,16.0),
(12,'2026-05',14.9,1264.1,16.0),
(12,'2026-06',16.6,1280.7,16.0),
(12,'2026-07',18.3,1299.0,16.0),
(12,'2026-08',20.0,1319.0,16.0),
(12,'2026-09',11.5,1330.5,16.0),
(13,'2026-04',15.8,1279.8,16.6),
(13,'2026-05',17.4,1297.2,16.6),
(13,'2026-06',19.1,1316.3,16.6),
(13,'2026-07',20.9,1337.2,16.6),
(13,'2026-08',12.3,1349.5,16.6),
(13,'2026-09',14.1,1363.6,16.6),
(14,'2026-04',18.3,1310.3,17.2),
(14,'2026-05',20.0,1330.3,17.2),
(14,'2026-06',11.5,1341.8,17.2),
(14,'2026-07',13.2,1355.0,17.2),
(14,'2026-08',14.9,1369.9,17.2),
(14,'2026-09',16.6,1386.5,17.2),
(15,'2026-04',20.9,1340.9,14.8),
(15,'2026-05',12.3,1353.2,14.8),
(15,'2026-06',14.1,1367.3,14.8),
(15,'2026-07',15.8,1383.1,14.8),
(15,'2026-08',17.4,1400.5,14.8),
(15,'2026-09',19.1,1419.6,14.8),
(16,'2026-04',13.2,1361.2,15.4),
(16,'2026-05',14.9,1376.1,15.4),
(16,'2026-06',16.6,1392.7,15.4),
(16,'2026-07',18.3,1411.0,15.4),
(16,'2026-08',20.0,1431.0,15.4),
(16,'2026-09',11.5,1442.5,15.4),
(17,'2026-04',15.8,1391.8,16.0),
(17,'2026-05',17.4,1409.2,16.0),
(17,'2026-06',19.1,1428.3,16.0),
(17,'2026-07',20.9,1449.2,16.0),
(17,'2026-08',12.3,1461.5,16.0),
(17,'2026-09',14.1,1475.6,16.0),
(18,'2026-04',18.3,1422.3,16.6),
(18,'2026-05',20.0,1442.3,16.6),
(18,'2026-06',11.5,1453.8,16.6),
(18,'2026-07',13.2,1467.0,16.6),
(18,'2026-08',14.9,1481.9,16.6),
(18,'2026-09',16.6,1498.5,16.6),
(19,'2026-04',20.9,1452.9,17.2),
(19,'2026-05',12.3,1465.2,17.2),
(19,'2026-06',14.1,1479.3,17.2),
(19,'2026-07',15.8,1495.1,17.2),
(19,'2026-08',17.4,1512.5,17.2),
(19,'2026-09',19.1,1531.6,17.2),
(20,'2026-04',13.2,1473.2,14.8),
(20,'2026-05',14.9,1488.1,14.8),
(20,'2026-06',16.6,1504.7,14.8),
(20,'2026-07',18.3,1523.0,14.8),
(20,'2026-08',20.0,1543.0,14.8),
(20,'2026-09',11.5,1554.5,14.8);

INSERT INTO pagos (usuario_id,cuenta_id,factura_id,referencia,monto,metodo,ultimos4,creado,estado) VALUES
(1,1,1,'PAY-202604-0001',406.01,'SPEI','4101','2026-04-22 10:21:00','Aplicado'),
(1,1,2,'PAY-202605-0001',429.80,'Domiciliación','4101','2026-05-22 10:22:00','Aplicado'),
(1,1,3,'PAY-202606-0001',473.58,'Tarjeta','4101','2026-06-22 10:23:00','Aplicado'),
(1,1,4,'PAY-202607-0001',481.84,'SPEI','4101','2026-07-22 10:24:00','Aplicado'),
(1,1,5,'PAY-202608-0001',353.98,'Domiciliación','4101','2026-08-22 10:25:00','Aplicado'),
(2,2,7,'PAY-202604-0002',443.18,'SPEI','4102','2026-04-22 10:21:00','Aplicado'),
(2,2,8,'PAY-202605-0002',468.46,'Domiciliación','4102','2026-05-22 10:22:00','Aplicado'),
(2,2,9,'PAY-202606-0002',360.58,'Tarjeta','4102','2026-06-22 10:23:00','Aplicado'),
(2,2,10,'PAY-202607-0002',367.36,'SPEI','4102','2026-07-22 10:24:00','Aplicado'),
(2,2,11,'PAY-202608-0002',392.63,'Domiciliación','4102','2026-08-22 10:25:00','Aplicado'),
(3,3,13,'PAY-202604-0003',481.84,'SPEI','4103','2026-04-22 10:21:00','Aplicado'),
(3,3,14,'PAY-202605-0003',353.98,'Domiciliación','4103','2026-05-22 10:22:00','Aplicado'),
(3,3,15,'PAY-202606-0003',399.24,'Tarjeta','4103','2026-06-22 10:23:00','Aplicado'),
(3,3,16,'PAY-202607-0003',406.01,'SPEI','4103','2026-07-22 10:24:00','Aplicado'),
(3,3,17,'PAY-202608-0003',429.80,'Domiciliación','4103','2026-08-22 10:25:00','Aplicado'),
(4,4,19,'PAY-202604-0004',367.36,'SPEI','4104','2026-04-22 10:21:00','Aplicado'),
(4,4,20,'PAY-202605-0004',392.63,'Domiciliación','4104','2026-05-22 10:22:00','Aplicado'),
(4,4,21,'PAY-202606-0004',436.41,'Tarjeta','4104','2026-06-22 10:23:00','Aplicado'),
(4,4,22,'PAY-202607-0004',443.18,'SPEI','4104','2026-07-22 10:24:00','Aplicado'),
(4,4,23,'PAY-202608-0004',468.46,'Domiciliación','4104','2026-08-22 10:25:00','Aplicado'),
(5,5,25,'PAY-202604-0005',406.01,'SPEI','4105','2026-04-22 10:21:00','Aplicado'),
(5,5,26,'PAY-202605-0005',429.80,'Domiciliación','4105','2026-05-22 10:22:00','Aplicado'),
(5,5,27,'PAY-202606-0005',473.58,'Tarjeta','4105','2026-06-22 10:23:00','Aplicado'),
(5,5,28,'PAY-202607-0005',481.84,'SPEI','4105','2026-07-22 10:24:00','Aplicado'),
(5,5,29,'PAY-202608-0005',353.98,'Domiciliación','4105','2026-08-22 10:25:00','Aplicado'),
(6,6,31,'PAY-202604-0006',443.18,'SPEI','4106','2026-04-22 10:21:00','Aplicado'),
(6,6,32,'PAY-202605-0006',468.46,'Domiciliación','4106','2026-05-22 10:22:00','Aplicado'),
(6,6,33,'PAY-202606-0006',360.58,'Tarjeta','4106','2026-06-22 10:23:00','Aplicado'),
(6,6,34,'PAY-202607-0006',367.36,'SPEI','4106','2026-07-22 10:24:00','Aplicado'),
(6,6,35,'PAY-202608-0006',392.63,'Domiciliación','4106','2026-08-22 10:25:00','Aplicado'),
(7,7,37,'PAY-202604-0007',481.84,'SPEI','4107','2026-04-22 10:21:00','Aplicado'),
(7,7,38,'PAY-202605-0007',353.98,'Domiciliación','4107','2026-05-22 10:22:00','Aplicado'),
(7,7,39,'PAY-202606-0007',399.24,'Tarjeta','4107','2026-06-22 10:23:00','Aplicado'),
(7,7,40,'PAY-202607-0007',406.01,'SPEI','4107','2026-07-22 10:24:00','Aplicado'),
(7,7,41,'PAY-202608-0007',429.80,'Domiciliación','4107','2026-08-22 10:25:00','Aplicado'),
(8,8,43,'PAY-202604-0008',367.36,'SPEI','4108','2026-04-22 10:21:00','Aplicado'),
(8,8,44,'PAY-202605-0008',392.63,'Domiciliación','4108','2026-05-22 10:22:00','Aplicado'),
(8,8,45,'PAY-202606-0008',436.41,'Tarjeta','4108','2026-06-22 10:23:00','Aplicado'),
(8,8,46,'PAY-202607-0008',443.18,'SPEI','4108','2026-07-22 10:24:00','Aplicado'),
(8,8,47,'PAY-202608-0008',468.46,'Domiciliación','4108','2026-08-22 10:25:00','Aplicado'),
(9,9,49,'PAY-202604-0009',406.01,'SPEI','4109','2026-04-22 10:21:00','Aplicado'),
(9,9,50,'PAY-202605-0009',429.80,'Domiciliación','4109','2026-05-22 10:22:00','Aplicado'),
(9,9,51,'PAY-202606-0009',473.58,'Tarjeta','4109','2026-06-22 10:23:00','Aplicado'),
(9,9,52,'PAY-202607-0009',481.84,'SPEI','4109','2026-07-22 10:24:00','Aplicado'),
(9,9,53,'PAY-202608-0009',353.98,'Domiciliación','4109','2026-08-22 10:25:00','Aplicado'),
(10,10,55,'PAY-202604-0010',443.18,'SPEI','4110','2026-04-22 10:21:00','Aplicado'),
(10,10,56,'PAY-202605-0010',468.46,'Domiciliación','4110','2026-05-22 10:22:00','Aplicado'),
(10,10,57,'PAY-202606-0010',360.58,'Tarjeta','4110','2026-06-22 10:23:00','Aplicado'),
(10,10,58,'PAY-202607-0010',367.36,'SPEI','4110','2026-07-22 10:24:00','Aplicado'),
(10,10,59,'PAY-202608-0010',392.63,'Domiciliación','4110','2026-08-22 10:25:00','Aplicado'),
(11,11,61,'PAY-202604-0011',481.84,'SPEI','4111','2026-04-22 10:21:00','Aplicado'),
(11,11,62,'PAY-202605-0011',353.98,'Domiciliación','4111','2026-05-22 10:22:00','Aplicado'),
(11,11,63,'PAY-202606-0011',399.24,'Tarjeta','4111','2026-06-22 10:23:00','Aplicado'),
(11,11,64,'PAY-202607-0011',406.01,'SPEI','4111','2026-07-22 10:24:00','Aplicado'),
(11,11,65,'PAY-202608-0011',429.80,'Domiciliación','4111','2026-08-22 10:25:00','Aplicado'),
(12,12,67,'PAY-202604-0012',367.36,'SPEI','4112','2026-04-22 10:21:00','Aplicado'),
(12,12,68,'PAY-202605-0012',392.63,'Domiciliación','4112','2026-05-22 10:22:00','Aplicado'),
(12,12,69,'PAY-202606-0012',436.41,'Tarjeta','4112','2026-06-22 10:23:00','Aplicado'),
(12,12,70,'PAY-202607-0012',443.18,'SPEI','4112','2026-07-22 10:24:00','Aplicado'),
(12,12,71,'PAY-202608-0012',468.46,'Domiciliación','4112','2026-08-22 10:25:00','Aplicado'),
(13,13,73,'PAY-202604-0013',406.01,'SPEI','4113','2026-04-22 10:21:00','Aplicado'),
(13,13,74,'PAY-202605-0013',429.80,'Domiciliación','4113','2026-05-22 10:22:00','Aplicado'),
(13,13,75,'PAY-202606-0013',473.58,'Tarjeta','4113','2026-06-22 10:23:00','Aplicado'),
(13,13,76,'PAY-202607-0013',481.84,'SPEI','4113','2026-07-22 10:24:00','Aplicado'),
(13,13,77,'PAY-202608-0013',353.98,'Domiciliación','4113','2026-08-22 10:25:00','Aplicado'),
(14,14,79,'PAY-202604-0014',443.18,'SPEI','4114','2026-04-22 10:21:00','Aplicado'),
(14,14,80,'PAY-202605-0014',468.46,'Domiciliación','4114','2026-05-22 10:22:00','Aplicado'),
(14,14,81,'PAY-202606-0014',360.58,'Tarjeta','4114','2026-06-22 10:23:00','Aplicado'),
(14,14,82,'PAY-202607-0014',367.36,'SPEI','4114','2026-07-22 10:24:00','Aplicado'),
(14,14,83,'PAY-202608-0014',392.63,'Domiciliación','4114','2026-08-22 10:25:00','Aplicado'),
(15,15,85,'PAY-202604-0015',481.84,'SPEI','4115','2026-04-22 10:21:00','Aplicado'),
(15,15,86,'PAY-202605-0015',353.98,'Domiciliación','4115','2026-05-22 10:22:00','Aplicado'),
(15,15,87,'PAY-202606-0015',399.24,'Tarjeta','4115','2026-06-22 10:23:00','Aplicado'),
(15,15,88,'PAY-202607-0015',406.01,'SPEI','4115','2026-07-22 10:24:00','Aplicado'),
(15,15,89,'PAY-202608-0015',429.80,'Domiciliación','4115','2026-08-22 10:25:00','Aplicado'),
(16,16,91,'PAY-202604-0016',367.36,'SPEI','4116','2026-04-22 10:21:00','Aplicado'),
(16,16,92,'PAY-202605-0016',392.63,'Domiciliación','4116','2026-05-22 10:22:00','Aplicado'),
(16,16,93,'PAY-202606-0016',436.41,'Tarjeta','4116','2026-06-22 10:23:00','Aplicado'),
(16,16,94,'PAY-202607-0016',443.18,'SPEI','4116','2026-07-22 10:24:00','Aplicado'),
(16,16,95,'PAY-202608-0016',468.46,'Domiciliación','4116','2026-08-22 10:25:00','Aplicado'),
(17,17,97,'PAY-202604-0017',406.01,'SPEI','4117','2026-04-22 10:21:00','Aplicado'),
(17,17,98,'PAY-202605-0017',429.80,'Domiciliación','4117','2026-05-22 10:22:00','Aplicado'),
(17,17,99,'PAY-202606-0017',473.58,'Tarjeta','4117','2026-06-22 10:23:00','Aplicado'),
(17,17,100,'PAY-202607-0017',481.84,'SPEI','4117','2026-07-22 10:24:00','Aplicado'),
(17,17,101,'PAY-202608-0017',353.98,'Domiciliación','4117','2026-08-22 10:25:00','Aplicado'),
(18,18,103,'PAY-202604-0018',443.18,'SPEI','4118','2026-04-22 10:21:00','Aplicado'),
(18,18,104,'PAY-202605-0018',468.46,'Domiciliación','4118','2026-05-22 10:22:00','Aplicado'),
(18,18,105,'PAY-202606-0018',360.58,'Tarjeta','4118','2026-06-22 10:23:00','Aplicado'),
(18,18,106,'PAY-202607-0018',367.36,'SPEI','4118','2026-07-22 10:24:00','Aplicado'),
(18,18,107,'PAY-202608-0018',392.63,'Domiciliación','4118','2026-08-22 10:25:00','Aplicado'),
(19,19,109,'PAY-202604-0019',481.84,'SPEI','4119','2026-04-22 10:21:00','Aplicado'),
(19,19,110,'PAY-202605-0019',353.98,'Domiciliación','4119','2026-05-22 10:22:00','Aplicado'),
(19,19,111,'PAY-202606-0019',399.24,'Tarjeta','4119','2026-06-22 10:23:00','Aplicado'),
(19,19,112,'PAY-202607-0019',406.01,'SPEI','4119','2026-07-22 10:24:00','Aplicado'),
(19,19,113,'PAY-202608-0019',429.80,'Domiciliación','4119','2026-08-22 10:25:00','Aplicado'),
(20,20,115,'PAY-202604-0020',367.36,'SPEI','4120','2026-04-22 10:21:00','Aplicado'),
(20,20,116,'PAY-202605-0020',392.63,'Domiciliación','4120','2026-05-22 10:22:00','Aplicado'),
(20,20,117,'PAY-202606-0020',436.41,'Tarjeta','4120','2026-06-22 10:23:00','Aplicado'),
(20,20,118,'PAY-202607-0020',443.18,'SPEI','4120','2026-07-22 10:24:00','Aplicado'),
(20,20,119,'PAY-202608-0020',468.46,'Domiciliación','4120','2026-08-22 10:25:00','Aplicado');

INSERT INTO notificaciones (usuario_id,titulo,mensaje,tipo,fecha,leida) VALUES
(1,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(1,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(1,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(2,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(2,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(2,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(3,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(3,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(3,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(4,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(4,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(4,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(5,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(5,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(5,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(6,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(6,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(6,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(7,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(7,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(7,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(8,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(8,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(8,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(9,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(9,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(9,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(10,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(10,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(10,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(11,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(11,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(11,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(12,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(12,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(12,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(13,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(13,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(13,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(14,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(14,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(14,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(15,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(15,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(15,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(16,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(16,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(16,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(17,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(17,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(17,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(18,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(18,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(18,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1),
(19,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(19,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(19,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',0),
(20,'Tu recibo de septiembre está disponible','Consulta el detalle de consumo, fecha límite y opciones de pago.','facturacion','2026-09-05 08:30:00',0),
(20,'Lectura registrada','La lectura mensual de tu medidor fue integrada correctamente.','servicio','2026-09-15 13:05:00',1),
(20,'Consejo de ahorro','Compara tu consumo con el promedio de tu zona desde Mi Portal.','cultura','2026-09-18 09:15:00',1);

INSERT INTO proveedores (nombre,servicio,estado) VALUES
('Metropolitana de Occidente Grupo Técnico','Desarrollo de software','En revisión'),
('Aqua del Altiplano México','Equipamiento de bombeo','Vigente'),
('Soluciones Urbanos S. de R.L.','Topografía y GIS','Vigente'),
('Construcciones Metropolitanos México','Mantenimiento de redes','En revisión'),
('Conducciones Industriales S.A. de C.V.','Mantenimiento de pozos','Vigente'),
('Telemetría del Bajío México','Mantenimiento electromecánico','Vigente'),
('Logística Hídricos Integrales','Rehabilitación de tanques','Vigente'),
('Aqua Hídricos Ingeniería','Desarrollo de software','Vigente'),
('Metropolitana del Bajío Grupo Técnico','Servicios de impresión','Vigente'),
('Telemetría Metropolitanos Proyectos','Tratamiento de aguas residuales','Vigente'),
('Control Integrados Integrales','Desarrollo de software','Vigente'),
('Equipos Operativos Servicios','Seguridad física','Vigente'),
('Aqua de Occidente México','Integración de sistemas de supervisión','Vigente'),
('Soluciones Urbanos Servicios','Mantenimiento electromecánico','Vigente'),
('Telemetría Ambientales S. de R.L.','Energía eléctrica y eficiencia','En revisión'),
('Hidro de Occidente S. de R.L.','Protección civil y seguridad','Vigente'),
('Mantenimiento Operativos S.A. de C.V.','Medición y macromedición','En revisión'),
('Soluciones del Altiplano S. de R.L.','Enlaces y soporte','En revisión'),
('Hidro Digitales México','Arrendamiento de maquinaria','En revisión'),
('Servicios Integrados México','Mantenimiento electromecánico','Vigente'),
('Soluciones Hídricos Ingeniería','Gestión de lodos','Vigente'),
('Consultoría del Altiplano S.A. de C.V.','Gestión de lodos','Vigente'),
('Consultoría Digitales Ingeniería','Gestión de lodos','Vigente'),
('Electromecánica Digitales S.A. de C.V.','Protección civil y seguridad','Vigente'),
('Ingeniería Metropolitanos Grupo Técnico','Limpieza industrial','Vigente'),
('Energía Digitales Proyectos','Atención y call center','Vigente'),
('Ingeniería Integrados S.A. de C.V.','Mantenimiento de redes','En revisión'),
('Aqua del Bajío Servicios','Telecomunicaciones','En revisión'),
('Consultoría Urbanos Servicios','Obra civil hidráulica','Vigente'),
('Saneamiento Especializados S. de R.L.','Equipamiento de bombeo','En revisión'),
('Construcciones del Altiplano S. de R.L.','Telecomunicaciones','En revisión'),
('Obras Integrados Proyectos','Arrendamiento de maquinaria','Vigente'),
('Consultoría del Centro Integrales','Atención y call center','Vigente'),
('Sistemas del Bajío México','Equipamiento de bombeo','Vigente'),
('Logística Regionales México','Protección civil y seguridad','Vigente'),
('Metropolitana Urbanos Ingeniería','Ciberseguridad y soporte TI','Vigente'),
('Ambiental Industriales Proyectos','Seguridad física','Vigente'),
('Proyectos Hídricos Proyectos','Integración de sistemas de supervisión','Vigente'),
('Energía Ambientales Ingeniería','Ciberseguridad y soporte TI','Vigente'),
('Ambiental del Bajío Servicios','Telecomunicaciones','En revisión'),
('Construcciones del Centro Integrales','Laboratorio y calidad del agua','Vigente'),
('Consultoría Digitales Integrales','Laboratorio y calidad del agua','Vigente'),
('Tecnología Urbanos S.A. de C.V.','Transporte especializado','Vigente'),
('Logística Metropolitanos Proyectos','Instrumentación y sensores','Vigente'),
('Construcciones Especializados Proyectos','Consultoría ambiental','Vigente'),
('Servicios Especializados Servicios','Arrendamiento de maquinaria','Vigente'),
('Control Metropolitanos Proyectos','Transporte especializado','Vigente'),
('Telemetría Urbanos S.A. de C.V.','Mantenimiento electromecánico','Vigente'),
('Logística del Bajío Servicios','Mantenimiento electromecánico','Vigente'),
('Construcciones del Centro S. de R.L.','Desarrollo de software','Vigente'),
('Hidro Operativos S. de R.L.','Limpieza industrial','Vigente'),
('Ambiental Digitales S. de R.L.','Instrumentación y sensores','Vigente'),
('Sistemas Digitales Proyectos','Mantenimiento de pozos','Vigente'),
('Obras Metropolitanos Grupo Técnico','Ciberseguridad y soporte TI','Vigente'),
('Metropolitana Regionales Proyectos','Telecomunicaciones','Vigente'),
('Obras Hídricos Ingeniería','Energía eléctrica y eficiencia','Vigente'),
('Logística Urbanos S.A. de C.V.','Tratamiento de aguas residuales','Vigente'),
('Metropolitana Digitales Grupo Técnico','Desarrollo de software','Vigente'),
('Ingeniería Operativos S. de R.L.','Integración de sistemas de supervisión','Vigente'),
('Redes Hídricos Integrales','Consultoría ambiental','Vigente'),
('Consultoría Integrados S.A. de C.V.','Ciberseguridad y soporte TI','Vigente'),
('Saneamiento de Occidente Servicios','Integración de sistemas de supervisión','Vigente'),
('Construcciones del Altiplano México','Ciberseguridad y soporte TI','Vigente'),
('Construcciones Especializados México','Limpieza industrial','Vigente'),
('Mantenimiento Regionales S.A. de C.V.','Medición y macromedición','Vigente'),
('Electromecánica Digitales Servicios','Ciberseguridad y soporte TI','Vigente'),
('Redes de Occidente Proyectos','Limpieza industrial','Vigente'),
('Hidro Urbanos Grupo Técnico','Mantenimiento de pozos','En revisión'),
('Saneamiento Urbanos S. de R.L.','Arrendamiento de maquinaria','Vigente'),
('Logística Regionales Ingeniería','Integración de sistemas de supervisión','Vigente'),
('Ingeniería del Valle S.A. de C.V.','Mantenimiento de redes','Vigente'),
('Ingeniería Especializados México','Protección civil y seguridad','Vigente'),
('Redes Especializados Proyectos','Telecomunicaciones','En revisión'),
('Logística Ambientales Integrales','Suministro de válvulas','Vigente'),
('Redes del Altiplano Servicios','Suministro de válvulas','Vigente'),
('Mantenimiento Industriales México','Instrumentación y sensores','Vigente'),
('Telemetría Regionales México','Suministro de válvulas','Vigente'),
('Logística Metropolitanos Integrales','Telecomunicaciones','En revisión'),
('Redes del Valle Grupo Técnico','Servicios de impresión','En revisión'),
('Proyectos Metropolitanos S. de R.L.','Mantenimiento de pozos','Vigente'),
('Logística Industriales Ingeniería','Medición y macromedición','Vigente'),
('Ingeniería del Altiplano Integrales','Consultoría ambiental','Vigente'),
('Control del Altiplano S.A. de C.V.','Consultoría ambiental','Vigente'),
('Energía Industriales Proyectos','Mantenimiento de pozos','Vigente'),
('Equipos del Centro S.A. de C.V.','Transporte especializado','En revisión'),
('Hidro Hídricos Grupo Técnico','Consultoría ambiental','Vigente'),
('Logística del Bajío Ingeniería','Enlaces y soporte','En revisión'),
('Construcciones de Occidente Integrales','Desarrollo de software','Vigente'),
('Logística Digitales S.A. de C.V.','Mantenimiento de pozos','Vigente'),
('Soluciones del Valle S. de R.L.','Instrumentación y sensores','Vigente'),
('Consultoría Regionales México','Seguridad física','Vigente'),
('Equipos Especializados Proyectos','Limpieza industrial','En revisión'),
('Saneamiento Urbanos Ingeniería','Tratamiento de aguas residuales','Vigente'),
('Servicios Urbanos Proyectos','Limpieza industrial','Vigente'),
('Construcciones de Occidente S. de R.L.','Instrumentación y sensores','Vigente'),
('Saneamiento del Altiplano Ingeniería','Mantenimiento de pozos','Vigente'),
('Tecnología Especializados S.A. de C.V.','Energía eléctrica y eficiencia','Vigente'),
('Mantenimiento Hídricos Ingeniería','Desarrollo de software','Vigente'),
('Construcciones Metropolitanos Proyectos','Desarrollo de software','Vigente'),
('Control Especializados México','Consultoría ambiental','Vigente'),
('Conducciones del Valle Grupo Técnico','Ciberseguridad y soporte TI','Vigente'),
('Aqua Ambientales Grupo Técnico','Mantenimiento de redes','Vigente'),
('Logística del Altiplano S.A. de C.V.','Mantenimiento electromecánico','En revisión'),
('Electromecánica Hídricos S. de R.L.','Atención y call center','Vigente'),
('Energía Ambientales Servicios','Seguridad física','Vigente'),
('Metropolitana Especializados Ingeniería','Limpieza industrial','Vigente'),
('Hidro Regionales Proyectos','Arrendamiento de maquinaria','Vigente'),
('Construcciones Especializados S. de R.L.','Ciberseguridad y soporte TI','Vigente'),
('Ambiental del Bajío S. de R.L.','Equipamiento de bombeo','Vigente'),
('Servicios Industriales Ingeniería','Medición y macromedición','Vigente'),
('Servicios Especializados S. de R.L.','Mantenimiento de pozos','Vigente'),
('Redes del Bajío S. de R.L.','Transporte especializado','Vigente'),
('Hidro Regionales S.A. de C.V.','Obra civil hidráulica','Vigente'),
('Consultoría Metropolitanos Ingeniería','Mantenimiento de redes','Vigente'),
('Infraestructura Industriales Proyectos','Atención y call center','Vigente'),
('Infraestructura Urbanos Integrales','Medición y macromedición','Vigente'),
('Proyectos del Bajío Integrales','Desarrollo de software','Vigente'),
('Saneamiento Ambientales México','Topografía y GIS','Vigente'),
('Servicios Especializados Proyectos','Desarrollo de software','Vigente'),
('Construcciones Urbanos México','Mantenimiento de redes','Vigente'),
('Energía Integrados México','Topografía y GIS','Vigente'),
('Tecnología Ambientales S.A. de C.V.','Equipamiento de bombeo','Vigente'),
('Consultoría Industriales Integrales','Energía eléctrica y eficiencia','Vigente'),
('Ambiental Regionales Ingeniería','Integración de sistemas de supervisión','Vigente'),
('Equipos Operativos Proyectos','Desarrollo de software','Vigente'),
('Servicios de Occidente S. de R.L.','Servicios de impresión','Vigente'),
('Sistemas Regionales Ingeniería','Mantenimiento de pozos','Vigente'),
('Redes del Valle México','Integración de sistemas de supervisión','Vigente'),
('Redes del Centro S.A. de C.V.','Protección civil y seguridad','Vigente'),
('Obras de Occidente México','Rehabilitación de tanques','Vigente'),
('Obras Regionales S. de R.L.','Instrumentación y sensores','Vigente'),
('Infraestructura Regionales Ingeniería','Atención y call center','Vigente'),
('Mantenimiento del Bajío Servicios','Laboratorio y calidad del agua','En revisión'),
('Conducciones Operativos Integrales','Obra civil hidráulica','Vigente'),
('Ambiental Regionales Proyectos','Equipamiento de bombeo','Vigente'),
('Hidro del Centro S.A. de C.V.','Instrumentación y sensores','Vigente'),
('Infraestructura del Altiplano Proyectos','Consultoría ambiental','Vigente'),
('Equipos del Centro Integrales','Atención y call center','Vigente'),
('Electromecánica del Bajío México','Obra civil hidráulica','Vigente'),
('Ingeniería Industriales Ingeniería','Gestión de lodos','Vigente'),
('Conducciones del Valle Servicios','Seguridad física','Vigente'),
('Control Industriales Ingeniería','Medición y macromedición','Vigente'),
('Servicios del Altiplano Servicios','Desarrollo de software','Vigente'),
('Conducciones Ambientales S.A. de C.V.','Obra civil hidráulica','Vigente'),
('Servicios Metropolitanos S.A. de C.V.','Rehabilitación de tanques','Vigente'),
('Obras Hídricos México','Enlaces y soporte','Vigente'),
('Sistemas del Valle S. de R.L.','Arrendamiento de maquinaria','En revisión'),
('Tecnología Regionales Grupo Técnico','Mantenimiento de pozos','En revisión'),
('Mantenimiento del Valle Proyectos','Laboratorio y calidad del agua','Vigente'),
('Saneamiento del Centro S. de R.L.','Instrumentación y sensores','Vigente'),
('Redes del Bajío Servicios','Mantenimiento electromecánico','Vigente'),
('Metropolitana del Centro S.A. de C.V.','Transporte especializado','Vigente'),
('Tecnología de Occidente México','Desarrollo de software','Vigente'),
('Ambiental del Bajío Ingeniería','Laboratorio y calidad del agua','Vigente'),
('Mantenimiento Especializados Integrales','Integración de sistemas de supervisión','Vigente'),
('Control del Altiplano Proyectos','Energía eléctrica y eficiencia','Vigente'),
('Telemetría de Occidente S. de R.L.','Topografía y GIS','Vigente'),
('Hidro Urbanos Servicios','Limpieza industrial','Vigente'),
('Equipos Integrados S.A. de C.V.','Enlaces y soporte','Vigente'),
('Equipos Regionales Proyectos','Protección civil y seguridad','Vigente'),
('Ingeniería Operativos Servicios','Telecomunicaciones','En revisión'),
('Construcciones Ambientales S. de R.L.','Instrumentación y sensores','Vigente'),
('Hidro Integrados Grupo Técnico','Rehabilitación de tanques','Vigente'),
('Construcciones del Altiplano Ingeniería','Transporte especializado','Vigente'),
('Metropolitana de Occidente S.A. de C.V.','Medición y macromedición','Vigente'),
('Ambiental Hídricos Proyectos','Atención y call center','Vigente'),
('Metropolitana de Occidente S. de R.L.','Atención y call center','Vigente'),
('Conducciones del Valle México','Mantenimiento de redes','Vigente'),
('Saneamiento Hídricos S.A. de C.V.','Atención y call center','En revisión'),
('Obras Especializados Servicios','Protección civil y seguridad','Vigente'),
('Control Hídricos S.A. de C.V.','Gestión de lodos','Vigente'),
('Obras Especializados México','Seguridad física','Vigente'),
('Energía Hídricos Grupo Técnico','Consultoría ambiental','Vigente'),
('Saneamiento Operativos S.A. de C.V.','Enlaces y soporte','Vigente'),
('Mantenimiento de Occidente Ingeniería','Seguridad física','Vigente'),
('Obras Urbanos Integrales','Tratamiento de aguas residuales','En revisión'),
('Energía del Centro México','Mantenimiento electromecánico','Vigente'),
('Consultoría de Occidente Servicios','Gestión de lodos','Vigente'),
('Consultoría Industriales Ingeniería','Arrendamiento de maquinaria','Vigente'),
('Telemetría del Centro Grupo Técnico','Transporte especializado','Vigente'),
('Proyectos del Bajío Servicios','Ciberseguridad y soporte TI','Vigente'),
('Logística Industriales S. de R.L.','Transporte especializado','Vigente'),
('Servicios Operativos Proyectos','Integración de sistemas de supervisión','Vigente'),
('Mantenimiento Regionales Grupo Técnico','Ciberseguridad y soporte TI','Vigente'),
('Sistemas Especializados Proyectos','Suministro de válvulas','Vigente'),
('Proyectos Integrados Proyectos','Consultoría ambiental','Vigente'),
('Telemetría del Altiplano México','Transporte especializado','Vigente'),
('Sistemas Integrados Integrales','Suministro de válvulas','Vigente'),
('Obras Operativos Ingeniería','Medición y macromedición','Vigente'),
('Mantenimiento Regionales México','Equipamiento de bombeo','Vigente'),
('Proyectos del Centro Grupo Técnico','Limpieza industrial','Vigente'),
('Redes de Occidente México','Mantenimiento de pozos','Vigente'),
('Sistemas Industriales Ingeniería','Suministro de válvulas','Vigente'),
('Telemetría del Valle S.A. de C.V.','Transporte especializado','Vigente'),
('Proyectos Industriales Proyectos','Transporte especializado','Vigente'),
('Construcciones del Altiplano Servicios','Enlaces y soporte','Vigente'),
('Servicios Integrados S. de R.L.','Medición y macromedición','Vigente'),
('Mantenimiento Metropolitanos Integrales','Mantenimiento de redes','Vigente'),
('Metropolitana Metropolitanos S. de R.L.','Topografía y GIS','Vigente'),
('Proyectos Regionales S. de R.L.','Limpieza industrial','En revisión'),
('Tecnología Ambientales Proyectos','Energía eléctrica y eficiencia','Vigente'),
('Electromecánica del Altiplano S. de R.L.','Servicios de impresión','En revisión'),
('Soluciones Operativos Servicios','Tratamiento de aguas residuales','Vigente'),
('Proyectos Industriales Servicios','Rehabilitación de tanques','Vigente'),
('Control de Occidente S.A. de C.V.','Seguridad física','Vigente'),
('Electromecánica Operativos Ingeniería','Mantenimiento de pozos','Vigente'),
('Equipos del Centro Proyectos','Mantenimiento de redes','Vigente'),
('Logística del Altiplano México','Mantenimiento de pozos','En revisión'),
('Hidro Especializados México','Limpieza industrial','Vigente'),
('Mantenimiento Hídricos Servicios','Ciberseguridad y soporte TI','Vigente'),
('Servicios Metropolitanos Grupo Técnico','Mantenimiento electromecánico','Vigente'),
('Servicios del Centro México','Integración de sistemas de supervisión','Vigente'),
('Equipos Regionales S.A. de C.V.','Mantenimiento de redes','En revisión'),
('Equipos Regionales Integrales','Transporte especializado','Vigente'),
('Servicios Operativos S. de R.L.','Mantenimiento electromecánico','Vigente'),
('Proyectos Especializados México','Servicios de impresión','Vigente'),
('Conducciones Especializados S. de R.L.','Topografía y GIS','Vigente'),
('Hidro Hídricos Ingeniería','Consultoría ambiental','Vigente'),
('Ambiental del Centro Proyectos','Medición y macromedición','Vigente');

INSERT INTO contratos (numero,proveedor_id,objeto,modalidad,monto,fecha_inicio,fecha_fin,origen_recurso,estado) VALUES
('INT-2025-0001',162,'Modernización de tableros eléctricos','Contrato marco',1337192.94,'2025-02-22','2025-11-22','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0002',183,'Servicio integral de laboratorio','Contrato marco',4533427.93,'2025-06-01','2025-12-25','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0003',98,'Suministro de medidores ultrasónicos','Invitación restringida',8702368.03,'2025-03-11','2026-12-21','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0004',46,'Servicio integral de laboratorio','Licitación pública',12190082.22,'2025-07-22','2025-11-25','Aportaciones intermunicipales','Vigente'),
('INT-2025-0005',219,'Suministro de medidores ultrasónicos','Adjudicación directa',9699022.95,'2025-04-20','2025-12-22','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0006',128,'Mantenimiento preventivo y correctivo de equipos de bombeo','Contrato marco',6275983.56,'2025-03-05','2025-10-14','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2025-0007',115,'Servicio de atención telefónica','Invitación restringida',2402219.97,'2025-07-22','2026-12-24','Recursos propios INTERAFAS','Vigente'),
('INT-2025-0008',127,'Mantenimiento de planta de tratamiento','Adjudicación directa',11481590.15,'2025-04-20','2025-12-10','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2025-0009',150,'Mantenimiento de planta de tratamiento','Licitación pública',3261285.72,'2025-02-07','2025-12-17','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2025-0010',200,'Mantenimiento de planta de tratamiento','Invitación restringida',2717494.75,'2025-01-09','2025-12-03','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2025-0011',113,'Mantenimiento de estaciones remotas','Invitación restringida',11538949.67,'2025-06-25','2025-10-04','Fondo de Resiliencia y Modernización Operativa','Concluido'),
('INT-2025-0012',10,'Actualización de cartografía GIS','Invitación restringida',5744222.43,'2025-02-15','2026-11-05','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0013',184,'Mantenimiento de servidores y almacenamiento','Contrato marco',6398809.53,'2025-01-24','2025-12-17','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2025-0014',12,'Suministro de válvulas de seccionamiento','Licitación pública',11824671.85,'2025-07-20','2026-11-01','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2025-0015',147,'Servicios de telemetría y comunicaciones','Licitación pública',3716552.85,'2025-02-04','2025-12-22','Aportaciones intermunicipales','Vigente'),
('INT-2025-0016',47,'Suministro de medidores ultrasónicos','Invitación restringida',10680211.52,'2025-07-04','2025-12-04','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0017',87,'Arrendamiento de maquinaria especializada','Adjudicación directa',8515799.68,'2025-04-22','2026-09-15','Aportaciones intermunicipales','Vigente'),
('INT-2025-0018',92,'Rehabilitación de red secundaria','Contrato marco',2893404.14,'2025-01-24','2025-12-16','Aportaciones intermunicipales','Vigente'),
('INT-2025-0019',180,'Limpieza de colectores metropolitanos','Invitación restringida',11256397.69,'2025-05-20','2025-11-07','Recursos propios INTERAFAS','Vigente'),
('INT-2025-0020',162,'Suministro de medidores ultrasónicos','Contrato marco',7318321.49,'2025-03-15','2025-11-25','Aportaciones intermunicipales','Vigente'),
('INT-2025-0021',33,'Suministro de medidores ultrasónicos','Contrato marco',3054386.33,'2025-07-20','2025-09-14','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2025-0022',26,'Rehabilitación de red secundaria','Invitación restringida',2631624.09,'2025-02-19','2026-11-12','Aportaciones intermunicipales','Concluido'),
('INT-2025-0023',75,'Mantenimiento de planta de tratamiento','Contrato marco',11601470.42,'2025-07-16','2025-09-09','Aportaciones intermunicipales','Vigente'),
('INT-2025-0024',114,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',5847230.06,'2025-03-08','2025-12-12','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2025-0025',203,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',3154018.98,'2025-08-08','2025-10-18','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0026',169,'Arrendamiento de maquinaria especializada','Adjudicación directa',334955.54,'2025-01-07','2025-10-02','Recursos propios INTERAFAS','Vigente'),
('INT-2025-0027',65,'Mantenimiento de estaciones remotas','Contrato marco',3639604.16,'2025-05-12','2025-11-23','Aportaciones intermunicipales','Vigente'),
('INT-2025-0028',188,'Servicios de telemetría y comunicaciones','Contrato marco',11504917.40,'2025-08-01','2026-10-11','Recursos propios INTERAFAS','Vigente'),
('INT-2025-0029',52,'Modernización de tableros eléctricos','Licitación pública',9983898.07,'2025-07-02','2025-09-12','Recursos propios INTERAFAS','Vigente'),
('INT-2025-0030',107,'Rehabilitación de tanque elevado','Licitación pública',8263315.70,'2025-07-01','2025-11-10','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2025-0031',18,'Servicio integral de laboratorio','Adjudicación directa',2033154.24,'2025-03-05','2025-10-06','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2025-0032',75,'Servicios de telemetría y comunicaciones','Licitación pública',1469968.83,'2025-02-02','2025-09-19','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2025-0033',75,'Servicio integral de laboratorio','Contrato marco',3497658.49,'2025-04-08','2025-10-21','Fondo de Resiliencia y Modernización Operativa','Concluido'),
('INT-2025-0034',51,'Mantenimiento de estaciones remotas','Licitación pública',1456441.68,'2025-01-20','2025-11-25','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0035',52,'Limpieza de colectores metropolitanos','Contrato marco',6950899.38,'2026-06-08','2026-12-07','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0036',62,'Limpieza de colectores metropolitanos','Adjudicación directa',7469674.86,'2026-01-01','2026-12-20','Aportaciones intermunicipales','Vigente'),
('INT-2026-0037',215,'Rehabilitación de tanque elevado','Contrato marco',12123245.78,'2026-08-06','2027-12-07','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0038',178,'Modernización de tableros eléctricos','Invitación restringida',6386967.82,'2026-01-13','2026-12-02','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0039',107,'Arrendamiento de maquinaria especializada','Contrato marco',12064172.53,'2026-08-22','2026-11-12','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0040',106,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',4368914.42,'2026-02-06','2026-10-01','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0041',91,'Actualización de cartografía GIS','Licitación pública',2142544.97,'2026-08-21','2026-09-09','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0042',80,'Mantenimiento de planta de tratamiento','Invitación restringida',4417506.20,'2026-02-24','2027-11-24','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0043',201,'Arrendamiento de maquinaria especializada','Licitación pública',12034994.06,'2026-02-12','2027-12-13','Aportaciones intermunicipales','Vigente'),
('INT-2026-0044',15,'Suministro de válvulas de seccionamiento','Adjudicación directa',8951709.97,'2026-04-12','2026-12-23','Aportaciones intermunicipales','Concluido'),
('INT-2026-0045',36,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',7968633.98,'2026-03-07','2026-12-06','Aportaciones intermunicipales','Vigente'),
('INT-2026-0046',160,'Mantenimiento de planta de tratamiento','Licitación pública',2991260.05,'2026-08-09','2027-09-11','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0047',82,'Servicio de atención telefónica','Adjudicación directa',12231486.33,'2026-03-17','2026-10-16','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0048',145,'Suministro de válvulas de seccionamiento','Invitación restringida',7039899.48,'2026-04-10','2026-11-06','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0049',118,'Mantenimiento de estaciones remotas','Licitación pública',1552521.03,'2026-05-15','2026-11-14','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0050',159,'Suministro de medidores ultrasónicos','Invitación restringida',2580295.20,'2026-08-01','2026-09-13','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0051',171,'Mantenimiento de planta de tratamiento','Contrato marco',9334885.26,'2026-08-24','2026-12-24','Aportaciones intermunicipales','Vigente'),
('INT-2026-0052',100,'Modernización de tableros eléctricos','Invitación restringida',1873084.14,'2026-08-13','2026-11-11','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0053',147,'Mantenimiento de estaciones remotas','Invitación restringida',7004953.03,'2026-06-19','2026-12-09','Aportaciones intermunicipales','Vigente'),
('INT-2026-0054',141,'Servicios de telemetría y comunicaciones','Invitación restringida',11372136.30,'2026-01-03','2026-09-18','Aportaciones intermunicipales','Vigente'),
('INT-2026-0055',175,'Rehabilitación de red secundaria','Licitación pública',10512626.38,'2026-02-13','2026-11-08','Fondo de Resiliencia y Modernización Operativa','Concluido'),
('INT-2026-0056',117,'Mantenimiento de estaciones remotas','Invitación restringida',3241249.86,'2026-07-09','2026-11-21','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0057',199,'Mantenimiento preventivo y correctivo de equipos de bombeo','Contrato marco',2496598.84,'2026-06-17','2026-09-10','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0058',107,'Mantenimiento de servidores y almacenamiento','Licitación pública',3879267.06,'2026-01-19','2026-11-05','Aportaciones intermunicipales','Vigente'),
('INT-2026-0059',76,'Suministro de medidores ultrasónicos','Contrato marco',1907819.29,'2026-02-04','2026-09-24','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0060',205,'Servicio de atención telefónica','Contrato marco',1427838.15,'2026-01-11','2026-10-10','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0061',154,'Modernización de tableros eléctricos','Invitación restringida',6240502.03,'2026-07-07','2026-12-03','Aportaciones intermunicipales','Vigente'),
('INT-2026-0062',76,'Modernización de tableros eléctricos','Adjudicación directa',5385351.59,'2026-01-04','2027-10-04','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0063',121,'Servicios de telemetría y comunicaciones','Invitación restringida',1779099.79,'2026-01-25','2026-10-25','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0064',147,'Mantenimiento preventivo y correctivo de equipos de bombeo','Invitación restringida',1481917.82,'2026-01-22','2027-10-11','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0065',91,'Rehabilitación de tanque elevado','Adjudicación directa',11518573.50,'2026-04-13','2026-12-24','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0066',15,'Servicio integral de laboratorio','Invitación restringida',7291213.96,'2026-02-11','2026-10-19','Recursos propios INTERAFAS','Concluido'),
('INT-2026-0067',219,'Mantenimiento de planta de tratamiento','Licitación pública',3062366.44,'2026-05-03','2026-11-08','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0068',87,'Rehabilitación de tanque elevado','Licitación pública',3840945.00,'2026-03-11','2026-10-18','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0069',205,'Mantenimiento de planta de tratamiento','Contrato marco',6672499.57,'2026-08-17','2027-10-09','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0070',99,'Mantenimiento de planta de tratamiento','Contrato marco',4757095.08,'2026-03-08','2026-11-18','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0071',145,'Actualización de cartografía GIS','Contrato marco',10501083.13,'2026-02-17','2026-12-10','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0072',206,'Servicios de telemetría y comunicaciones','Contrato marco',3689139.30,'2026-04-25','2026-11-01','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0073',80,'Mantenimiento preventivo y correctivo de equipos de bombeo','Invitación restringida',1527083.65,'2026-06-08','2027-10-15','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0074',155,'Mantenimiento de planta de tratamiento','Adjudicación directa',6640230.55,'2026-07-21','2026-12-15','Aportaciones intermunicipales','Vigente'),
('INT-2026-0075',215,'Suministro de válvulas de seccionamiento','Invitación restringida',9138632.95,'2026-05-05','2026-11-18','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0076',12,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',8667523.72,'2026-03-11','2026-10-13','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0077',45,'Servicio de atención telefónica','Licitación pública',1744656.13,'2026-07-17','2026-10-19','Fondo Metropolitano de Infraestructura Hídrica','Concluido'),
('INT-2026-0078',119,'Mantenimiento de planta de tratamiento','Invitación restringida',6636506.77,'2026-05-13','2027-11-18','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0079',74,'Servicios de telemetría y comunicaciones','Invitación restringida',6414732.78,'2026-04-18','2026-12-24','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0080',83,'Modernización de tableros eléctricos','Invitación restringida',9424222.87,'2026-02-21','2027-10-19','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0081',134,'Limpieza de colectores metropolitanos','Licitación pública',11025647.38,'2026-03-07','2026-09-08','Aportaciones intermunicipales','Vigente'),
('INT-2026-0082',64,'Suministro de válvulas de seccionamiento','Licitación pública',6577654.05,'2026-03-24','2026-10-16','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0083',149,'Mantenimiento preventivo y correctivo de equipos de bombeo','Adjudicación directa',7905723.63,'2026-04-14','2027-11-16','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0084',209,'Servicios de telemetría y comunicaciones','Adjudicación directa',823028.35,'2026-04-25','2026-09-15','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0085',73,'Actualización de cartografía GIS','Licitación pública',10815752.45,'2026-08-08','2027-10-13','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0086',99,'Mantenimiento de servidores y almacenamiento','Adjudicación directa',12061507.46,'2026-02-23','2027-12-25','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0087',60,'Suministro de válvulas de seccionamiento','Adjudicación directa',11670901.86,'2026-06-19','2027-12-18','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0088',114,'Mantenimiento de servidores y almacenamiento','Adjudicación directa',6855065.22,'2026-05-20','2027-12-14','Recursos propios INTERAFAS','Concluido'),
('INT-2026-0089',99,'Limpieza de colectores metropolitanos','Invitación restringida',11835385.12,'2026-01-09','2027-11-12','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0090',218,'Suministro de válvulas de seccionamiento','Invitación restringida',3847463.38,'2026-01-09','2026-12-04','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0091',134,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',12603693.69,'2026-08-08','2027-09-18','Aportaciones intermunicipales','Vigente'),
('INT-2026-0092',76,'Modernización de tableros eléctricos','Invitación restringida',5488577.38,'2026-03-04','2026-09-20','Aportaciones intermunicipales','Vigente'),
('INT-2026-0093',39,'Rehabilitación de tanque elevado','Invitación restringida',12113612.52,'2026-02-03','2026-09-22','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0094',212,'Mantenimiento de planta de tratamiento','Invitación restringida',5655979.89,'2026-05-16','2026-09-12','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0095',1,'Servicio de atención telefónica','Invitación restringida',11982951.87,'2026-03-02','2026-10-12','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0096',199,'Modernización de tableros eléctricos','Licitación pública',7828657.79,'2026-02-04','2026-11-14','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0097',198,'Suministro de medidores ultrasónicos','Contrato marco',501865.23,'2026-07-22','2026-12-07','Aportaciones intermunicipales','Vigente'),
('INT-2026-0098',201,'Suministro de válvulas de seccionamiento','Adjudicación directa',10438880.30,'2026-06-02','2026-10-25','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0099',209,'Modernización de tableros eléctricos','Adjudicación directa',5653773.37,'2026-04-16','2026-10-09','Programa de Agua Potable y Saneamiento','Concluido'),
('INT-2026-0100',177,'Mantenimiento preventivo y correctivo de equipos de bombeo','Invitación restringida',9519909.39,'2026-03-15','2026-09-16','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0101',94,'Rehabilitación de tanque elevado','Adjudicación directa',4582360.90,'2026-05-24','2026-12-03','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0102',176,'Rehabilitación de red secundaria','Adjudicación directa',6377841.62,'2026-05-05','2026-09-20','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0103',212,'Servicios de telemetría y comunicaciones','Invitación restringida',9423449.52,'2026-04-15','2026-10-09','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0104',78,'Servicio integral de laboratorio','Invitación restringida',3217621.87,'2026-02-05','2026-11-24','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0105',77,'Limpieza de colectores metropolitanos','Adjudicación directa',10554590.62,'2026-02-06','2026-11-06','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0106',112,'Mantenimiento de estaciones remotas','Licitación pública',3106887.36,'2026-08-18','2026-11-03','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0107',159,'Rehabilitación de tanque elevado','Invitación restringida',7585939.64,'2026-08-06','2026-09-01','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0108',154,'Limpieza de colectores metropolitanos','Adjudicación directa',4831718.35,'2026-03-24','2026-11-13','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0109',178,'Servicio de atención telefónica','Licitación pública',1540493.33,'2026-06-06','2027-10-05','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0110',17,'Actualización de cartografía GIS','Invitación restringida',5979527.79,'2026-08-19','2026-09-17','Recursos propios INTERAFAS','Concluido'),
('INT-2026-0111',87,'Arrendamiento de maquinaria especializada','Invitación restringida',5541144.51,'2026-03-01','2026-10-08','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0112',168,'Actualización de cartografía GIS','Licitación pública',10705614.38,'2026-07-07','2026-12-17','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0113',54,'Limpieza de colectores metropolitanos','Adjudicación directa',3927893.06,'2026-02-19','2026-12-09','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0114',86,'Mantenimiento preventivo y correctivo de equipos de bombeo','Licitación pública',2621380.88,'2026-02-11','2026-12-14','Aportaciones intermunicipales','Vigente'),
('INT-2026-0115',151,'Arrendamiento de maquinaria especializada','Invitación restringida',681445.07,'2026-06-15','2027-10-06','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0116',184,'Mantenimiento de estaciones remotas','Invitación restringida',7496980.90,'2026-07-11','2027-11-22','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0117',170,'Actualización de cartografía GIS','Contrato marco',5303202.87,'2026-05-20','2027-09-04','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0118',38,'Suministro de válvulas de seccionamiento','Invitación restringida',4014351.56,'2026-04-16','2026-11-16','Aportaciones intermunicipales','Vigente'),
('INT-2026-0119',105,'Suministro de medidores ultrasónicos','Contrato marco',4613790.54,'2026-05-09','2026-11-02','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0120',47,'Mantenimiento de estaciones remotas','Invitación restringida',611129.25,'2026-05-14','2026-10-01','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0121',84,'Rehabilitación de red secundaria','Contrato marco',3670192.44,'2026-04-16','2026-09-21','Aportaciones intermunicipales','Concluido'),
('INT-2026-0122',30,'Mantenimiento de estaciones remotas','Adjudicación directa',9528965.15,'2026-03-19','2026-10-18','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0123',194,'Suministro de medidores ultrasónicos','Contrato marco',8881529.42,'2026-03-16','2026-12-19','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0124',91,'Arrendamiento de maquinaria especializada','Invitación restringida',272190.52,'2026-07-13','2026-09-14','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0125',96,'Mantenimiento de planta de tratamiento','Invitación restringida',5964840.90,'2026-06-20','2026-11-17','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0126',193,'Mantenimiento de planta de tratamiento','Contrato marco',6199128.37,'2026-04-21','2027-11-24','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0127',105,'Arrendamiento de maquinaria especializada','Invitación restringida',5203202.83,'2026-02-11','2026-12-16','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0128',10,'Mantenimiento de planta de tratamiento','Contrato marco',9680812.05,'2026-04-02','2026-09-21','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0129',173,'Rehabilitación de red secundaria','Adjudicación directa',7240822.72,'2026-01-04','2026-11-09','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0130',66,'Modernización de tableros eléctricos','Contrato marco',9757628.64,'2026-03-20','2026-10-06','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0131',144,'Mantenimiento de planta de tratamiento','Adjudicación directa',10777508.42,'2026-07-02','2026-11-11','Recursos propios INTERAFAS','Vigente'),
('INT-2026-0132',53,'Servicio de atención telefónica','Adjudicación directa',1974447.28,'2026-07-06','2026-10-02','Fondo de Resiliencia y Modernización Operativa','Concluido'),
('INT-2026-0133',25,'Suministro de medidores ultrasónicos','Contrato marco',832797.53,'2026-04-07','2026-11-14','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0134',66,'Modernización de tableros eléctricos','Invitación restringida',3988391.59,'2026-04-19','2026-12-01','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0135',211,'Suministro de válvulas de seccionamiento','Licitación pública',4106928.61,'2026-07-20','2027-09-01','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0136',162,'Mantenimiento preventivo y correctivo de equipos de bombeo','Contrato marco',8808250.19,'2026-04-10','2027-10-23','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0137',51,'Limpieza de colectores metropolitanos','Invitación restringida',10665828.92,'2026-08-17','2027-12-22','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0138',13,'Arrendamiento de maquinaria especializada','Adjudicación directa',8319228.32,'2026-01-13','2026-09-09','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0139',128,'Suministro de medidores ultrasónicos','Contrato marco',3072619.73,'2026-04-14','2026-10-22','Fondo de Resiliencia y Modernización Operativa','Vigente'),
('INT-2026-0140',218,'Servicio integral de laboratorio','Invitación restringida',8315448.01,'2026-04-09','2026-11-19','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0141',195,'Limpieza de colectores metropolitanos','Licitación pública',1644191.89,'2026-04-24','2026-09-19','Fondo Metropolitano de Infraestructura Hídrica','Vigente'),
('INT-2026-0142',203,'Actualización de cartografía GIS','Licitación pública',1522560.25,'2026-05-18','2026-10-11','Programa de Agua Potable y Saneamiento','Vigente'),
('INT-2026-0143',181,'Servicios de telemetría y comunicaciones','Adjudicación directa',12501264.25,'2026-01-23','2026-11-14','Programa de Agua Potable y Saneamiento','Concluido');

INSERT INTO licitaciones (numero,tipo,objeto,area,origen_recurso,presupuesto,publicacion,visita,junta,apertura,fallo,estado,bases_archivo) VALUES
('LP-INT-026-2026','Obra pública','Rehabilitación integral de la línea de conducción Norte - Centro','Dirección de Operación Hidráulica','Fondo Metropolitano de Infraestructura Hídrica',48600000.00,'2026-09-15','2026-09-29','2026-10-02','2026-10-12','2026-10-19','Abierta','bases_LP-INT-026-2026.pdf'),
('LP-INT-027-2026','Obra pública','Construcción del tanque regulador RC-04 y obras de interconexión','Dirección de Operación Hidráulica','Programa de Agua Potable y Saneamiento',72800000.00,'2026-09-18','2026-10-01','2026-10-05','2026-10-15','2026-10-23','Abierta','bases_LP-INT-027-2026.pdf'),
('LP-INT-028-2026','Servicios','Modernización de estaciones remotas de telemetría fase III','Dirección de Tecnologías e Innovación','Fondo de Resiliencia y Modernización Operativa',18450000.00,'2026-09-20',NULL,'2026-10-03','2026-10-13','2026-10-20','Abierta','bases_LP-INT-028-2026.pdf'),
('LP-INT-029-2026','Adquisiciones','Suministro e instalación de 8,500 micromedidores','Dirección Comercial','Recursos propios INTERAFAS',23900000.00,'2026-09-21',NULL,'2026-10-06','2026-10-16','2026-10-24','Abierta','bases_LP-INT-029-2026.pdf'),
('LP-INT-024-2026','Obra pública','Rehabilitación electromecánica de la estación de bombeo EB-12','Dirección de Operación Hidráulica','Aportaciones intermunicipales',12600000.00,'2026-08-18','2026-08-29','2026-09-01','2026-09-10','2026-09-17','En fallo','bases_LP-INT-024-2026.pdf'),
('LP-INT-021-2026','Servicios','Servicio integral de análisis de calidad del agua 2026-2027','Dirección de Calidad y Saneamiento','Recursos propios INTERAFAS',8450000.00,'2026-07-22',NULL,'2026-08-04','2026-08-14','2026-08-21','Adjudicada','bases_LP-INT-021-2026.pdf');

UPDATE cuentas_servicio c
SET saldo = (SELECT COALESCE(SUM(f.saldo),0) FROM facturas f WHERE f.cuenta_id=c.id);


CREATE TABLE IF NOT EXISTS lecturas_ciudadanas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  cuenta_id INT NOT NULL,
  lectura DECIMAL(12,2) NOT NULL,
  fecha_captura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  foto VARCHAR(220) NULL,
  observaciones VARCHAR(255) NULL,
  estatus VARCHAR(50) NOT NULL DEFAULT 'En validación',
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (cuenta_id) REFERENCES cuentas_servicio(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS afectaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(180) NOT NULL,
  tipo VARCHAR(70) NOT NULL,
  estado VARCHAR(50) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  sector VARCHAR(100) NOT NULL,
  descripcion VARCHAR(320) NOT NULL,
  inicio DATETIME NOT NULL,
  fin_estimado DATETIME NULL,
  cuentas_estimadas INT NOT NULL DEFAULT 0,
  x_pct DECIMAL(5,2) NOT NULL,
  y_pct DECIMAL(5,2) NOT NULL,
  prioridad VARCHAR(30) NOT NULL DEFAULT 'Media',
  actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

UPDATE cuentas_servicio SET alias='Casa principal',sector='Centro Histórico' WHERE id=1;
UPDATE cuentas_servicio SET alias='Servicio residencial',sector='San José' WHERE id BETWEEN 2 AND 25;

INSERT INTO cuentas_servicio (usuario_id,cuenta,contrato,medidor,domicilio,colonia,municipio,codigo_postal,tipo_servicio,tarifa,fecha_alta,estatus,alias,sector,saldo,lectura_actual,fecha_lectura) VALUES
(1,'40010101','CTR-2026-00101','MTR-SA-3101','Priv. del Parque 24, Lomas del Río','Lomas del Río','Saint Louis','78218','Doméstico','Doméstica estándar','2021-04-12','Activo','Departamento familiar','Sector Poniente',284.70,884.2,'2026-09-14');

INSERT INTO consumos (cuenta_id,periodo,consumo_m3,lectura,promedio_zona_m3) VALUES
(26,'2026-04',13.8,820.4,15.2),(26,'2026-05',14.6,835.0,15.0),(26,'2026-06',12.9,847.9,14.8),(26,'2026-07',11.7,859.6,14.6),(26,'2026-08',12.8,872.4,14.9),(26,'2026-09',11.8,884.2,14.7);
INSERT INTO facturas (cuenta_id,folio,periodo,fecha_emision,fecha_limite,lectura_anterior,lectura_actual,consumo_m3,cargo_agua,cargo_saneamiento,otros,total,saldo,estado) VALUES
(26,'REC-2026-101-07','2026-07','2026-07-18','2026-08-05',847.9,859.6,11.7,168.40,54.30,0,222.70,0,'Pagada'),
(26,'REC-2026-101-08','2026-08','2026-08-18','2026-09-05',859.6,872.4,12.8,181.20,57.60,0,238.80,0,'Pagada'),
(26,'REC-2026-101-09','2026-09','2026-09-18','2026-10-05',872.4,884.2,11.8,171.90,55.80,57.00,284.70,284.70,'Pendiente');

INSERT INTO lecturas_ciudadanas (usuario_id,cuenta_id,lectura,fecha_captura,observaciones,estatus) VALUES
(1,1,1542.6,'2026-08-15 08:42:00','Lectura enviada desde Mi Portal.','Validada'),
(1,1,1567.3,'2026-09-15 08:31:00','Lectura enviada desde Mi Portal.','Validada'),
(1,26,884.2,'2026-09-14 19:06:00','Lectura enviada desde Mi Portal.','Validada');

INSERT INTO afectaciones (titulo,tipo,estado,municipio,sector,descripcion,inicio,fin_estimado,cuentas_estimadas,x_pct,y_pct,prioridad) VALUES
('Mantenimiento de válvula reguladora','Mantenimiento programado','En curso','Saint Louis','Sector Centro','Trabajos sobre válvula de regulación. Puede presentarse baja presión de forma intermitente.','2026-09-22 09:00:00','2026-09-22 18:30:00',1840,47,42,'Media'),
('Reparación de línea secundaria','Fuga','En atención','Saint Louis','Sector Poniente','Cuadrilla interviene una línea secundaria; el suministro se mantiene con presión reducida.','2026-09-22 12:10:00','2026-09-22 20:00:00',920,35,48,'Alta'),
('Lavado de tanque de almacenamiento','Mantenimiento programado','Programada','Cerro de San Pablo','Los Pinos','Mantenimiento sanitario del tanque TP-03. Se recomienda almacenar únicamente lo necesario.','2026-09-23 07:00:00','2026-09-23 15:00:00',1360,22,31,'Media'),
('Baja presión por maniobra operativa','Baja presión','Monitoreo','Soledade','San Felipe','Ajuste temporal de presión mientras se estabiliza la distribución del sector.','2026-09-22 14:30:00','2026-09-22 19:30:00',2480,72,54,'Media'),
('Rehabilitación de colector','Alcantarillado','En curso','Soledade','La Estación','Obra de rehabilitación con cierres parciales de vialidad; el servicio sanitario permanece disponible.','2026-09-20 08:00:00','2026-09-28 18:00:00',0,78,65,'Baja'),
('Interconexión de red nueva','Obra programada','Programada','Saint Louis','Sector Norte','Interconexión de red secundaria. Se prevé suspensión controlada durante cuatro horas.','2026-09-24 10:00:00','2026-09-24 14:00:00',3120,52,25,'Alta'),
('Fuga reparada en línea de distribución','Fuga','Concluida','Cerro de San Pablo','Vista Hermosa','Reparación concluida; la presión se recupera gradualmente en el sector.','2026-09-21 06:20:00','2026-09-21 13:50:00',740,18,55,'Baja'),
('Mantenimiento de estación de bombeo','Mantenimiento programado','Programada','Soledade','San José','Intervención electromecánica preventiva con respaldo operativo de equipo alterno.','2026-09-25 08:00:00','2026-09-25 17:00:00',1650,68,38,'Media');

UPDATE cuentas_servicio c
SET saldo = (SELECT COALESCE(SUM(f.saldo),0) FROM facturas f WHERE f.cuenta_id=c.id);


-- ===== Escenario vivo INTERAFAS / Pulso Metropolitano =====
CREATE TABLE IF NOT EXISTS scenario_state (
  id TINYINT PRIMARY KEY,
  phase TINYINT NOT NULL DEFAULT 0,
  phase_name VARCHAR(120) NOT NULL DEFAULT 'Operación normal',
  phase_started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_event_code VARCHAR(100) NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT IGNORE INTO scenario_state (id,phase,phase_name) VALUES (1,0,'Operación normal');

CREATE TABLE IF NOT EXISTS scenario_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_code VARCHAR(100) NOT NULL UNIQUE,
  flag_number INT NULL,
  source VARCHAR(80) NOT NULL DEFAULT 'lab',
  detail VARCHAR(300) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS news_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phase_required TINYINT NOT NULL,
  delay_seconds INT NOT NULL DEFAULT 0,
  category VARCHAR(50) NOT NULL,
  slug VARCHAR(140) UNIQUE NOT NULL,
  headline VARCHAR(240) NOT NULL,
  subheadline VARCHAR(320) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  author VARCHAR(120) NOT NULL,
  hero_asset VARCHAR(160) NOT NULL,
  priority INT NOT NULL DEFAULT 10,
  display_order INT NOT NULL DEFAULT 0,
  breaking TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS scenario_social_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phase_required TINYINT NOT NULL,
  delay_seconds INT NOT NULL DEFAULT 0,
  handle VARCHAR(80) NOT NULL,
  display_name VARCHAR(120) NOT NULL,
  municipality VARCHAR(100) NULL,
  label VARCHAR(80) NOT NULL,
  content VARCHAR(700) NOT NULL,
  likes INT NOT NULL DEFAULT 0,
  shares INT NOT NULL DEFAULT 0,
  party VARCHAR(20) NULL,
  avatar_url VARCHAR(255) NULL
);

DROP TRIGGER IF EXISTS trg_scenario_event;
DELIMITER //
CREATE TRIGGER trg_scenario_event AFTER INSERT ON scenario_events
FOR EACH ROW
BEGIN
  DECLARE target_phase TINYINT DEFAULT 0;
  DECLARE target_name VARCHAR(120) DEFAULT 'Operación normal';
  SET target_phase = CASE NEW.event_code
    WHEN 'FLAG_16' THEN 1
    WHEN 'FLAG_17' THEN 2
    WHEN 'FLAG_18' THEN 3
    WHEN 'FLAG_19' THEN 3
    WHEN 'FLAG_20' THEN 4
    WHEN 'RECOVERY_STARTED' THEN 5
    ELSE 0 END;
  SET target_name = CASE target_phase
    WHEN 1 THEN 'Afectaciones iniciales'
    WHEN 2 THEN 'Incidente tecnológico confirmado'
    WHEN 3 THEN 'Compromiso operacional bajo investigación'
    WHEN 4 THEN 'Impacto operacional mayor'
    WHEN 5 THEN 'Recuperación y seguimiento'
    ELSE 'Operación normal' END;
  IF target_phase > (SELECT phase FROM scenario_state WHERE id=1) THEN
    UPDATE scenario_state SET phase=target_phase, phase_name=target_name, phase_started_at=CURRENT_TIMESTAMP, last_event_code=NEW.event_code WHERE id=1;
  ELSE
    UPDATE scenario_state SET last_event_code=NEW.event_code WHERE id=1;
  END IF;
END//
DELIMITER ;

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,0,'local','interafas-refuerza-mantenimiento','INTERAFAS refuerza mantenimiento preventivo antes de temporada de mayor demanda','Cuadrillas metropolitanas ampliarán revisiones en estaciones de bombeo y redes principales.','INTERAFAS informó que durante las próximas semanas reforzará recorridos preventivos en estaciones de bombeo, líneas de conducción y sectores con mayor número de reportes. El organismo señaló que los trabajos buscan reducir interrupciones no programadas y mejorar los tiempos de atención.

La programación incluye inspecciones electromecánicas, revisión de válvulas y verificación de sistemas de telemetría. Las autoridades indicaron que los trabajos se realizarán por sectores para evitar afectaciones prolongadas.','Redacción','field-engineer.svg',30,0,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,0,'servicios','pago-digital-supera-mitad','Más de la mitad de los pagos de agua ya se realizan por canales digitales','El organismo reportó crecimiento sostenido del portal ciudadano y del recibo digital.','El uso de canales digitales para pago y consulta de recibos mantiene una tendencia ascendente en los tres municipios atendidos por INTERAFAS. Datos institucionales señalan que 57.8 por ciento de los pagos mensuales ya se efectúan por medios digitales.

El organismo prevé ampliar los servicios disponibles dentro del portal ciudadano, entre ellos constancias, seguimiento de reportes y consulta histórica de consumos.','Redacción','payment-online.svg',18,0,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,0,'politica','cabildos-revisan-plan-hidrico','Cabildos metropolitanos revisan avances del Plan de Desarrollo Hídrico','Representantes de los tres municipios analizaron metas de continuidad, saneamiento e inversión.','Los cabildos de Cerro de San Pablo, Saint Louis y Soledade recibieron un reporte de seguimiento del Plan Metropolitano de Desarrollo Hídrico 2022–2026. La sesión incluyó indicadores de cobertura, eficiencia física, saneamiento y modernización tecnológica.

Grupos de oposición solicitaron mayor detalle sobre ejecución presupuestal y resultados territoriales, mientras que representantes del gobierno defendieron el avance de las metas programadas.','Mesa política','hero-water.svg',12,0,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,0,'comunidad','vecinos-piden-reparacion-fugas','Vecinos piden acelerar reparación de fugas recurrentes en tres sectores','Usuarios señalan que algunas incidencias reaparecen semanas después de ser atendidas.','Habitantes de diversos sectores metropolitanos solicitaron a INTERAFAS reforzar el mantenimiento de redes secundarias donde se han presentado fugas recurrentes. El organismo informó que los reportes se encuentran integrados a su programa de renovación por sectores.','Comunidad','support-team.svg',10,0,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (1,0,'ultima','reportes-baja-presion-norte','Vecinos reportan baja presión en sectores del norte metropolitano','Los primeros avisos comenzaron durante la mañana; el organismo revisa estaciones de bombeo y telemetría.','Usuarios de colonias del norte de Saint Louis y Cerro de San Pablo reportaron variaciones de presión durante la mañana. INTERAFAS informó que personal operativo revisa el comportamiento de estaciones de bombeo y tanques de almacenamiento.

Hasta el momento no se ha informado de una interrupción generalizada. El organismo pidió a la población utilizar sus canales oficiales para registrar afectaciones y evitar información no confirmada.','Última hora','hero-water.svg',100,1,1);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (1,90,'servicios','intermitencias-portal-ciudadano','Portal ciudadano registra intermitencias en trámites y consultas','Usuarios reportaron sesiones cerradas y lentitud al consultar documentos.','El portal ciudadano de INTERAFAS presentó intermitencias durante la mañana. Algunos usuarios señalaron dificultades para consultar recibos, constancias y seguimiento de trámites.

El organismo informó que el servicio se encuentra bajo revisión técnica y que los canales telefónicos permanecen disponibles.','Redacción','support-team.svg',75,2,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (1,180,'comunidad','cinco-colonias-variaciones','Cinco colonias concentran reportes de variaciones en el suministro','Las afectaciones son distintas por sector y no implican un corte metropolitano.','Reportes ciudadanos recibidos durante las últimas horas se concentran en cinco colonias ubicadas en Saint Louis y Cerro de San Pablo. Las principales quejas están relacionadas con baja presión y recuperación lenta de niveles.

Personal de campo realiza maniobras operativas y verificaciones locales.','Comunidad','infrastructure-map.svg',60,3,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (1,300,'politica','oposicion-pide-informe-tecnico','Oposición pide informe técnico sobre las fallas reportadas','Representantes del FMC y ACC solicitaron explicar el origen de las intermitencias.','Representantes de los bloques Frente Metropolitano Cívico y Alianza Ciudadana del Centro solicitaron a INTERAFAS un reporte técnico sobre las variaciones de presión y las fallas registradas en servicios digitales.

El bloque MRP pidió esperar el diagnóstico del organismo antes de atribuir responsabilidades.','Mesa política','hero-water.svg',45,4,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (2,0,'ultima','interafas-confirma-incidente-tecnologico','INTERAFAS confirma incidente tecnológico y activa protocolo de contingencia','El organismo informó que equipos especializados revisan sistemas corporativos y de supervisión.','INTERAFAS confirmó que investiga un incidente tecnológico detectado durante la revisión de sus sistemas. La institución activó medidas de contingencia y reforzó la supervisión de servicios prioritarios.

De acuerdo con el comunicado, las áreas de operación, tecnologías, jurídico y comunicación trabajan de manera coordinada. La institución señaló que el abastecimiento continúa bajo seguimiento y que algunas plataformas digitales pueden permanecer limitadas de forma preventiva.','Última hora','operations-room.svg',120,1,1);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (2,75,'servicios','canales-alternos-atencion','Habilitan canales alternos de atención mientras continúa revisión técnica','Reportes y aclaraciones podrán realizarse temporalmente por teléfono y módulos presenciales.','INTERAFAS habilitó canales alternos de atención ante la restricción temporal de algunas funciones digitales. Los módulos presenciales y la línea metropolitana operarán con capacidad ampliada mientras continúan las revisiones.','Servicio','support-team.svg',70,2,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (2,180,'politica','alcaldes-mesa-coordinacion','Alcaldes instalan mesa de coordinación por incidente en INTERAFAS','Los tres gobiernos municipales señalaron que la prioridad es mantener la continuidad del servicio.','Los gobiernos de Cerro de San Pablo, Saint Louis y Soledade instalaron una mesa de coordinación para dar seguimiento al incidente tecnológico reportado por INTERAFAS.

El alcalde metropolitano Mateo Cárdenas indicó que las decisiones operativas serán tomadas con base en criterios técnicos. Representantes de oposición pidieron transparencia sobre el alcance del incidente y los controles existentes.','Mesa política','hero-water.svg',62,3,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (2,300,'opinion','incidente-prueba-resiliencia','El incidente que pone a prueba la resiliencia metropolitana','La continuidad de un servicio esencial depende tanto de tecnología como de procedimientos, comunicación y decisiones oportunas.','La atención de un incidente en una infraestructura de agua no puede reducirse a reiniciar servidores. La operación depende de personas, telecomunicaciones, proveedores, sistemas corporativos y tecnología operacional.

La pregunta central será si los mecanismos de coordinación permiten preservar el servicio mientras se determina el alcance técnico.','Mesa de análisis','operations-room.svg',35,4,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (3,0,'ultima','revision-sistemas-operacionales','INTERAFAS amplía revisión a sistemas vinculados con supervisión operacional','La institución mantiene controles de contingencia mientras especialistas analizan registros y accesos.','La revisión del incidente tecnológico se amplió a componentes vinculados con la supervisión operacional, confirmó INTERAFAS. El organismo indicó que la medida responde a evidencia técnica identificada durante el análisis.

Operadores mantienen vigilancia reforzada sobre niveles, caudales y presiones, mientras equipos técnicos preservan registros y verifican la integridad de sistemas relacionados.','Última hora','operations-room.svg',130,1,1);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (3,90,'servicios','maniobras-manuales-sectores','Operadores realizan maniobras locales y verificaciones manuales en sectores estratégicos','Las acciones buscan mantener estabilidad mientras continúa la revisión de sistemas de supervisión.','Personal operativo realiza verificaciones presenciales y maniobras locales en instalaciones seleccionadas. Fuentes del organismo señalaron que estas acciones forman parte de medidas preventivas para mantener la continuidad del servicio.','Servicio','field-engineer.svg',78,2,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (3,180,'comunidad','usuarios-reportan-presion-irregular','Aumentan reportes de presión irregular en zonas del corredor norte','Las quejas no son uniformes; algunas colonias mantienen servicio sin cambios.','La conversación ciudadana registra un incremento de reportes relacionados con presión irregular. INTERAFAS mantiene cuadrillas en campo y pidió evitar almacenamiento excesivo de agua, debido a que puede alterar la demanda en sectores con recuperación gradual.','Comunidad','infrastructure-map.svg',64,3,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (3,300,'politica','congreso-metropolitano-comparecencia','Bloques políticos plantean comparecencia técnica tras ampliarse el incidente','La propuesta busca conocer medidas de contención, continuidad y protección de información.','Representantes del FMC, ACC y VU solicitaron una comparecencia técnica de responsables de INTERAFAS. Legisladores del MRP señalaron que apoyarán una revisión una vez que concluya la etapa crítica de respuesta.

Los distintos bloques coincidieron en la necesidad de conocer el alcance y las medidas de continuidad, aunque discreparon sobre el momento adecuado para exigir responsabilidades.','Mesa política','hero-water.svg',50,4,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (4,0,'ultima','ataque-alcanzo-supervision','Ataque a INTERAFAS alcanzó funciones vinculadas con supervisión operacional','Un cambio no autorizado obligó a activar medidas de contingencia en una instalación metropolitana.','INTERAFAS confirmó que el incidente incluyó una acción no autorizada sobre una función del entorno de supervisión operacional. La institución indicó que los mecanismos de contingencia permitieron aislar el componente afectado y mantener el control del proceso.

La investigación preliminar analiza la secuencia desde los sistemas expuestos hacia componentes internos. El organismo evitó atribuir públicamente el incidente a un actor específico mientras continúan las diligencias técnicas.','Última hora','operations-room.svg',160,1,1);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (4,60,'servicios','baja-caudal-planta-norte','Caudal de Planta Norte cayó temporalmente antes de la recuperación operativa','La variación provocó baja presión en sectores dependientes de esa instalación.','Registros operativos muestran una disminución temporal del caudal asociado con Planta Norte. La variación fue compensada mediante maniobras de operación y apoyo desde otros sectores.

INTERAFAS informó que la presión comenzó a recuperarse de forma gradual y que mantiene monitoreo reforzado.','Servicio','field-engineer.svg',105,2,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (4,120,'comunidad','hospitales-servicios-prioritarios','Hospitales y servicios prioritarios activaron reservas preventivas durante la contingencia','Autoridades señalaron que no se registró desabasto generalizado en instalaciones críticas.','Hospitales y servicios de emergencia ubicados en zonas con variaciones de presión activaron protocolos preventivos de almacenamiento. Autoridades reportaron que no fue necesario suspender actividades críticas.','Comunidad','hero-water.svg',88,3,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (4,180,'politica','gobierno-anuncia-revision-ciberseguridad','Gobierno metropolitano anuncia revisión integral de ciberseguridad en infraestructura hídrica','La medida incluirá segmentación, accesos de terceros, telemetría y respuesta ante incidentes.','Los gobiernos metropolitanos anunciaron una revisión integral de los controles de ciberseguridad asociados con INTERAFAS. El ejercicio abarcará accesos remotos, proveedores, segmentación entre entornos, mecanismos de actualización y procedimientos de respuesta.

Los bloques de oposición solicitaron que los resultados sean presentados públicamente, mientras el gobierno señaló que primero se protegerá la información técnica sensible.','Mesa política','operations-room.svg',82,4,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (4,300,'opinion','lecciones-incidente-agua','Las lecciones que deja un incidente de agua conectado','La superficie de ataque no termina en el portal público y la recuperación exige coordinación entre TI y operación.','El incidente demuestra por qué la ciberseguridad de infraestructura crítica debe considerar simultáneamente tecnología de información, tecnología operacional, personas y proveedores.

Un control aparentemente menor puede adquirir otra dimensión cuando existe conectividad o dependencia entre sistemas. La recuperación deberá incluir no solo correcciones técnicas, sino cambios de gobernanza y ejercicios periódicos.','Mesa de análisis','operations-room.svg',55,5,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (5,0,'ultima','interafas-restablece-operacion','INTERAFAS reporta operación estabilizada y mantiene monitoreo reforzado','Servicios digitales regresarán gradualmente mientras continúa el análisis forense.','INTERAFAS informó que la operación hidráulica se encuentra estabilizada y que los servicios digitales serán restablecidos de forma gradual. El organismo mantiene monitoreo reforzado, validaciones manuales en instalaciones críticas y controles temporales de acceso.','Última hora','hero-water.svg',150,1,1);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (5,120,'servicios','portal-reabre-gradualmente','Portal ciudadano reabre funciones de consulta y pago de manera gradual','Algunas operaciones permanecerán limitadas mientras se completan validaciones de seguridad.','Las funciones principales del portal ciudadano comenzaron a restablecerse. INTERAFAS indicó que algunas operaciones administrativas seguirán sujetas a verificación adicional durante los próximos días.','Servicio','payment-online.svg',75,2,0);

INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (5,240,'politica','auditoria-plan-mejora','Mesa metropolitana acuerda auditoría y plan de mejora posterior al incidente','El seguimiento incluirá responsables, plazos y revisión de controles de terceros.','Representantes de los tres municipios acordaron integrar una auditoría posterior al incidente y un plan de mejora con responsables y fechas compromiso. El acuerdo contempla revisar controles técnicos, procesos de autorización, proveedores y capacidad de recuperación.','Mesa política','operations-room.svg',60,3,0);

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 0,0,'@luzm_17','Luz M.','Saint Louis','Servicio','¿Alguien sabe si la baja presión del Sector 14 ya fue reportada? Desde la mañana sale muy poca agua.',18,4,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=0 AND handle='@luzm_17' AND content='¿Alguien sabe si la baja presión del Sector 14 ya fue reportada? Desde la mañana sale muy poca agua.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 0,0,'@marco_rdz','Marco R.','Cerro de San Pablo','Informe 2026','El informe sí trae números: más telemetría, reparación de fugas y obra. No todo está resuelto, pero sí se ve avance.',146,41,'MRP' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=0 AND handle='@marco_rdz' AND content='El informe sí trae números: más telemetría, reparación de fugas y obra. No todo está resuelto, pero sí se ve avance.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 0,0,'@tono_colonia12','Toño C.','Soledade','Crítica','Mucho informe y mucha pantallita, pero acá seguimos esperando agua. Ya dejen de hacerse p#$% y vengan a la colonia 12.',204,73,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=0 AND handle='@tono_colonia12' AND content='Mucho informe y mucha pantallita, pero acá seguimos esperando agua. Ya dejen de hacerse p#$% y vengan a la colonia 12.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 0,0,'@vecina_norte','Laura N.','Cerro de San Pablo','Servicio','A mí sí me resolvieron el reporte hoy. Lo levanté anoche y la cuadrilla llegó antes de las nueve.',91,16,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=0 AND handle='@vecina_norte' AND content='A mí sí me resolvieron el reporte hoy. Lo levanté anoche y la cuadrilla llegó antes de las nueve.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 0,0,'@observador_metro','Observador Metro','Saint Louis','Política','El FMC cuestionó costos del programa de telemetría y el MRP defendió los resultados. Habrá que ver los datos completos.',67,22,'FMC' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=0 AND handle='@observador_metro' AND content='El FMC cuestionó costos del programa de telemetría y el MRP defendió los resultados. Habrá que ver los datos completos.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 0,0,'@agua_con_todos','Agua con Todos','Soledade','Cultura','La calculadora de consumo está útil. En mi casa sí nos pasábamos muchísimo sin darnos cuenta.',54,8,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=0 AND handle='@agua_con_todos' AND content='La calculadora de consumo está útil. En mi casa sí nos pasábamos muchísimo sin darnos cuenta.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 1,20,'@sector14sl','Sector 14 SL','Saint Louis','Servicio','Ya somos varios con baja presión. En algunas calles apenas está subiendo al tinaco.',88,31,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=1 AND handle='@sector14sl' AND content='Ya somos varios con baja presión. En algunas calles apenas está subiendo al tinaco.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 1,120,'@reporte_urbano','Reporte Urbano','Cerro de San Pablo','Servicio','El portal se cae cuando intento descargar la constancia. Por teléfono sí están tomando reportes.',73,19,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=1 AND handle='@reporte_urbano' AND content='El portal se cae cuando intento descargar la constancia. Por teléfono sí están tomando reportes.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 1,210,'@maria_soledade','María G.','Soledade','Servicio','Acá el servicio está normal. Ojalá no se extienda porque ya vi mucha gente llenando tambos de más.',39,6,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=1 AND handle='@maria_soledade' AND content='Acá el servicio está normal. Ojalá no se extienda porque ya vi mucha gente llenando tambos de más.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 1,330,'@frente_civico','Frente Cívico Local','Saint Louis','Política','El FMC pide que INTERAFAS informe qué está pasando y por qué coinciden fallas digitales con reportes de presión.',112,44,'FMC' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=1 AND handle='@frente_civico' AND content='El FMC pide que INTERAFAS informe qué está pasando y por qué coinciden fallas digitales con reportes de presión.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 2,20,'@interafas_info','INTERAFAS Info','Metropolitano','Aviso','Se activaron canales alternos de atención mientras continúan revisiones técnicas. Evite compartir datos personales fuera de canales oficiales.',310,122,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=2 AND handle='@interafas_info' AND content='Se activaron canales alternos de atención mientras continúan revisiones técnicas. Evite compartir datos personales fuera de canales oficiales.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 2,90,'@carmen_aguaviva','Carmen A.','Saint Louis','Política','Que investiguen bien y no empiecen con rumores. Si hay incidente, que expliquen con datos y mantengan el servicio.',81,17,'MRP' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=2 AND handle='@carmen_aguaviva' AND content='Que investiguen bien y no empiecen con rumores. Si hay incidente, que expliquen con datos y mantengan el servicio.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 2,180,'@critico_del_agua','Crítico del Agua','Soledade','Crítica','Ahora resulta que todo es “incidente tecnológico”. Espero que no salgan con que nadie sabía nada, porque eso sí sería una m#$%.',192,70,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=2 AND handle='@critico_del_agua' AND content='Ahora resulta que todo es “incidente tecnológico”. Espero que no salgan con que nadie sabía nada, porque eso sí sería una m#$%.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 2,300,'@vecinos_unidos','Vecinos Unidos','Cerro de San Pablo','Comunidad','En nuestra zona la presión se recuperó. Seguimos atentos a los avisos oficiales y al mapa de afectaciones.',66,12,'VU' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=2 AND handle='@vecinos_unidos' AND content='En nuestra zona la presión se recuperó. Seguimos atentos a los avisos oficiales y al mapa de afectaciones.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 3,15,'@turno_norte','Turno Norte','Saint Louis','Servicio','Hay cuadrillas entrando a instalaciones y más movimiento de lo normal. En la colonia seguimos con presión irregular.',127,39,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=3 AND handle='@turno_norte' AND content='Hay cuadrillas entrando a instalaciones y más movimiento de lo normal. En la colonia seguimos con presión irregular.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 3,100,'@alianza_centro','Alianza Centro','Metropolitano','Política','ACC solicitará una explicación técnica sobre el alcance del incidente y las medidas de continuidad.',105,31,'ACC' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=3 AND handle='@alianza_centro' AND content='ACC solicitará una explicación técnica sobre el alcance del incidente y las medidas de continuidad.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 3,190,'@no_me_cuenten','No Me Cuenten','Soledade','Crítica','Primero dijeron intermitencia, luego incidente y ahora revisión operacional. Está cab#$% que la información salga a cuenta gotas.',231,88,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=3 AND handle='@no_me_cuenten' AND content='Primero dijeron intermitencia, luego incidente y ahora revisión operacional. Está cab#$% que la información salga a cuenta gotas.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 3,310,'@ingenieria_urbana','Ingeniería Urbana','Saint Louis','Análisis','Si están haciendo verificaciones manuales, probablemente la prioridad sea preservar continuidad mientras delimitan el alcance.',74,25,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=3 AND handle='@ingenieria_urbana' AND content='Si están haciendo verificaciones manuales, probablemente la prioridad sea preservar continuidad mientras delimitan el alcance.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 4,10,'@pulso_servicios','Pulso Servicios','Metropolitano','Última hora','INTERAFAS confirma acción no autorizada sobre una función de supervisión operacional. Hay contingencia activa.',412,205,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=4 AND handle='@pulso_servicios' AND content='INTERAFAS confirma acción no autorizada sobre una función de supervisión operacional. Hay contingencia activa.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 4,70,'@hospital_norte','Comunidad Hospital Norte','Saint Louis','Comunidad','Se activaron reservas preventivas, pero el hospital sigue operando. Ojalá estabilicen pronto la presión.',184,51,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=4 AND handle='@hospital_norte' AND content='Se activaron reservas preventivas, pero el hospital sigue operando. Ojalá estabilicen pronto la presión.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 4,130,'@tono_colonia12','Toño C.','Soledade','Crítica','¿Ven? No era nomás “una fallita”. Qué ch#$% necesidad de esperar hasta que truene para revisar controles.',348,121,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=4 AND handle='@tono_colonia12' AND content='¿Ven? No era nomás “una fallita”. Qué ch#$% necesidad de esperar hasta que truene para revisar controles.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 4,190,'@resultados_mrp','Resultados Metropolitanos','Metropolitano','Política','El MRP respalda la revisión integral y pide que primero se estabilice el servicio antes de repartir culpas.',136,64,'MRP' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=4 AND handle='@resultados_mrp' AND content='El MRP respalda la revisión integral y pide que primero se estabilice el servicio antes de repartir culpas.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 4,250,'@fmc_metropolitano','FMC Metropolitano','Metropolitano','Política','El FMC solicitará auditoría independiente y publicación de un plan de corrección con fechas y responsables.',158,71,'FMC' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=4 AND handle='@fmc_metropolitano' AND content='El FMC solicitará auditoría independiente y publicación de un plan de corrección con fechas y responsables.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 4,340,'@vecina_norte','Laura N.','Cerro de San Pablo','Servicio','Ya regresó mejor la presión. Lo importante es que expliquen qué pasó y qué van a cambiar para que no vuelva a ocurrir.',203,42,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=4 AND handle='@vecina_norte' AND content='Ya regresó mejor la presión. Lo importante es que expliquen qué pasó y qué van a cambiar para que no vuelva a ocurrir.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 5,15,'@interafas_info','INTERAFAS Info','Metropolitano','Aviso','Operación estabilizada. El restablecimiento de servicios digitales será gradual y continuará el monitoreo reforzado.',521,233,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=5 AND handle='@interafas_info' AND content='Operación estabilizada. El restablecimiento de servicios digitales será gradual y continuará el monitoreo reforzado.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 5,110,'@agua_con_todos','Agua con Todos','Soledade','Comunidad','Qué bueno que se estabilizó. Ahora sí toca publicar mejoras concretas, no solo decir “ya quedó”.',151,29,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=5 AND handle='@agua_con_todos' AND content='Qué bueno que se estabilizó. Ahora sí toca publicar mejoras concretas, no solo decir “ya quedó”.');

INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party) SELECT 5,220,'@observador_metro','Observador Metro','Saint Louis','Política','Gobierno y oposición coinciden en una auditoría posterior. La discusión será qué resultados se hacen públicos y en qué plazo.',94,28,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE phase_required=5 AND handle='@observador_metro' AND content='Gobierno y oposición coinciden en una auditoría posterior. La discusión será qué resultados se hacen públicos y en qué plazo.');

UPDATE scenario_social_posts SET avatar_url = CASE MOD(id,12)
 WHEN 0 THEN 'https://randomuser.me/api/portraits/women/44.jpg'
 WHEN 1 THEN 'https://randomuser.me/api/portraits/men/32.jpg'
 WHEN 2 THEN 'https://randomuser.me/api/portraits/women/68.jpg'
 WHEN 3 THEN 'https://randomuser.me/api/portraits/men/75.jpg'
 WHEN 4 THEN 'https://randomuser.me/api/portraits/women/31.jpg'
 WHEN 5 THEN 'https://randomuser.me/api/portraits/men/46.jpg'
 WHEN 6 THEN 'https://randomuser.me/api/portraits/women/12.jpg'
 WHEN 7 THEN 'https://randomuser.me/api/portraits/men/52.jpg'
 WHEN 8 THEN 'https://randomuser.me/api/portraits/women/57.jpg'
 WHEN 9 THEN 'https://randomuser.me/api/portraits/men/18.jpg'
 WHEN 10 THEN 'https://randomuser.me/api/portraits/women/26.jpg'
 WHEN 11 THEN 'https://randomuser.me/api/portraits/men/64.jpg'
 END WHERE avatar_url IS NULL;

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
  attempt_token VARCHAR(96) NULL UNIQUE,
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
  source_system VARCHAR(40) NOT NULL DEFAULT 'auditor',
  severity VARCHAR(16) NOT NULL DEFAULT 'info',
  detail TEXT NULL,
  metadata_json LONGTEXT NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activity_attempt (attempt_id, created_at),
  INDEX idx_activity_source (attempt_id, source_system, created_at),
  CONSTRAINT fk_activity_attempt FOREIGN KEY (attempt_id) REFERENCES lab_attempts(id),
  CONSTRAINT fk_activity_student FOREIGN KEY (student_id) REFERENCES lab_students(id)
);
ALTER TABLE flag_submissions ADD COLUMN IF NOT EXISTS attempt_id INT NULL AFTER id;
ALTER TABLE flag_submissions ADD INDEX idx_flag_number_fk (flag_number);
ALTER TABLE flag_submissions DROP INDEX uq_flag_accepted;
ALTER TABLE flag_submissions ADD UNIQUE KEY uq_attempt_flag_status (attempt_id, flag_number, status);
ALTER TABLE flag_submissions ADD INDEX idx_flag_attempt (attempt_id, created_at);
ALTER TABLE scenario_events ADD COLUMN IF NOT EXISTS attempt_id INT NULL AFTER id;
ALTER TABLE scenario_events DROP INDEX event_code;
ALTER TABLE scenario_events ADD INDEX idx_scenario_attempt (attempt_id, created_at);
ALTER TABLE scenario_events ADD UNIQUE KEY uq_attempt_event (attempt_id, event_code);


-- NEWSROOM PRO V0.4.4
-- Pulso Metropolitano newsroom pro content
UPDATE news_articles SET hero_asset='photo-pumping', body='INTERAFAS inició un programa intensivo de mantenimiento preventivo en estaciones de bombeo, líneas primarias y tanques de regulación antes del periodo de mayor demanda. El organismo informó que las cuadrillas trabajarán por sectores para reducir la posibilidad de interrupciones no programadas y atender componentes que acumulan más horas de operación.

El programa contempla inspecciones electromecánicas, revisión de válvulas, pruebas de respaldo eléctrico y verificación de instrumentos de presión y caudal. Personal de operación señaló que la prioridad será intervenir equipos sin sacar de servicio más infraestructura de la necesaria, por lo que algunas maniobras se realizarán durante ventanas de bajo consumo.

Los trabajos se concentrarán inicialmente en el corredor norte y en instalaciones que alimentan zonas con crecimiento reciente. INTERAFAS indicó que los usuarios podrán consultar en el mapa de afectaciones los cierres programados y los tiempos estimados de normalización cuando una intervención requiera disminuir temporalmente la presión.

Especialistas consultados por Pulso Metropolitano señalaron que el mantenimiento preventivo es especialmente relevante en sistemas que combinan infraestructura hidráulica, telemetría y control remoto. Una falla de un componente físico puede generar efectos similares a un problema de supervisión, por lo que la confirmación en campo sigue siendo parte esencial de la operación.

El organismo adelantó que publicará un balance de los trabajos al cierre del periodo, con número de equipos intervenidos, kilómetros de red revisados y principales incidencias detectadas. La cobertura continuará con seguimiento a las zonas donde se programen maniobras.' WHERE slug='interafas-refuerza-mantenimiento';
UPDATE news_articles SET hero_asset='photo-control', body='Más de la mitad de los pagos mensuales de agua en la zona metropolitana se realizan ya mediante canales digitales, de acuerdo con cifras difundidas por INTERAFAS. El organismo ubicó la proporción en 57.8 por ciento y atribuyó el crecimiento al uso del portal ciudadano, domiciliación y referencias para transferencias.

El cambio también ha modificado la forma en que los usuarios consultan información de su servicio. Recibos, historial de consumo, constancias y seguimiento de trámites concentran una parte creciente de las consultas, mientras que los módulos presenciales mantienen mayor demanda para aclaraciones, cambios de titular y casos que requieren documentos físicos.

INTERAFAS señaló que el incremento de operaciones digitales obliga a mantener controles de disponibilidad y seguridad equivalentes a los de otros servicios críticos. La plataforma procesa información de cuentas, referencias de pago y documentos administrativos, por lo que las interrupciones tecnológicas pueden traducirse rápidamente en filas o retrasos en módulos.

Usuarios consultados por este medio destacaron la comodidad del pago en línea, aunque algunos señalaron que todavía recurren a sucursales cuando una transacción tarda en reflejarse. El organismo afirma que trabaja en conciliación automática y notificaciones para reducir ese tipo de incidencias.

La meta institucional es ampliar el uso digital sin eliminar alternativas presenciales. El siguiente reporte de desempeño incluirá tiempos promedio de reflejo de pago, disponibilidad del portal y proporción de trámites concluidos sin acudir a una oficina.' WHERE slug='pago-digital-supera-mitad';
UPDATE news_articles SET hero_asset='photo-reservoir', body='Los cabildos de Cerro de San Pablo, Saint Louis y Soledade recibieron el reporte de avance del Plan Metropolitano de Desarrollo Hídrico 2022–2026. La sesión conjunta revisó indicadores de cobertura, continuidad, saneamiento, eficiencia física e inversión en modernización tecnológica.

Entre los puntos discutidos estuvieron la reducción de pérdidas en red, la renovación de equipos de bombeo y la ampliación de telemetría en instalaciones críticas. Representantes municipales pidieron que los indicadores se presenten también por sector hidráulico para distinguir las diferencias entre zonas consolidadas y áreas de expansión.

El informe técnico señaló que la continuidad promedio se mantiene por encima de 23 horas diarias, aunque existen sectores donde la presión depende de horarios de bombeo y niveles de almacenamiento. Los cabildos solicitaron priorizar obras que reduzcan esas diferencias y que los avances se publiquen en formatos abiertos.

En materia de saneamiento se revisaron volúmenes tratados, capacidad de plantas y mantenimiento de colectores. También se planteó fortalecer la coordinación con protección civil y servicios urbanos para atender incidentes que afecten vialidades o instalaciones esenciales.

La mesa acordó una nueva revisión trimestral y pidió que el cierre del plan incluya metas alcanzadas, pendientes, inversión ejercida y proyectos que deberán continuar en la siguiente administración. Pulso Metropolitano dará seguimiento a los acuerdos y a la publicación de los anexos técnicos.' WHERE slug='cabildos-revisan-plan-hidrico';
UPDATE news_articles SET hero_asset='photo-break', body='Vecinos de tres sectores metropolitanos solicitaron acelerar la reparación definitiva de fugas que, según sus reportes, reaparecen semanas después de intervenciones correctivas. Las quejas se concentran en ramales secundarios con tubería de mayor antigüedad y en puntos donde el pavimento ha sido abierto en varias ocasiones.

Los habitantes señalan que el problema no siempre implica una gran salida de agua: en algunos casos comienza como humedad persistente, caída de presión o filtraciones que aumentan gradualmente. Esa condición dificulta la detección temprana y puede hacer que el reporte sea clasificado inicialmente como de baja prioridad.

INTERAFAS informó que las incidencias recurrentes se están integrando a un programa de renovación por sectores y no solo a reparaciones puntuales. El criterio, explicó, es sustituir tramos completos cuando el historial muestra múltiples fallas cercanas en una misma línea.

Especialistas en redes consultados por este medio señalaron que reparar únicamente el punto visible puede desplazar esfuerzos hacia un tramo debilitado contiguo. La presión, el material de la tubería y los movimientos del terreno son factores que deben considerarse antes de cerrar una intervención.

El organismo pidió conservar los números de reporte para identificar reincidencias y anunció que actualizará el mapa público de afectaciones cuando una reparación requiera cierre de válvulas o reducción temporal del servicio.' WHERE slug='vecinos-piden-reparacion-fugas';
UPDATE news_articles SET hero_asset='photo-reservoir', body='Usuarios de colonias del norte de Saint Louis y Cerro de San Pablo comenzaron a reportar baja presión desde las primeras horas de la mañana. Los avisos no describen un corte generalizado: algunos domicilios mantienen servicio con menor fuerza, mientras otros registran recuperación intermitente.

INTERAFAS informó inicialmente que personal operativo revisa niveles de tanques, comportamiento de estaciones de bombeo y datos de telemetría. Hasta el momento no se ha anunciado una suspensión programada que explique por sí sola la concentración de reportes.

En recorridos de Pulso Metropolitano se observaron cuadrillas en instalaciones del corredor norte y vehículos de operación entrando a puntos de control. El organismo pidió evitar el almacenamiento excesivo de agua mientras se determina la causa, debido a que un aumento abrupto de demanda puede retrasar la recuperación en los extremos de la red.

Comercios y viviendas ubicados en zonas altas son los que reportan con mayor frecuencia variaciones. Usuarios de sectores cercanos a los tanques principales señalaron que el suministro continúa, aunque con cambios de presión que comenzaron antes de las 09:00 horas.

La información continúa en desarrollo. Este medio mantiene seguimiento de los avisos oficiales, el mapa de afectaciones y los reportes ciudadanos para determinar si el comportamiento permanece localizado o se extiende a más sectores.' WHERE slug='reportes-baja-presion-norte';
UPDATE news_articles SET hero_asset='photo-control', body='El portal ciudadano de INTERAFAS registró intermitencias durante la mañana, al mismo tiempo que usuarios reportaban variaciones de presión en algunos sectores. Las fallas digitales incluyen sesiones que se cierran de manera inesperada, lentitud al consultar documentos y tiempos de espera superiores a lo habitual.

La institución informó que el área tecnológica revisa la plataforma y que, mientras se estabiliza, los pagos ya realizados conservarán sus referencias. También recomendó no repetir una operación si el cargo aparece en la aplicación bancaria pero el comprobante del portal tarda en generarse.

En módulos presenciales se observó un incremento moderado de usuarios que buscaban obtener constancias o confirmar pagos. Personal de atención indicó que los trámites pueden continuar por canales alternos, aunque algunos procesos dependen de validaciones internas y podrían tardar más.

Por ahora no existe confirmación pública de que la falla del portal y las variaciones hidráulicas tengan un origen común. INTERAFAS sostiene que ambos frentes están siendo revisados de forma simultánea por equipos distintos.

Pulso Metropolitano solicitó información sobre disponibilidad de servicios, hora de inicio de la incidencia y alcance de la revisión técnica. La nota será actualizada cuando exista una explicación confirmada.' WHERE slug='intermitencias-portal-ciudadano';
UPDATE news_articles SET hero_asset='photo-tanker', body='Cinco colonias concentran hasta ahora la mayor parte de los reportes ciudadanos por variaciones en el suministro. Los avisos describen baja presión, recuperación lenta después de periodos de mayor consumo y diferencias entre calles de un mismo sector.

INTERAFAS aclaró que no existe un corte metropolitano y que los niveles de afectación son distintos según la elevación y la configuración de la red. Cuadrillas verifican válvulas, estaciones y puntos de presión para descartar una avería física de gran escala.

En dos de las colonias, pequeños comercios comenzaron a almacenar agua para actividades básicas. Autoridades pidieron hacerlo de manera moderada y evitar compras de pánico, pues el servicio continúa y las reservas institucionales se mantienen disponibles para contingencias prioritarias.

La red de hospitales no ha reportado afectaciones operativas en esta etapa. Protección Civil mantiene contacto con instalaciones que dependen de abastecimiento continuo y pidió reportar cualquier caída sostenida de presión mediante los canales oficiales.

El mapa de afectaciones será actualizado conforme se validen los avisos. Pulso Metropolitano mantendrá una lista de zonas confirmadas y diferenciará los reportes ciudadanos de las afectaciones reconocidas oficialmente.' WHERE slug='cinco-colonias-variaciones';
UPDATE news_articles SET hero_asset='photo-control', body='Representantes del Frente Metropolitano Cívico y de la Alianza Ciudadana del Centro solicitaron a INTERAFAS un informe técnico sobre las variaciones de presión y las intermitencias digitales registradas durante la mañana. Los bloques pidieron conocer si existe relación entre ambos eventos y qué medidas de continuidad fueron activadas.

Los representantes señalaron que la prioridad debe ser mantener el servicio y evitar especulaciones, pero consideraron necesario documentar la secuencia de fallas. También solicitaron información sobre proveedores tecnológicos, protocolos de contingencia y capacidad de operación manual.

Integrantes del Movimiento Regional Progresista respondieron que cualquier revisión debe realizarse sin obstaculizar las tareas técnicas en curso y pidieron esperar a que el organismo confirme la causa. Coincidieron, sin embargo, en que habrá que presentar un balance una vez estabilizada la situación.

INTERAFAS no ha atribuido hasta ahora las incidencias a una causa específica. La institución mantiene personal de operación y tecnología revisando sistemas y ha informado que emitirá actualizaciones cuando existan datos validados.

La discusión se trasladará a la mesa metropolitana de servicios públicos si las afectaciones continúan. Este medio dará seguimiento a las solicitudes formales y a cualquier comparecencia técnica que sea convocada.' WHERE slug='oposicion-pide-informe-tecnico';
UPDATE news_articles SET hero_asset='photo-control', body='INTERAFAS confirmó que investiga un incidente tecnológico detectado durante la revisión de sus sistemas y activó su protocolo de contingencia. La institución informó que equipos especializados trabajan sobre plataformas corporativas y componentes utilizados para supervisar la operación.

El organismo no detalló el vector de acceso ni atribuyó el incidente a un actor. Señaló que la prioridad inmediata es mantener la continuidad del servicio, preservar registros técnicos y limitar cualquier acceso que no sea indispensable mientras se desarrolla el análisis.

Parte de los servicios digitales permanece restringida o intermitente. Atención ciudadana habilitó canales alternos para reportes, pagos y aclaraciones, mientras que en instalaciones operativas se reforzaron verificaciones presenciales de niveles, presión y estado de equipos.

Fuentes familiarizadas con la respuesta señalaron a Pulso Metropolitano que la investigación busca establecer si las anomalías observadas durante la mañana forman parte de un mismo evento. Esa relación todavía no ha sido confirmada públicamente.

Los tres municipios fueron notificados y preparan una mesa de coordinación. INTERAFAS anunció que emitirá un nuevo parte cuando termine la primera etapa de contención y existan elementos suficientes para precisar el alcance.' WHERE slug='interafas-confirma-incidente-tecnologico';
UPDATE news_articles SET hero_asset='photo-distribution', body='INTERAFAS habilitó canales alternos de atención mientras algunas funciones del portal ciudadano permanecen limitadas por la revisión técnica. Los reportes de fugas, falta de agua y aclaraciones pueden realizarse por teléfono y en módulos presenciales con capacidad ampliada.

La institución pidió a los usuarios conservar referencias bancarias y evitar repetir pagos cuando exista evidencia de cargo. Las operaciones pendientes serán conciliadas una vez que los sistemas administrativos recuperen su funcionamiento normal.

En los principales módulos metropolitanos se asignó personal adicional para orientar a usuarios que requieren constancias o seguimiento de trámites. Algunos documentos que dependen de consultas internas podrían entregarse posteriormente mediante correo o notificación en el portal.

El organismo señaló que la atención prioritaria se concentra en reportes relacionados con continuidad del servicio, hospitales, escuelas y usuarios vulnerables. Las consultas no urgentes pueden presentar tiempos de respuesta mayores durante la contingencia.

Los canales alternos permanecerán activos hasta que las funciones digitales sean validadas. Pulso Metropolitano actualizará la lista de servicios disponibles conforme se restablezcan.' WHERE slug='canales-alternos-atencion';
UPDATE news_articles SET hero_asset='photo-reservoir', body='Los gobiernos de Cerro de San Pablo, Saint Louis y Soledade instalaron una mesa de coordinación para dar seguimiento al incidente tecnológico reportado por INTERAFAS. Los tres gobiernos señalaron que la prioridad es preservar la continuidad del servicio y apoyar la respuesta técnica.

La mesa recibe reportes sobre presión, niveles de almacenamiento, operación de plantas, atención ciudadana y estado de sistemas digitales. También participan enlaces de protección civil y comunicación social para mantener criterios comunes frente a información no confirmada.

Autoridades pidieron evitar atribuciones sobre el origen del incidente hasta que concluya la revisión forense. Señalaron que cualquier declaración sobre responsabilidades deberá sustentarse en evidencia técnica y, en su caso, ser canalizada a las instancias competentes.

Los municipios prepararon recursos logísticos de respaldo para servicios prioritarios, aunque hasta ahora no se ha informado de un desabasto generalizado. Las reservas y rutas de distribución permanecerán en condición preventiva mientras continúe la contingencia.

La mesa acordó emitir cortes de información coordinados y revisar al final del evento los procedimientos de continuidad y respuesta. La siguiente actualización se espera una vez que INTERAFAS complete la contención inicial.' WHERE slug='alcaldes-mesa-coordinacion';
UPDATE news_articles SET hero_asset='photo-scada', body='La atención de un incidente tecnológico en una infraestructura de agua no puede reducirse a reiniciar servidores. La continuidad depende de personas, telecomunicaciones, sistemas corporativos, tecnología operacional, proveedores y procedimientos que deben funcionar incluso cuando una parte del entorno digital deja de ser confiable.

La primera prueba de resiliencia ocurre cuando una organización puede distinguir entre una falla habitual y un evento de seguridad. Una caída de presión puede originarse en una bomba, una válvula, un sensor, una comunicación o una acción no autorizada. La respuesta exige confirmar el proceso físico y no confiar únicamente en una pantalla.

El segundo reto es mantener operación y evidencia al mismo tiempo. Aislar sistemas con demasiada rapidez puede interrumpir servicios esenciales; mantenerlos conectados sin controles puede ampliar el incidente. Esa tensión obliga a trabajar con procedimientos definidos antes de la crisis y con responsables que sepan quién puede autorizar cada acción.

La comunicación es otro componente técnico de la resiliencia. Cuando ciudadanos, personal de campo y autoridades reciben mensajes contradictorios, aumenta la presión sobre los canales de atención y se vuelve más difícil separar un reporte real de un rumor. Informar con precisión también forma parte de la respuesta.

Lo ocurrido en INTERAFAS será una prueba de qué tan bien se conectan esas capacidades. El resultado no dependerá solo de encontrar la causa, sino de preservar el servicio, recuperar sistemas de forma segura y convertir la evidencia obtenida en mejoras concretas.' WHERE slug='incidente-prueba-resiliencia';
UPDATE news_articles SET hero_asset='photo-scada', body='INTERAFAS amplió la revisión del incidente a componentes vinculados con la supervisión operacional después de identificar evidencia técnica que requiere comprobar accesos y cambios registrados en sistemas internos. La institución mantiene activos controles de contingencia y monitoreo reforzado.

La revisión incluye estaciones de supervisión, gateways de telemetría, registros de autenticación y comunicaciones entre sistemas corporativos y operativos. El organismo no ha informado de daños físicos y señaló que las variables críticas son verificadas también mediante personal en campo.

Operadores fueron instruidos para confirmar localmente estados de bombas, válvulas y niveles cuando exista una discrepancia entre la información mostrada por los sistemas y las condiciones observadas en sitio. Algunas funciones remotas permanecen limitadas mientras concluye la validación.

Especialistas consultados explicaron que la ampliación de alcance no significa por sí misma que exista control externo de equipos. En una investigación de este tipo, revisar los límites entre redes y los privilegios de cuentas es parte de la tarea para determinar hasta dónde pudo llegar una actividad no autorizada.

La institución emitirá un nuevo parte cuando pueda establecer si los accesos observados tuvieron efecto operacional. Pulso Metropolitano mantiene abierta la cobertura y separará los hechos confirmados de las hipótesis técnicas.' WHERE slug='revision-sistemas-operacionales';
UPDATE news_articles SET hero_asset='photo-pumping', body='Personal operativo de INTERAFAS realiza verificaciones presenciales y maniobras locales en instalaciones seleccionadas mientras continúa la revisión de sistemas de supervisión. Las acciones buscan mantener estabilidad sin depender exclusivamente de instrucciones remotas.

En estaciones del corredor norte se reforzaron rondines, lectura directa de instrumentos y confirmación de estados de bombas y válvulas. Técnicos también revisan que los niveles de tanques correspondan con los valores registrados por telemetría.

El organismo señaló que estas medidas forman parte de un esquema de continuidad y no implican que toda la infraestructura opere de manera manual. Los sistemas que han sido validados continúan en servicio, mientras los componentes bajo revisión se mantienen con restricciones adicionales.

Las maniobras pueden producir cambios temporales de presión entre sectores, especialmente durante redistribuciones de caudal. Atención ciudadana pidió reportar únicamente afectaciones sostenidas para facilitar la priorización de cuadrillas.

INTERAFAS no ha establecido un horario para levantar todas las restricciones. La transición hacia operación normal dependerá de pruebas técnicas y de la confirmación de que los accesos utilizados durante el incidente han sido contenidos.' WHERE slug='maniobras-manuales-sectores';
UPDATE news_articles SET hero_asset='photo-tanker', body='La conversación ciudadana registra un incremento de reportes de presión irregular en zonas del corredor norte. Las quejas no son uniformes: algunas calles mantienen niveles normales mientras otras describen disminuciones durante periodos de mayor demanda.

INTERAFAS relacionó parte de las variaciones con maniobras preventivas y verificaciones locales que se realizan durante la revisión de sistemas de supervisión. El organismo insiste en que no existe un corte generalizado y que los tanques mantienen reservas operativas.

En comercios y viviendas, los usuarios comenzaron a ajustar actividades que requieren mayor volumen de agua. Protección Civil pidió no almacenar cantidades excesivas y conservar recipientes cerrados cuando sea necesario contar con una reserva doméstica.

Los reportes con mayor persistencia se concentran en sectores altos y extremos de red. Cuadrillas realizan mediciones de presión para distinguir efectos de las maniobras de otros problemas, como fugas o válvulas que no recuperaron completamente su posición.

Este medio continuará cruzando reportes vecinales con el mapa oficial de afectaciones. Las zonas se considerarán confirmadas únicamente cuando exista validación operativa o un aviso institucional.' WHERE slug='usuarios-reportan-presion-irregular';
UPDATE news_articles SET hero_asset='photo-control', body='Representantes del FMC, ACC y VU plantearon convocar a responsables técnicos de INTERAFAS a una comparecencia una vez que la fase de contención permita presentar información verificable. La propuesta se produjo después de que la institución ampliara su revisión a sistemas vinculados con supervisión operacional.

Los bloques solicitaron conocer la arquitectura general de continuidad, los controles sobre cuentas privilegiadas y la forma en que se separan los servicios administrativos de los sistemas que apoyan la operación. También pidieron un inventario de proveedores con acceso remoto.

Legisladores del MRP señalaron que apoyarán una revisión técnica, pero consideraron inconveniente exigir detalles operativos mientras el incidente sigue activo. Argumentaron que cierta información podría interferir con las tareas de contención o exponer configuraciones que todavía están siendo revisadas.

La mesa directiva acordó recibir primero un informe preliminar reservado y posteriormente definir el formato de una sesión pública. No se ha establecido fecha y cualquier comparecencia dependerá de la evolución de la contingencia.

Pulso Metropolitano dará seguimiento a los documentos que se hagan públicos y a las medidas que se propongan después de la investigación, sin anticipar conclusiones sobre responsabilidad mientras no exista evidencia final.' WHERE slug='congreso-metropolitano-comparecencia';
UPDATE news_articles SET hero_asset='photo-scada', body='INTERAFAS confirmó que el incidente alcanzó funciones vinculadas con la supervisión operacional y que se registró al menos un cambio no autorizado que obligó a activar medidas de contingencia en una instalación metropolitana. La institución señaló que el evento fue contenido y que no existe evidencia de daño físico.

El cambio coincidió con una variación temporal de caudal y presión en sectores abastecidos desde la Planta Norte. Operadores confirmaron la condición mediante instrumentos locales y ejecutaron maniobras para estabilizar el proceso, mientras el acceso remoto relacionado con el evento fue restringido.

La investigación se concentra ahora en reconstruir la secuencia de accesos, determinar qué credenciales o rutas fueron utilizadas y establecer si existieron modificaciones previas que no produjeron un efecto visible. Los registros digitales están siendo preservados como parte del análisis forense.

Autoridades metropolitanas indicaron que servicios prioritarios fueron notificados y que se activaron medidas preventivas de reserva. No se reportó desabasto generalizado, aunque algunas zonas experimentaron baja presión durante el periodo de recuperación.

INTERAFAS anunció que mantendrá operación reforzada y limitará funciones remotas hasta concluir nuevas validaciones. La cobertura permanece abierta porque el alcance total del incidente todavía está bajo investigación.' WHERE slug='ataque-alcanzo-supervision';
UPDATE news_articles SET hero_asset='photo-pumping', body='Registros operativos revisados durante la contingencia muestran una disminución temporal del caudal proveniente de la Planta Norte antes de que los operadores estabilizaran la instalación. El cambio se reflejó posteriormente en presión más baja en sectores dependientes de esa línea de alimentación.

INTERAFAS informó que la variación estuvo asociada con el evento operacional que forma parte de la investigación de ciberseguridad. Técnicos confirmaron localmente el estado de equipos y restablecieron condiciones utilizando procedimientos de contingencia.

La reducción no afectó de la misma manera a toda la zona. Sectores con almacenamiento cercano mantuvieron servicio, mientras áreas elevadas y extremos de red registraron una recuperación más lenta. El organismo utilizó redistribución de caudal para reducir el impacto.

Personal de campo mantuvo vigilancia sobre presión y niveles después de recuperar la bomba principal. Las alarmas se conservaron en los registros para que el equipo forense pueda comparar la secuencia operacional con los eventos registrados en sistemas digitales.

El organismo no ha publicado todavía todos los valores de la serie temporal. Señaló que los datos formarán parte del informe técnico posterior al incidente y que la prioridad actual continúa siendo la estabilización.' WHERE slug='baja-caudal-planta-norte';
UPDATE news_articles SET hero_asset='photo-distribution', body='Hospitales, centros asistenciales y otros servicios prioritarios activaron reservas preventivas durante la contingencia de INTERAFAS, aunque autoridades señalaron que no se registró un desabasto generalizado en instalaciones críticas.

Los protocolos incluyen verificación de cisternas, revisión de autonomía y contacto directo con las áreas municipales de protección civil. En caso de ser necesario, existe capacidad para movilizar abastecimiento mediante pipas hacia instalaciones que no puedan esperar a la recuperación de presión.

Dos hospitales del corredor norte reportaron disminuciones temporales en la presión de entrada, pero continuaron operando con almacenamiento propio. Los responsables fueron instruidos para informar cambios en su autonomía y priorizar consumos esenciales.

Autoridades pidieron a la población no bloquear rutas de abastecimiento ni realizar compras de pánico. La disponibilidad preventiva de vehículos y agua embotellada se mantiene como respaldo y no como señal de un corte general.

La condición de los servicios prioritarios seguirá siendo uno de los indicadores utilizados para decidir cuándo puede declararse superada la fase de contingencia.' WHERE slug='hospitales-servicios-prioritarios';
UPDATE news_articles SET hero_asset='photo-control', body='Los gobiernos metropolitanos anunciaron una revisión integral de ciberseguridad en infraestructura hídrica después de confirmarse que el incidente de INTERAFAS alcanzó funciones relacionadas con supervisión operacional. La revisión abarcará controles técnicos, procedimientos y accesos de terceros.

Entre los temas anunciados están segmentación entre redes corporativas y operativas, administración de cuentas privilegiadas, acceso remoto de proveedores, respaldo de configuraciones, monitoreo de telemetría y procedimientos de respuesta ante incidentes.

Las autoridades señalaron que la revisión no sustituirá la investigación forense actualmente en curso. Primero se busca preservar evidencia y establecer la secuencia del incidente; después se determinarán medidas correctivas y plazos de implementación.

Representantes de distintos bloques políticos coincidieron en la necesidad de una auditoría, aunque difieren en el momento y el nivel de información que debe hacerse público. La mesa metropolitana anunció que habrá una versión técnica y una síntesis ciudadana.

INTERAFAS mantendrá restricciones temporales sobre funciones remotas hasta que los controles sean validados. El programa de mejora será incorporado al seguimiento posterior del incidente.' WHERE slug='gobierno-anuncia-revision-ciberseguridad';
UPDATE news_articles SET hero_asset='photo-scada', body='El incidente de INTERAFAS demuestra que la superficie de ataque de un organismo de agua no termina en su portal público. Cuentas de usuario, documentos, aplicaciones administrativas, accesos de proveedores y sistemas de supervisión forman parte de una cadena que debe analizarse como un conjunto.

La primera lección es que la segmentación técnica solo funciona cuando también existe segmentación de identidades y privilegios. Una red separada pierde valor si una misma cuenta, servicio o ruta administrativa permite atravesar controles sin una validación adicional.

La segunda es que la respuesta debe integrar a tecnología y operación. Una alarma digital necesita confirmación física; una maniobra hidráulica puede cambiar la evidencia que un analista intenta preservar. Equipos que trabajan por separado pueden tomar decisiones correctas de manera individual y aun así producir un mal resultado colectivo.

La tercera es que la recuperación no consiste únicamente en volver a encender funciones. Es necesario saber desde qué estado se recupera, validar configuraciones, rotar credenciales, revisar persistencia y vigilar si el comportamiento vuelve a desviarse.

El informe posterior será tan importante como la contención. La capacidad de convertir una secuencia de fallas en controles, responsables y fechas concretas determinará si la organización aprende del incidente o simplemente vuelve a su estado anterior.' WHERE slug='lecciones-incidente-agua';
UPDATE news_articles SET hero_asset='photo-reservoir', body='INTERAFAS informó que la operación hidráulica se encuentra estabilizada después de las medidas de contingencia aplicadas durante el incidente tecnológico. Los principales indicadores de caudal, presión y almacenamiento se mantienen dentro de rangos operativos bajo monitoreo reforzado.

La institución señaló que la estabilización no significa que la investigación haya concluido. Equipos forenses continúan revisando registros, accesos y configuraciones para reconstruir la secuencia completa y confirmar que no permanezcan mecanismos de acceso no autorizados.

Las funciones remotas se están habilitando de manera gradual después de pruebas específicas. Algunas operaciones continúan sujetas a doble validación o confirmación presencial, especialmente en instalaciones que participaron en la contingencia.

Atención ciudadana reportó una disminución de quejas por presión irregular y mantiene abiertos los casos que requieren verificación en campo. Los servicios prioritarios regresan progresivamente a sus protocolos habituales de reserva.

INTERAFAS preparará un informe de cierre técnico y un plan de mejora. Mientras tanto, el escenario se mantiene en fase de recuperación y cualquier nueva anomalía será tratada con los controles reforzados implementados durante la respuesta.' WHERE slug='interafas-restablece-operacion';
UPDATE news_articles SET hero_asset='photo-control', body='Las principales funciones del portal ciudadano comenzaron a reabrirse de manera gradual después de validaciones de seguridad realizadas durante la fase de recuperación. Consulta de recibos y pagos se encuentra disponible para parte de los usuarios, mientras otros trámites permanecen limitados.

INTERAFAS indicó que la restauración se realiza por módulos para evitar reactivar dependencias que todavía están bajo revisión. Antes de habilitar cada función se verifican credenciales de servicio, registros de acceso y consistencia de información.

Los pagos efectuados durante la contingencia serán conciliados con las referencias bancarias y los usuarios no deberán repetirlos si ya cuentan con comprobante de cargo. Atención ciudadana mantiene canales alternos para aclaraciones mientras se completa el proceso.

El organismo también está forzando nuevas sesiones en algunos servicios y revisando permisos asociados con cuentas administrativas. Estas medidas pueden producir cierres de sesión adicionales, pero forman parte del proceso de recuperación controlada.

La institución no ha dado una fecha única para restablecer el cien por ciento de las funciones. Cada módulo será habilitado cuando cumpla los criterios técnicos definidos por el equipo de recuperación.' WHERE slug='portal-reabre-gradualmente';
UPDATE news_articles SET hero_asset='photo-pumping', body='La mesa metropolitana acordó realizar una auditoría posterior al incidente y construir un plan de mejora con responsables, plazos y mecanismos de seguimiento. El acuerdo se tomó una vez que INTERAFAS reportó estabilizada la operación hidráulica y comenzó la recuperación gradual de servicios digitales.

La auditoría revisará controles de acceso, segmentación, administración de proveedores, respaldo de configuraciones, monitoreo y respuesta. También analizará si las medidas previstas en los procedimientos existentes fueron suficientes durante las primeras horas de la contingencia.

Los representantes municipales solicitaron que el plan distinga acciones inmediatas de proyectos estructurales. Cambios de credenciales, restricciones de acceso y reglas de monitoreo pueden implementarse en días, mientras que rediseños de arquitectura o renovación de equipos requieren presupuesto y planificación.

INTERAFAS deberá presentar evidencia de cierre para cada acción y establecer indicadores que permitan medir si el riesgo disminuye. La mesa también pidió ejercicios periódicos que involucren a operación, tecnología, comunicación y protección civil.

Una versión pública del informe se difundirá una vez que concluya el análisis forense y se retiren datos cuya exposición pueda crear nuevos riesgos. Pulso Metropolitano continuará el seguimiento de los compromisos y sus fechas de cumplimiento.' WHERE slug='auditoria-plan-mejora';
ALTER TABLE news_articles ADD COLUMN IF NOT EXISTS event_required VARCHAR(100) NULL AFTER phase_required;
ALTER TABLE scenario_social_posts ADD COLUMN IF NOT EXISTS event_required VARCHAR(100) NULL AFTER phase_required;
CREATE INDEX IF NOT EXISTS idx_news_event_required ON news_articles(event_required);
CREATE INDEX IF NOT EXISTS idx_social_event_required ON scenario_social_posts(event_required);

-- Diversidad visual para el archivo ya existente.
UPDATE news_articles SET hero_asset = CASE MOD(id,13)
  WHEN 0 THEN 'photo-pumping'
  WHEN 1 THEN 'photo-reservoir'
  WHEN 2 THEN 'photo-scada'
  WHEN 3 THEN 'photo-control'
  WHEN 4 THEN 'photo-tanker'
  WHEN 5 THEN 'photo-break'
  WHEN 6 THEN 'photo-distribution'
  WHEN 7 THEN 'photo-cyber'
  WHEN 8 THEN 'photo-ambulance'
  WHEN 9 THEN 'photo-evidence'
  WHEN 10 THEN 'photo-flood'
  WHEN 11 THEN 'photo-microphone'
  ELSE 'photo-press' END;

-- FLAG 16: primera presión pública + voz operativa.
INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking)
VALUES
(1,'FLAG_16',0,'servicios','operacion-hidraulica-explica-baja-presion','Operación Hidráulica reconoce anomalías: “No estamos ante una falla ordinaria”','Elena Marín Soler, directora de Operación Hidráulica, confirma variaciones atípicas y despliegue de cuadrillas en el corredor norte.',
'La primera explicación técnica de INTERAFAS llegó después de que decenas de usuarios reportaran baja presión casi al mismo tiempo en sectores que normalmente no comparten una misma incidencia. La directora de Operación Hidráulica, Elena Marín Soler, confirmó que el patrón obligó a ampliar la revisión más allá de una avería convencional.\n\n“Hay comportamientos que no corresponden a una fuga aislada ni a un mantenimiento programado. Estamos verificando bombeo, niveles de tanque y telemetría de manera simultánea”, señaló Marín Soler en entrevista con Pulso Metropolitano.\n\nLa funcionaria indicó que las cuadrillas recibieron instrucciones de validar físicamente estaciones y válvulas críticas, incluso cuando el centro de monitoreo todavía mostraba variables dentro de rangos aparentemente aceptables. Esa discrepancia es uno de los puntos que más preocupa al equipo operativo.\n\nPreguntada sobre si existía riesgo de desabasto, respondió que el sistema mantenía reservas, pero evitó garantizar que la presión pudiera sostenerse sin nuevas maniobras. “No sería responsable prometer normalidad mientras seguimos descartando escenarios”, dijo.\n\nINTERAFAS pidió a los usuarios de las zonas afectadas moderar temporalmente consumos no esenciales y consultar el mapa de afectaciones. La institución no ha informado todavía una causa definitiva.',
'Mariana Solís · Unidad de Investigación','elena-marin.svg',96,0,1);

-- FLAG 17: confirmación tecnológica + entrevista con TI.
INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking)
VALUES
(2,'FLAG_17',0,'ultima','tecnologias-admite-acceso-no-previsto','Tecnologías admite actividad no prevista en sistemas internos; “el alcance aún no está cerrado”','Laura Bianchi Romero confirma que el incidente dejó de tratarse como una simple indisponibilidad del portal.',
'INTERAFAS confirmó que la investigación interna detectó actividad no prevista en sistemas que no deberían haber sido alcanzados desde el entorno público. La directora de Tecnologías e Innovación, Laura Bianchi Romero, dijo que el organismo activó procedimientos de contención y preservación de evidencia.\n\n“En este momento no podemos sostener que se trate solamente de una falla de disponibilidad. Hay indicadores suficientes para analizar un acceso no autorizado”, afirmó Bianchi Romero en una entrevista realizada fuera del centro de operaciones.\n\nLa directiva evitó precisar qué credenciales o aplicaciones pudieron verse comprometidas, argumentando que divulgar detalles antes de terminar la contención podría afectar la investigación. Sí confirmó que se revocaron sesiones, se restringieron conexiones internas y se reforzó el monitoreo.\n\nPulso Metropolitano preguntó por qué algunos reportes de presión coincidieron con la degradación de los servicios digitales. Bianchi respondió que no existe todavía una atribución técnica concluyente, pero reconoció que la coincidencia “es parte central de la hipótesis de trabajo”.\n\nLa institución mantiene activos canales alternos de atención mientras especialistas revisan registros de acceso, cambios de configuración y movimientos entre sistemas.',
'Iván Rojas · Tecnología','laura-bianchi.svg',110,0,1);

-- FLAG 18: entorno operacional + director general e Isabelle Laurent.
INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking)
VALUES
(3,'FLAG_18',0,'ultima','interafas-reconoce-alcance-operacional','INTERAFAS reconoce que la investigación alcanzó sistemas de supervisión operacional','Adrián Leclerc e Isabelle Laurent confirman verificaciones manuales y restricciones temporales en el centro de control.',
'El incidente tecnológico que comenzó con intermitencias digitales escaló a una revisión de sistemas vinculados con la supervisión operacional del servicio de agua. El director general de INTERAFAS, Adrián Leclerc Mendoza, confirmó que el organismo trabaja bajo un esquema de contingencia reforzada.\n\n“No vamos a minimizar el escenario. Si existe una duda sobre la integridad de una señal, la instrucción es contrastarla en campo antes de ejecutar decisiones”, declaró Leclerc.\n\nLa coordinadora de Monitoreo Operacional, Isabelle Laurent Castro, explicó que varios equipos pasaron a validación manual. “Lo importante es separar lo que vemos en pantalla de lo que realmente está ocurriendo en la infraestructura. Esa verificación física está en curso”, dijo.\n\nLaurent evitó señalar si ya se había identificado una manipulación de variables, aunque confirmó que se revisan historiales de bombas, presión y caudal. Personal operativo fue desplegado a instalaciones consideradas prioritarias.\n\nEl director general rechazó responder si alguien dentro de INTERAFAS ignoró alertas previas. “Esa pregunta tendrá que responderse con evidencia, no con especulación. Pero si hubo omisiones, tendrán que documentarse”, afirmó.\n\nLa contingencia ya tiene efectos administrativos: cambios de turno, suspensión de mantenimientos no esenciales y presencia de personal jurídico y tecnológico en el centro de coordinación.',
'Carla Méndez · Enviada especial','adrian-leclerc.svg',118,0,1);

-- FLAG 19: presión política, entrevista al alcalde y denuncia financiera.
INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking)
VALUES
(3,'FLAG_19',0,'politica','alcalde-al-limite-entrevista-interafas','“No me diga que nadie lo vio”: alcalde enfrenta preguntas por la crisis de INTERAFAS','En una entrevista tensa, el alcalde de Saint Louis exige nombres, tiempos y responsabilidades mientras el incidente sigue abierto.',
'El alcalde de Saint Louis, Mateo Rivas Calderón, llegó a la conferencia de coordinación con un mensaje de respaldo institucional, pero terminó enfrentando preguntas sobre controles, contratos y advertencias previas.\n\n—¿Quién falló? —preguntó Pulso Metropolitano.\n\n“Eso lo va a determinar la investigación”, respondió. Al insistir sobre si el gobierno municipal conocía debilidades de seguridad, el alcalde elevó el tono: “No me diga que nadie lo vio. Si había reportes y alguien decidió archivarlos, quiero saber quién fue”.\n\nRivas Calderón reconoció que recibió un informe preliminar que menciona controles de acceso, sistemas internos y la necesidad de revisar proveedores tecnológicos. Negó que su oficina hubiera autorizado ocultar información sobre el incidente.\n\n—¿Renunciaría algún funcionario si se confirma negligencia?\n\n“Si hubo negligencia grave, no voy a proteger a nadie. Pero tampoco voy a condenar a una persona antes de tener el expediente completo”, contestó.\n\nEl alcalde fue cuestionado también por la contratación de servicios tecnológicos durante los últimos dos años. Señaló que solicitará a la Contraloría un corte extraordinario de expedientes y entregables.\n\nLa entrevista terminó cuando asesores de comunicación dieron por concluida la ronda de preguntas después de que se le preguntara si confiaba todavía en la dirección de INTERAFAS. “Confío en que responda con hechos”, dijo antes de retirarse.',
'Julia Ferrer · Política','photo-microphone',125,0,1),
(3,'FLAG_19',0,'politica','denuncia-contratos-posible-desvio','Denuncia pide investigar contratos tecnológicos y un posible desvío de 12.7 millones','La acusación no acredita por sí sola un desfalco; Contraloría confirma que revisará pagos, entregables y modificaciones contractuales.',
'Una denuncia administrativa presentada ante la Contraloría Metropolitana solicitó revisar contratos de modernización tecnológica vinculados con INTERAFAS y operaciones por 12.7 millones de unidades monetarias que, según los promoventes, requieren aclaración documental.\n\nEl escrito habla de un posible desvío de recursos, pero hasta ahora ninguna autoridad ha determinado que exista un desfalco. La revisión se concentrará en entregables, ampliaciones de contrato, fechas de aceptación y relación entre proveedores.\n\nLa directora Administrativa de INTERAFAS, Sofía Costa Navarro, afirmó que pondrá a disposición expedientes, órdenes de pago y actas de recepción. “Una investigación financiera debe seguir el rastro documental completo; no vamos a adelantar conclusiones”, declaró.\n\nEl director Jurídico, Marc Dubois Hernández, señaló que la institución preservará documentación digital y física relacionada con los procedimientos señalados. Añadió que cualquier alteración posterior a la apertura de la revisión sería tratada como un incidente adicional.\n\nPartidos de oposición ficticios FMC y ACC exigieron una auditoría externa, mientras MRP pidió evitar que la investigación financiera interfiera con la recuperación operativa.\n\nPulso Metropolitano solicitó postura a dos proveedores mencionados en la denuncia. Ambos negaron irregularidades y aseguraron haber cumplido sus contratos.',
'Equipo de Investigación','sofia-costa.svg',121,1,1);

-- FLAG 20: impacto humano, intervención de autoridades, defacement y presión presidencial.
INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking)
VALUES
(4,'FLAG_20',0,'ultima','ruptura-inundacion-heridos-crisis','Ruptura e inundación dejan seis heridos durante la peor hora de la crisis hídrica','Autoridades investigan si una transición abrupta de presión contribuyó a la falla de una conducción; dos personas permanecen hospitalizadas.',
'La crisis de INTERAFAS dejó de ser únicamente tecnológica. Una ruptura en una conducción principal provocó inundación en un paso vial del corredor norte y movilizó a cuerpos de emergencia. Seis personas resultaron lesionadas; dos fueron trasladadas a un hospital para observación.\n\nProtección Civil informó que todavía no existe una relación causal definitiva entre el incidente cibernético y la ruptura. Sin embargo, especialistas analizan si cambios abruptos de presión pudieron aumentar el estrés sobre un tramo previamente identificado como vulnerable.\n\nVecinos describieron un incremento repentino del flujo sobre la vialidad. Equipos de emergencia cerraron el paso mientras operadores de INTERAFAS aislaron el sector mediante maniobras locales.\n\nLa directora de Operación Hidráulica, Elena Marín, confirmó que la secuencia de presión será preservada como evidencia técnica. “Necesitamos reconstruir minuto por minuto lo que ocurrió antes de la ruptura”, señaló.\n\nEl incidente afectó temporalmente accesos a una clínica y obligó a redistribuir camiones cisterna hacia sectores con menor reserva. No se reportan fallecimientos.\n\nLa prioridad inmediata es estabilizar la red y determinar si el daño fue consecuencia directa, indirecta o coincidente con el ataque.',
'Equipo de Última Hora','photo-flood',160,0,1),
(4,'FLAG_20',0,'ultima','investigacion-detenciones-despidos','Investigación escala: un contratista detenido, dos despidos y tres mandos suspendidos','Fiscalía, Contraloría y autoridades administrativas abren expedientes paralelos; INTERAFAS confirma dos despidos y tres suspensiones cautelares.',
'La respuesta institucional entró en una nueva etapa después del colapso operativo. Autoridades confirmaron la detención preventiva de un contratista externo para el cumplimiento de una orden relacionada con preservación de evidencia y accesos a sistemas. La detención no equivale a una declaración de culpabilidad.\n\nINTERAFAS informó además el despido de dos responsables de nivel medio tras una revisión administrativa inicial y la suspensión cautelar de tres mandos mientras se examinan decisiones tomadas antes y durante el incidente. Los despidos son medidas laborales y no equivalen por sí mismos a responsabilidad penal.\n\nEl director Jurídico, Marc Dubois, confirmó que se preparan denuncias por acceso no autorizado, alteración de sistemas y posibles omisiones de control. “Se van a distinguir responsabilidades técnicas, administrativas y, si corresponde, penales”, declaró.\n\nLa Contraloría solicitó imágenes de servidores, registros de autenticación, expedientes de contratación y comunicaciones de proveedores. Equipos forenses comenzaron a trabajar bajo cadena de custodia.\n\nFuentes cercanas a la investigación señalan que una línea de análisis se concentra en el uso de credenciales y otra en las condiciones bajo las cuales determinados sistemas internos podían ser alcanzados desde componentes expuestos.\n\nLos nombres de las personas investigadas no serán publicados mientras no exista una determinación formal.',
'Unidad de Investigación','photo-evidence',170,0,1),
(4,'FLAG_20',0,'politica','presidencia-exige-investigacion-formal','Presidencia exige investigación formal y un informe completo sobre la crisis de INTERAFAS','El Ejecutivo solicita responsabilidades, cronología del ataque, afectaciones humanas y revisión de contratos vinculados con sistemas críticos.',
'La Presidencia emitió un posicionamiento extraordinario tras conocerse las afectaciones operativas y humanas relacionadas con la crisis de INTERAFAS. El comunicado ordena integrar una investigación formal con participación de instancias técnicas, administrativas y de procuración de justicia.\n\nEl documento exige reconstruir la cronología completa: desde las primeras anomalías digitales hasta el acceso a funciones de supervisión, las variaciones de presión y la afectación física registrada en el corredor norte.\n\nTambién solicita revisar la actuación de funcionarios, proveedores y responsables de continuidad operativa. La instrucción incluye entregar un informe sobre contratos tecnológicos, controles de acceso y recomendaciones que hubieran quedado pendientes.\n\nEn una breve declaración ante medios, una vocería presidencial señaló que “la infraestructura crítica no admite explicaciones incompletas cuando existen consecuencias para la población”.\n\nLa Presidencia pidió que los hallazgos no clasificados sean publicados una vez asegurada la evidencia, y que se establezca un calendario de medidas correctivas verificables.\n\nEl posicionamiento incrementa la presión sobre los gobiernos municipales y sobre la dirección general de INTERAFAS, que hasta ahora había concentrado la comunicación institucional.',
'Redacción Política','photo-press',180,0,1),
(4,'FLAG_20',0,'ultima','defacement-interafas-colectivo-umbral','Portal de INTERAFAS es reemplazado por mensaje de un colectivo hacktivista','La página institucional dejó de mostrar servicios y apareció una pantalla con la frase “el gobierno no sirve”.',
'El portal público de INTERAFAS dejó de mostrar su contenido institucional y fue sustituido por una pantalla atribuida a un grupo que se identifica como Colectivo Umbral. El mensaje afirma que “el gobierno no sirve” y reivindica la intrusión como una demostración de debilidad institucional.\n\nPulso Metropolitano verificó que la alteración visual, conocida como defacement, afecta el acceso normal a la portada y a diferentes secciones públicas del organismo dentro de la ventana actual del incidente.\n\nEspecialistas consultados advierten que un defacement no prueba por sí mismo que el atacante conserve control sobre sistemas operacionales; sin embargo, en el contexto de la crisis amplifica el impacto reputacional y obliga a tratar el servidor web como evidencia.\n\nLa directora de Tecnologías, Laura Bianchi, señaló que el portal permanecerá aislado mientras se determine la ruta de modificación. “La prioridad no es volver a poner una portada en línea; es asegurar que entendemos cómo ocurrió”, indicó.\n\nEl ataque fue ampliamente compartido en redes, donde capturas del mensaje circularon junto con críticas a la gestión del incidente. INTERAFAS pidió no utilizar enlaces alternativos o supuestos sitios de recuperación difundidos por cuentas no verificadas.\n\nLa investigación busca establecer si la alteración del portal fue realizada por el mismo actor que intervino en etapas anteriores o por un grupo oportunista que aprovechó la exposición pública del incidente.',
'Lucía Andrade · Ciberseguridad','photo-cyber',175,0,1),
(4,'FLAG_20',0,'comunidad','hospitales-reservas-y-riesgo-sanitario','Hospitales activan reservas y protocolo sanitario por caída de presión','La red hospitalaria mantuvo servicios esenciales, pero advirtió que una interrupción prolongada habría obligado a suspender procedimientos no urgentes.',
'Tres hospitales y varias unidades de atención primaria activaron reservas internas de agua durante el periodo de menor presión. Las autoridades sanitarias informaron que los servicios esenciales continuaron operando, aunque algunos procedimientos no urgentes fueron reprogramados de manera preventiva.\n\nAdministradores hospitalarios explicaron que la presión reducida puede afectar esterilización, limpieza y sistemas auxiliares si las reservas se agotan. Por esa razón se coordinó suministro preventivo mediante cisternas.\n\nNo se ha identificado contaminación de la red, pero personal sanitario solicitó mantener vigilancia sobre parámetros de calidad después de cambios bruscos de presión.\n\nLa situación expone uno de los riesgos menos visibles de una brecha en infraestructura hidráulica: una alteración digital puede convertirse rápidamente en un problema de continuidad clínica, movilidad urbana y protección civil.\n\nINTERAFAS informó que la recuperación de presión será gradual para evitar nuevos transitorios en la red.',
'Andrea Vega · Comunidad','photo-ambulance',150,1,1);

-- Recuperación: consecuencias institucionales posteriores.
INSERT IGNORE INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking)
VALUES
(5,'RECOVERY_STARTED',0,'politica','director-general-comparecera','Director general de INTERAFAS comparecerá ante comisión especial','Adrián Leclerc deberá responder sobre controles, advertencias previas, contratación y decisiones tomadas durante la emergencia.',
'Una comisión especial convocó al director general de INTERAFAS, Adrián Leclerc Mendoza, para explicar la gestión del incidente y las decisiones tomadas antes, durante y después de la contingencia.\n\nLa comparecencia incluirá preguntas sobre segmentación de sistemas, controles de acceso, supervisión de proveedores, continuidad operativa y mecanismos de respuesta a incidentes.\n\nLeclerc confirmó que acudirá y aseguró que entregará la cronología interna disponible. “Una crisis de esta naturaleza exige rendición de cuentas y evidencia verificable”, declaró.\n\nLa comisión también convocará a responsables de Operación Hidráulica, Tecnologías, Administración y Monitoreo Operacional.\n\nLa oposición anunció que solicitará información sobre observaciones de auditorías anteriores, mientras el bloque gubernamental pidió separar las fallas comprobadas de acusaciones todavía no acreditadas.',
'Redacción Política','adrian-leclerc.svg',115,0,0);

-- Redes: presión creciente y reacción inmediata por eventos.
INSERT IGNORE INTO scenario_social_posts
(phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party)
VALUES
(1,'FLAG_16',0,'@agua_norte_ya','Vecinos del Norte','Saint Louis','Servicio','Ya no es una colonia: somos varios sectores con la misma baja presión. Que dejen de decir que es mantenimiento y expliquen qué está pasando.',286,104,NULL),
(2,'FLAG_17',0,'@cuentasclaras','Cuentas Claras Metro','Metropolitano','Crítica','Si ya admiten acceso no autorizado, queremos saber desde cuándo lo sabían y quién decidió seguir operando como si nada. Esto huele muy mal.',421,188,NULL),
(3,'FLAG_18',0,'@operador_enojado','Operador Anónimo','Metropolitano','Crítica','Si están verificando TODO a mano es porque ya no confían en lo que ven en pantalla. Eso no es una “intermitencia”, eso es una crisis.',598,271,NULL),
(3,'FLAG_19',0,'@basta_de_excusas','Basta de Excusas','Soledade','Crítica','¿Contratos dudosos, sistemas expuestos y ahora nadie sabe quién autorizó qué? Ya estuvo bueno de hacerse p#$%s. Queremos responsables, no comunicados.',811,396,NULL),
(3,'FLAG_19',0,'@fmc_metropolitano','FMC Metropolitano','Metropolitano','Política','Solicitamos auditoría independiente, comparecencias públicas y preservación inmediata de todos los contratos y correos vinculados con la modernización tecnológica.',403,174,'FMC'),
(4,'FLAG_20',0,'@corredor_norte','Alerta Corredor Norte','Saint Louis','Emergencia','Hay agua cubriendo la vialidad y ambulancias entrando. Esto ya dejó de ser un problema de computadoras. ¿Quién va a responder por los heridos?',1290,811,NULL),
(4,'FLAG_20',0,'@no_fue_una_falla','No Fue Una Falla','Metropolitano','Crítica','Nos dijeron que todo estaba “bajo control” hasta que literalmente se rompió la red. Que no salgan mañana con otro comunicado de m#$%.',1552,932,NULL),
(4,'FLAG_20',0,'@presion_ciudadana','Presión Ciudadana','Cerro de San Pablo','Política','Presidencia ya pidió investigación formal. Ahora queremos nombres, fechas, contratos, bitácoras y sanciones. Todo público.',909,421,NULL),
(4,'FLAG_20',0,'@captura_urbana','Captura Urbana','Metropolitano','Última hora','La web de INTERAFAS fue reemplazada por un mensaje hacktivista. La captura ya circula por todas partes. El golpe reputacional es brutal.',1820,1170,NULL);
-- INTERAFAS v0.4.6 · continuidad narrativa
ALTER TABLE afectaciones ADD COLUMN IF NOT EXISTS event_required VARCHAR(100) NULL AFTER prioridad;
ALTER TABLE afectaciones ADD INDEX IF NOT EXISTS idx_afectacion_event (event_required);
ALTER TABLE scenario_social_posts ADD COLUMN IF NOT EXISTS meme_title VARCHAR(180) NULL AFTER avatar_url;
ALTER TABLE scenario_social_posts ADD COLUMN IF NOT EXISTS meme_caption VARCHAR(320) NULL AFTER meme_title;

-- Afectaciones que aparecen conforme avanza el incidente.
INSERT INTO afectaciones (titulo,tipo,estado,municipio,sector,descripcion,inicio,fin_estimado,cuentas_estimadas,x_pct,y_pct,prioridad,event_required)
SELECT 'Variaciones de presión en corredor norte','Baja presión','En atención','Saint Louis','Corredor Norte','Se investigan variaciones simultáneas de presión. Cuadrillas verifican estaciones, tanques y válvulas de regulación.','2026-09-23 09:10:00','2026-09-23 13:00:00',6840,51,24,'Alta','FLAG_16'
WHERE NOT EXISTS (SELECT 1 FROM afectaciones WHERE event_required='FLAG_16' AND titulo='Variaciones de presión en corredor norte');
INSERT INTO afectaciones (titulo,tipo,estado,municipio,sector,descripcion,inicio,fin_estimado,cuentas_estimadas,x_pct,y_pct,prioridad,event_required)
SELECT 'Contingencia por indisponibilidad de monitoreo remoto','Incidente tecnológico','En atención','Cerro de San Pablo','Zona Centro','Operadores realizan validación en campo mientras se restringen funciones de supervisión remota.','2026-09-23 10:45:00','2026-09-23 16:30:00',4250,27,42,'Alta','FLAG_17'
WHERE NOT EXISTS (SELECT 1 FROM afectaciones WHERE event_required='FLAG_17' AND titulo='Contingencia por indisponibilidad de monitoreo remoto');
INSERT INTO afectaciones (titulo,tipo,estado,municipio,sector,descripcion,inicio,fin_estimado,cuentas_estimadas,x_pct,y_pct,prioridad,event_required)
SELECT 'Operación manual de estaciones prioritarias','Continuidad operacional','En curso','Saint Louis','Sector Norte','Se activó verificación manual de variables y maniobras locales en instalaciones prioritarias.','2026-09-23 12:30:00','2026-09-23 19:00:00',9120,57,30,'Crítica','FLAG_18'
WHERE NOT EXISTS (SELECT 1 FROM afectaciones WHERE event_required='FLAG_18' AND titulo='Operación manual de estaciones prioritarias');
INSERT INTO afectaciones (titulo,tipo,estado,municipio,sector,descripcion,inicio,fin_estimado,cuentas_estimadas,x_pct,y_pct,prioridad,event_required)
SELECT 'Presión irregular y cierre preventivo de ramal','Contingencia','En atención','Soledade','San Felipe','Se aisló preventivamente un ramal tras registrarse transitorios de presión fuera del patrón esperado.','2026-09-23 14:20:00','2026-09-23 21:30:00',11700,73,51,'Crítica','FLAG_19'
WHERE NOT EXISTS (SELECT 1 FROM afectaciones WHERE event_required='FLAG_19' AND titulo='Presión irregular y cierre preventivo de ramal');
INSERT INTO afectaciones (titulo,tipo,estado,municipio,sector,descripcion,inicio,fin_estimado,cuentas_estimadas,x_pct,y_pct,prioridad,event_required)
SELECT 'Emergencia hidráulica en corredor norte','Emergencia','En atención','Saint Louis','Corredor Norte','Ruptura de conducción, anegamiento vial y caída abrupta de presión. Protección civil y servicios médicos trabajan en la zona.','2026-09-23 16:42:00',NULL,18600,61,18,'Crítica','FLAG_20'
WHERE NOT EXISTS (SELECT 1 FROM afectaciones WHERE event_required='FLAG_20' AND titulo='Emergencia hidráulica en corredor norte');

-- Memes, burlas y presión social escalonada.
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption)
SELECT 1,'FLAG_16',0,'@memes_del_tinaco','Memes del Tinaco','Saint Louis','Humor','Cuando INTERAFAS dice “operación normal” pero tu regadera ya suena como cafetera.',648,291,NULL,'OPERACIÓN NORMAL','El tinaco: “yo no firmé para esto” 💀'
WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@memes_del_tinaco' AND event_required='FLAG_16');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption)
SELECT 2,'FLAG_17',0,'@ctrl_alt_agua','Ctrl+Alt+Agua','Metropolitano','Humor','Ya salió el comunicado de “incidente tecnológico”. Internet haciendo su trabajo:',1090,602,NULL,'HAVE YOU TRIED TURNING THE CITY OFF AND ON AGAIN?','— Soporte técnico metropolitano, probablemente.'
WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@ctrl_alt_agua' AND event_required='FLAG_17');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption)
SELECT 3,'FLAG_18',0,'@scada_memes','SCADA Memes','Metropolitano','Humor','Operadores verificando en campo porque ya nadie le cree a la pantalla:',1650,877,NULL,'CONFÍA EN MÍ BRO','La pantalla: 100% normal · La planta: 🔥'
WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@scada_memes' AND event_required='FLAG_18');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption)
SELECT 3,'FLAG_19',0,'@licitacion_memes','Licitación Memes','Soledade','Humor','Cuando empiezan a revisar contratos y de pronto todos ponen “fuera de oficina”.',2310,1205,NULL,'VISTO A LAS 14:18','Contratista abandonó el grupo.'
WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@licitacion_memes' AND event_required='FLAG_19');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption)
SELECT 4,'FLAG_20',0,'@ciudad_sin_filtro','Ciudad Sin Filtro','Metropolitano','Humor negro','Ya no sé si abrir Pulso Metropolitano o Protección Civil. Cada refresh desbloquea otro desastre.',3880,2490,NULL,'NUEVO LOGRO DESBLOQUEADO','“Infraestructura crítica: modo pesadilla”'
WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@ciudad_sin_filtro' AND event_required='FLAG_20');

-- El defacement solo existe al acreditar las 20 banderas.
UPDATE news_articles SET event_required='ALL_FLAGS', phase_required=4 WHERE slug='defacement-interafas-colectivo-umbral';
UPDATE news_articles SET headline='Hacktivistas crean una pestaña clandestina dentro del portal de INTERAFAS', subheadline='Tras completarse la cadena de intrusión, aparece una nueva sección // SYSTEM // con un mensaje atribuido a Colectivo Umbral.', body='Una nueva pestaña apareció dentro de la navegación de INTERAFAS después de que la investigación del incidente alcanzara su punto más crítico. A diferencia de un derribo total del sitio, el contenido institucional continúa disponible, pero la sección // SYSTEM // muestra un mensaje atribuido al colectivo ficticio Umbral.\n\nLa intervención visual afirma que la institución acumuló deuda técnica, controles rotos y una confianza excesiva en su arquitectura. La presencia de la pestaña sugiere que el atacante consiguió persistir o modificar componentes de presentación del portal sin necesidad de sustituir toda la web.\n\nEspecialistas consultados señalaron que este tipo de alteración debe tratarse como evidencia: retirar el contenido de inmediato puede destruir información útil para reconstruir la ruta de acceso. El equipo de respuesta tendría que preservar archivos, registros, marcas de tiempo y cambios de configuración antes de restaurar una versión confiable.\n\nLa directora de Tecnologías, Laura Bianchi Romero, indicó que la aparición del mensaje se integró a la investigación forense y que el organismo mantuvo el resto del portal disponible mientras aislaba el componente afectado.\n\nLa publicación generó nuevas críticas y burlas en redes sociales, pero también elevó la presión para que la investigación determine si la alteración fue obra del mismo actor responsable del resto de la intrusión.\n\nPulso Metropolitano verificó que la pestaña únicamente aparece después de completarse la cadena de hallazgos del incidente.' WHERE slug='defacement-interafas-colectivo-umbral';
UPDATE scenario_social_posts SET event_required='ALL_FLAGS', content='Apareció una pestaña // SYSTEM // dentro de INTERAFAS con un mensaje del Colectivo Umbral. El resto del portal sigue arriba, pero la captura ya está en todas partes.' WHERE handle='@captura_urbana';

-- Cobertura metropolitana no relacionada: mantiene vivo el portal antes y durante la crisis.
INSERT IGNORE INTO news_articles (phase_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES
(0,0,'local','parque-lineal-rio-abre-tramo','Abre nuevo tramo del parque lineal del Río Verde','El corredor suma ciclovía, iluminación y áreas de descanso entre Saint Louis y Soledade.','El nuevo tramo del parque lineal del Río Verde abrió al público este miércoles con 3.4 kilómetros adicionales de ciclovía, andadores y áreas de descanso. Autoridades metropolitanas estiman que el corredor podrá recibir hasta 12 mil usuarios durante los fines de semana.\n\nLa obra incluye iluminación de bajo consumo, zonas de sombra y puntos de hidratación. Vecinos de colonias cercanas celebraron la recuperación del espacio, aunque pidieron reforzar seguridad y mantenimiento nocturno.\n\nEl proyecto forma parte de un programa de movilidad activa que conectará parques, centros educativos y estaciones de transporte. La siguiente etapa contempla dos puentes peatonales.\n\nOrganizaciones ciclistas señalaron que la infraestructura será útil si se mantiene libre de vehículos y comercio invasivo. El municipio anunció vigilancia y limpieza diaria durante el primer mes.','Daniela Ruiz · Ciudad','infrastructure-map.svg',15,0,0),
(0,0,'comunidad','universidad-robotica-final-nacional','Equipo universitario llega a final nacional de robótica','Estudiantes metropolitanos competirán con un robot autónomo diseñado para tareas de rescate.','Un equipo de estudiantes de ingeniería obtuvo su pase a la final nacional de robótica con un prototipo autónomo capaz de identificar obstáculos, transportar pequeños suministros y enviar telemetría a un centro de control.\n\nEl proyecto fue desarrollado durante seis meses y combina visión artificial, navegación autónoma y sensores de proximidad. Los integrantes explicaron que el objetivo no es sustituir a brigadistas, sino explorar herramientas para entornos inseguros.\n\nLa universidad informó que apoyará el viaje del equipo y abrirá una demostración pública antes de la competencia. Docentes destacaron que el prototipo integra conocimientos de electrónica, programación y diseño mecánico.\n\nLa final se realizará el próximo mes y reunirá a 24 instituciones del país.','Mateo Cruz · Educación','operations-room.svg',14,0,0),
(0,0,'local','ruta-nocturna-transporte-prueba','Transporte metropolitano probará ruta nocturna durante cuatro fines de semana','El servicio piloto conectará Centro Histórico, zona universitaria y corredor hospitalario.','La autoridad de movilidad anunció una prueba de transporte nocturno durante cuatro fines de semana. La ruta operará de 23:00 a 03:00 horas y conectará zonas con actividad cultural, estudiantil y hospitalaria.\n\nEl programa busca medir demanda, tiempos de viaje y percepción de seguridad antes de decidir si se convierte en servicio permanente. Las unidades tendrán seguimiento GPS y paradas definidas.\n\nComerciantes y trabajadores nocturnos recibieron positivamente el anuncio, mientras asociaciones vecinales solicitaron vigilancia en los puntos de ascenso y descenso.\n\nLa autoridad publicará un reporte de uso al concluir la prueba.','Sergio Vidal · Movilidad','support-team.svg',13,0,0),
(0,0,'cultura','festival-luces-centro-historico','Festival de luces transformará ocho fachadas del Centro Histórico','Proyecciones, música y recorridos peatonales se realizarán durante tres noches.','Ocho edificios del Centro Histórico formarán parte de un festival de iluminación arquitectónica que combinará proyecciones, música y recorridos peatonales. La programación incluirá funciones cada media hora.\n\nLos organizadores informaron que el acceso será gratuito y que varias calles tendrán cierres parciales desde las 18:00 horas. También habrá presentaciones de artistas locales y un corredor gastronómico.\n\nProtección Civil recomendó utilizar transporte público y atender la señalización temporal. Comerciantes esperan un aumento de visitantes durante el fin de semana.\n\nEl programa completo estará disponible en módulos turísticos y redes oficiales.','Clara Ibarra · Cultura','hero-water.svg',12,0,0),
(0,0,'comunidad','refugios-adopcion-mascotas-jornada','Refugios preparan jornada metropolitana de adopción responsable','Más de 80 perros y gatos participarán en una campaña conjunta de salud y adopción.','Refugios y asociaciones civiles realizarán una jornada de adopción responsable con más de 80 animales previamente valorados por equipos veterinarios.\n\nLa actividad incluirá vacunación, orientación sobre esterilización y registro de tutores. Las organizaciones pedirán identificación y una entrevista breve antes de autorizar cada adopción.\n\nVeterinarios participantes recordaron que adoptar implica gastos permanentes de alimentación, salud y cuidados. También pidieron evitar regalos impulsivos de animales.\n\nLa jornada se realizará el sábado en la plaza metropolitana.','Ana Lucía Mora · Comunidad','support-team.svg',11,0,0),
(0,0,'economia','mercado-productores-regionales','Productores regionales abren mercado temporal en Soledade','Cuarenta cooperativas ofrecerán alimentos, textiles y productos artesanales sin intermediarios.','Cuarenta cooperativas de la región instalarán un mercado temporal en Soledade durante tres días. La iniciativa busca conectar directamente a productores con consumidores y reducir costos de intermediación.\n\nLa oferta incluye frutas, hortalizas, quesos, panadería, textiles y productos artesanales. La organización habilitará pagos electrónicos y espacios de degustación.\n\nProductores señalaron que este tipo de mercados ayuda a probar nuevos productos y conocer directamente las preferencias de los compradores.\n\nEl municipio prevé replicar el modelo una vez al mes si la asistencia supera las expectativas.','Raúl Montenegro · Economía','payment-online.svg',10,0,0),
(0,0,'local','biblioteca-amplia-horarios-examenes','Biblioteca Metropolitana amplía horarios por temporada de exámenes','Las salas de estudio permanecerán abiertas hasta medianoche durante tres semanas.','La Biblioteca Metropolitana ampliará temporalmente sus horarios para atender a estudiantes durante la temporada de exámenes. Las salas principales operarán hasta las 00:00 horas de lunes a sábado.\n\nLa institución habilitará 180 espacios adicionales de estudio, préstamo de computadoras y asesorías breves para búsqueda bibliográfica.\n\nEl acceso nocturno requerirá registro en recepción y las áreas infantiles conservarán su horario habitual.\n\nLa medida será evaluada con base en afluencia y uso de servicios digitales.','Paula León · Ciudad','support-team.svg',9,0,0),
(0,0,'comunidad','bomberos-rescatan-perro-canal','Bomberos rescatan a un perro atrapado en canal pluvial','El animal fue entregado a una asociación y se encuentra fuera de peligro.','Bomberos metropolitanos rescataron a un perro que quedó atrapado en un canal pluvial después de una lluvia ligera. Vecinos alertaron a los servicios de emergencia al escuchar al animal.\n\nLa maniobra requirió cuerdas y una escalera para descender de forma segura. El perro presentaba signos de agotamiento, pero no lesiones graves.\n\nUna asociación local lo trasladó a valoración veterinaria y comenzó la búsqueda de sus tutores.\n\nEl cuerpo de bomberos aprovechó el caso para pedir a la población no ingresar a canales durante lluvias y reportar animales atrapados a números de emergencia.','Lucía Prado · Comunidad','field-engineer.svg',8,0,0);


-- ===== v0.4.7 editorial y conversación social =====
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER DATABASE interafas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE scenario_social_posts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- v0.4.7 · Opinión: formatos periodísticos variados.
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES
(0,NULL,0,'opinion','entrevista-experta-infraestructura-no-se-protege-sola','ENTREVISTA | “La infraestructura no se protege sola”: una especialista explica por qué gobernanza y operación deben hablarse','La investigadora ficticia Helena Ríos analiza resiliencia, continuidad y el error de pensar que la ciberseguridad es solo un asunto de sistemas.','Helena Ríos lleva quince años estudiando continuidad de servicios públicos y resiliencia organizacional. En entrevista con Pulso Metropolitano sostiene que la infraestructura crítica suele fallar menos por una sola vulnerabilidad que por la acumulación de decisiones pequeñas que nadie conecta entre sí.\n\n“Cuando operación, tecnología, jurídico y dirección trabajan como mundos separados, el problema no es únicamente técnico. El verdadero riesgo aparece en las fronteras entre responsabilidades”, explicó.\n\nLa especialista señala que un organismo de agua debe conocer qué activos sostienen los procesos esenciales, quién puede tomar decisiones de emergencia y qué funciones pueden continuar manualmente. También advierte que la telemetría y la automatización mejoran el servicio, pero aumentan la dependencia de controles de acceso, segmentación y monitoreo.\n\nRíos considera que la preparación debe incluir ejercicios que involucren a directivos y no solo a personal informático. “Si el primer simulacro ocurre durante una crisis real, ya llegaste tarde”, afirmó.\n\nLa entrevista forma parte de una serie de Pulso Metropolitano sobre servicios públicos, tecnología y resiliencia urbana.','Mariana Solís · Entrevistas','photo-control',48,0,0),
(0,NULL,0,'opinion','voces-calle-servicio-publico-confianza','VOCES DE LA CALLE | ¿Qué hace que la gente confíe en un servicio público?','Pulso Metropolitano conversó con usuarios, comerciantes y estudiantes sobre atención, continuidad y transparencia.','Durante dos jornadas, reporteros de Pulso Metropolitano conversaron con 24 habitantes de los tres municipios sobre su relación cotidiana con los servicios públicos. Las respuestas muestran que la confianza no depende únicamente de que el agua llegue: también importan la capacidad de explicar fallas, responder reportes y cumplir tiempos prometidos.\n\nAlgunos usuarios destacaron mejoras en atención digital y seguimiento de reportes. Otros señalaron que una buena experiencia no compensa semanas de baja presión o recibos difíciles de aclarar.\n\n“Yo prefiero que me digan que van a tardar seis horas y lleguen en seis, a que me prometan una y nadie aparezca”, comentó Arturo, comerciante de Saint Louis. Una estudiante de Soledade resumió otra preocupación: “Cuando todo funciona nadie pregunta cómo está hecho; cuando falla, quieres entender quién era responsable”.\n\nLas opiniones recogidas no constituyen una encuesta representativa. Son una muestra periodística de experiencias cotidianas que ayuda a entender cómo se construye —o se pierde— legitimidad alrededor de un servicio esencial.','Equipo de Comunidad','photo-microphone',41,0,0),
(0,NULL,0,'opinion','pingo-la-nube-no-arregla-tuberias','PINGO | “Subieron todo a la nube… menos el tinaco”','La viñeta satírica de la semana se ríe de nuestra obsesión por ponerle “smart” a todo.','Pingo aparece esta semana frente a una pantalla con veinte indicadores verdes mientras sostiene una cubeta vacía. En la esquina, un letrero dice: “Transformación digital: 100% completada”.\n\nLa sátira apunta a una contradicción común en gobiernos y empresas: modernizar interfaces sin resolver procesos básicos. “No estoy contra la tecnología”, dice Pingo en la viñeta, “solo quisiera que el dashboard también cargara agua”.\n\nLa columna gráfica no atribuye hechos a una institución específica. Utiliza humor para cuestionar la tendencia a confundir digitalización con mejora automática del servicio.\n\nComo cada semana, lectores enviaron sus propias versiones del meme. La más compartida sustituye el tablero por una hoja de cálculo con la leyenda: “Indicador de satisfacción: no preguntar”.','Pingo · Humor editorial','photo-pumping',36,0,0),
(0,NULL,0,'opinion','reporte-especial-ciudad-dependencias-invisibles','REPORTE ESPECIAL | La ciudad que no vemos: dependencias invisibles detrás de un vaso de agua','Bombas, energía, telecomunicaciones, personal, laboratorios y proveedores forman una cadena mucho más compleja de lo que parece.','Abrir una llave parece un acto simple. Detrás existe una cadena de captación, bombeo, almacenamiento, desinfección, medición, distribución, facturación, mantenimiento y atención. Cada etapa depende de personas, energía, comunicaciones y decisiones.\n\nPulso Metropolitano reconstruyó, a partir de información pública y entrevistas con especialistas ficticios, las principales dependencias de un sistema hídrico metropolitano. La conclusión es clara: no existe un único “sistema del agua”, sino una red de procesos que puede degradarse de formas muy distintas.\n\nUna falla eléctrica puede detener una estación de bombeo; una avería de telecomunicaciones puede obligar a operar localmente; un problema de inventario puede retrasar una reparación; una mala gestión de accesos puede exponer plataformas que originalmente fueron diseñadas solo para consulta.\n\nLos expertos consultados coinciden en que resiliencia significa conocer esas dependencias y preparar alternativas antes de necesitarlas. El objetivo no es prometer que nada fallará, sino evitar que una falla local se convierta en una crisis metropolitana.','Unidad de Investigación','photo-reservoir',45,0,0),
(2,'FLAG_17',0,'opinion','entrevista-ciberseguridad-incidente-no-empieza-cuando-sale-en-prensa','ENTREVISTA | “Un incidente no empieza cuando sale en prensa; para entonces ya lleva tiempo ocurriendo”','Un especialista en respuesta a incidentes explica qué señales buscan los equipos cuando una falla digital comienza a tocar la operación.','Tras la confirmación de un incidente tecnológico en INTERAFAS, Pulso Metropolitano consultó al especialista ficticio Esteban Arce, dedicado a respuesta a incidentes y análisis forense. Arce insiste en que una investigación seria debe separar hechos comprobados, hipótesis y rumores.\n\n“El error más peligroso durante las primeras horas es querer una explicación total. Primero hay que preservar evidencia, reducir exposición y entender qué sistemas siguen siendo confiables”, señaló.\n\nSegún el especialista, cuando existe infraestructura operacional involucrada, las decisiones técnicas deben coordinarse con operadores que conocen el proceso físico. Apagar indiscriminadamente equipos o redes puede ser tan dañino como mantenerlas conectadas.\n\nArce también subrayó que los registros de autenticación, cambios de configuración, accesos administrativos y movimientos entre sistemas permiten reconstruir una secuencia. “La pregunta no es solo cómo entraron, sino qué pudieron ver, qué pudieron cambiar y cuánto tiempo estuvieron ahí”.','Mariana Solís · Entrevistas','photo-cyber',79,0,0),
(3,'FLAG_18',0,'opinion','pingo-control-total-clave-postit','PINGO | “Sistema de control total — clave pegada en el monitor”','La sátira de Pingo aparece en plena crisis tecnológica y dispara miles de compartidos.','La nueva viñeta de Pingo muestra una puerta blindada con siete candados. A un lado, una nota adhesiva dice: “Contraseña: Admin123 — no borrar”.\n\nEl dibujo se publicó después de que la crisis de INTERAFAS ampliara su alcance hacia funciones de supervisión operacional. La pieza no afirma que esa práctica exista en el organismo; utiliza exageración para burlarse de la distancia que a veces existe entre controles costosos y hábitos cotidianos.\n\nEn redes, usuarios transformaron la viñeta en decenas de variaciones: cámaras protegidas con cinta, servidores bajo llave con la puerta abierta y un supuesto manual de “ciberseguridad avanzada” cuya primera instrucción es “no se lo digas a nadie”.\n\nDetrás del humor aparece una pregunta seria: ¿de qué sirven controles sofisticados si las prácticas de operación los debilitan?','Pingo · Humor editorial','photo-control',83,0,0),
(3,'FLAG_19',0,'opinion','voces-calle-quien-debe-responder-interafas','VOCES DE LA CALLE | “Alguien tiene que explicar quién sabía qué”: ciudadanos exigen responsabilidades','Reporteros recorrieron zonas afectadas para documentar frustración, dudas y también llamados a evitar rumores.','La conversación en las calles cambió de tono conforme crecieron las revelaciones sobre el incidente de INTERAFAS. Pulso Metropolitano entrevistó a vecinos, comerciantes, estudiantes y trabajadores en cinco puntos de los tres municipios.\n\nLa palabra que más se repitió fue “responsabilidad”. Algunos ciudadanos pidieron renuncias inmediatas; otros consideraron prematuro señalar culpables antes de conocer los resultados de las investigaciones.\n\n“Quiero saber qué falló, quién lo sabía y qué hicieron cuando se enteraron”, dijo Patricia, vecina de Saint Louis. Un comerciante de Soledade pidió algo distinto: “Primero que estabilicen el agua. Después que se peleen en el cabildo”.\n\nTambién aparecieron preocupaciones sobre contratos, mantenimiento y proveedores. Ninguna de las acusaciones recogidas pudo ser confirmada de manera independiente al cierre de esta nota.\n\nEl ejercicio periodístico no pretende representar estadísticamente a toda la población; documenta el cambio de clima social conforme el incidente deja de percibirse como una simple falla técnica.','Equipo de Calle','photo-microphone',88,0,0),
(4,'FLAG_20',0,'opinion','reporte-especial-como-falla-digital-se-vuelve-riesgo-fisico','REPORTE ESPECIAL | Cómo una brecha digital puede terminar convertida en riesgo físico','Expertos explican la cadena que conecta decisiones informáticas, supervisión operacional, presión hidráulica y seguridad pública.','La crisis de INTERAFAS puso sobre la mesa una pregunta que durante años pareció reservada a especialistas: ¿cómo puede una intrusión digital terminar afectando calles, hospitales o personas?\n\nLa respuesta está en la convergencia entre sistemas de información y procesos físicos. Los tableros de supervisión no son únicamente pantallas: representan bombas, niveles, presiones, válvulas y alarmas que forman parte de decisiones operacionales.\n\nEspecialistas ficticios consultados por Pulso Metropolitano explicaron que el riesgo aumenta cuando las mismas credenciales, redes o servicios permiten atravesar capas que deberían estar separadas. Una acción no autorizada puede generar una lectura falsa, impedir una respuesta o inducir una maniobra equivocada.\n\nEso no significa que cualquier ataque informático pueda “abrir una válvula” de manera automática. Cada instalación tiene arquitectura, controles y modos de respaldo distintos. La gravedad surge cuando varias debilidades se encadenan y coinciden con condiciones físicas reales.\n\nEl incidente ya es materia de investigación técnica, administrativa y penal. Determinar el vínculo exacto entre la intrusión y las afectaciones físicas será una de las tareas centrales de los peritajes.','Unidad de Investigación','photo-break',98,0,1);

-- v0.4.7 · conversación base más abundante y deliberadamente desordenada.
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@ana_servicios','Ana L.','Saint Louis','Servicio','Ayer reporté una fuga a las 7:20 y a las 10 ya había cuadrilla. Ojalá siempre funcionara así.',84,11,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@ana_servicios');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@raul_del_mercado','Raúl M.','Soledade','Crítica','Tres reportes y sigo esperando que revisen la tapa rota de la calle. Para cobrar sí son puntuales.',126,35,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@raul_del_mercado');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@metro_con_datos','Metro con Datos','Metropolitano','Análisis','El tablero de indicadores está mejor que el del año pasado. Falta que publiquen históricos descargables de más variables.',59,14,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@metro_con_datos');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@martha_centro','Martha C.','Cerro de San Pablo','Política','Si la obra funciona, que se reconozca. Si no funciona, que se critique. Todo lo demás es porra de partido.',97,28,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@martha_centro');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@soy_del_mrp','Cuenta Ciudadana 2026','Saint Louis','Política','Se nota que este gobierno sí está metiendo dinero en agua. Antes nadie volteaba a ver las tuberías.',71,19,'MRP',NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@soy_del_mrp');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@no_compro_discurso','No compro discurso','Soledade','Política','Cada administración presume kilómetros de red y luego llueve dos horas y tenemos media colonia inundada. Menos anuncios.',183,66,'FMC',NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@no_compro_discurso');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@tacos_el_puente','Tacos El Puente','Saint Louis','Fuera de tema','¿Alguien sabe si hoy sí juega el Saint Louis FC a las ocho? Pregunto porque aquí todo mundo habla del agua 😂',44,4,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@tacos_el_puente');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@perrito_hidratado','Perrito Hidratado','Metropolitano','Humor','Yo entrando a ver el recibo y terminando 40 minutos en el observatorio hídrico.',331,98,NULL,'SOLO IBA A PAGAR','Ahora sé más de presión por sector que de mis finanzas personales.' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@perrito_hidratado');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@lucia_maestra','Lucía P.','Cerro de San Pablo','Comunidad','La sección de cultura del agua sí me sirvió para una actividad con mis alumnos. Deberían mandar ese material a las escuelas.',52,9,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@lucia_maestra');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@usuario_cansado','Usuario cansado','Saint Louis','Crítica','La app bonita, el portal bonito, pero llevo dos semanas aclarando un cargo. El diseño no contesta el teléfono.',214,72,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@usuario_cansado');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@ingeniera_nora','Nora V.','Metropolitano','Análisis','Que tengan telemetría pública me parece positivo. La transparencia técnica también sirve para que la gente entienda la complejidad del sistema.',63,17,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@ingeniera_nora');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@vecino_random','Vecino Random','Soledade','Fuera de tema','No tiene nada que ver pero ¿quién dejó un paraguas azul en la parada de San Felipe? Lo tengo yo.',27,2,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@vecino_random');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@cafe_y_agua','Café y Agua','Saint Louis','Humor','Mi relación con INTERAFAS: yo pago, ellos mandan PDF, ninguno de los dos sabe qué pasó.',412,130,NULL,'RELACIÓN ESTABLE','Visto por última vez: fecha de corte.' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@cafe_y_agua');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@contralor_de_sillon','Contralor de Sillón','Metropolitano','Política','Todos expertos en obra hidráulica desde el sillón. Yo incluido. Pero mínimo publiquen contratos completos para pelear con documentos.',151,47,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@contralor_de_sillon');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@sofi_norte','Sofi R.','Saint Louis','Servicio','Hoy presión normal en mi zona. Lo pongo porque también hay que avisar cuando sí funciona.',68,7,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@sofi_norte');
INSERT INTO scenario_social_posts (phase_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,0,'@don_chemo','Don Chemo','Cerro de San Pablo','Humor','Antes uno sabía que faltaba agua porque abría la llave. Ahora primero revisas tres dashboards y luego abres la llave.',289,84,NULL,'MODERNIDAD','Falta de agua 2.0.' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@don_chemo');

-- v0.4.8 · Cada FLAG libera cobertura periodística y conversación social.
SET NAMES utf8mb4;
ALTER TABLE news_articles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE scenario_social_posts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_01',0,'local','archivo-publico-genera-preguntas','Respaldo de despliegue queda expuesto junto a documentos públicos de INTERAFAS','Un archivo ZIP generado durante una actualización del portal quedó accesible en la misma ruta usada para documentos públicos.','Un archivo de respaldo generado durante una actualización del portal de INTERAFAS quedó accesible junto a documentos públicos de contratación. El ZIP contiene un manifiesto de versión, notas de despliegue, un registro de sincronización y archivos auxiliares utilizados por el equipo web. El hallazgo no demuestra por sí mismo una intrusión, pero evidencia que un artefacto temporal permaneció bajo el directorio público después del despliegue.

Fuentes técnicas del organismo indicaron que el archivo debió eliminarse al concluir la sincronización de la versión. El equipo de Tecnologías revisa ahora el procedimiento de despliegue y la configuración del servidor que permitió listar el contenido del directorio. Especialistas consultados por Pulso Metropolitano señalan que respaldos y residuos de publicación pueden revelar nombres internos, rutas, versiones y hábitos operativos útiles para reconocimiento posterior.

INTERAFAS informó que retirará de manera preventiva cualquier documento cuya publicación no pueda justificarse y anunció una revisión de inventario. La institución insistió en que, hasta el momento, no existe evidencia de afectación al servicio.','Unidad Digital','photo-evidence',46,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_01',0,'@pulso_flag_01','Conversación Metro','Metropolitano','Conversación','¿Alguien más vio el ZIP que apareció junto a los PDF de contratos? Parece de una actualización del portal.',27,5,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_01');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_01',0,'@reaccion_flag_01','Voces Metropolitanas','Metropolitano','Conversación','Si eso era “interno”, alguien debería explicar por qué estaba a un clic.',24,5,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_01');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_02',0,'tecnologia','codigo-cliente-revela-pistas','Código visible en el navegador revela más de lo que parece','Analistas advierten que comentarios, rutas y variables expuestas del lado del cliente pueden ayudar a reconstruir la arquitectura de un sistema.','Una revisión del código entregado al navegador por el portal de INTERAFAS permitió observar referencias internas que no eran necesarias para el funcionamiento público. Aunque se trata de información disponible para cualquier usuario, especialistas consideran que la acumulación de pequeños detalles puede facilitar el reconocimiento de una plataforma.

El equipo de Tecnologías confirmó que revisará scripts, comentarios y configuraciones expuestas en el frontend. “Que algo esté en el navegador no significa que sea secreto, pero tampoco significa que debamos regalar contexto innecesario”, señaló una fuente técnica del organismo.

La institución no reportó interrupciones y mantiene el portal operando con normalidad. La revisión se incorporó al programa de endurecimiento de aplicaciones.','Mesa de Tecnología','photo-cyber',47,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_02',0,'@pulso_flag_02','Conversación Metro','Metropolitano','Conversación','Hay gente revisando hasta el código de la página. Internet nunca perdona un comentario olvidado.',34,7,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_02');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_02',0,'@reaccion_flag_02','Voces Metropolitanas','Metropolitano','Conversación','Mi teoría: cada “detalle sin importancia” termina siendo una pieza del rompecabezas.',33,8,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_02');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_03',0,'servicios','usuarios-reportan-datos-cruzados','Usuarios reportan acceso cruzado a información de cuentas de servicio','INTERAFAS investiga si una falla de autorización permitió consultar datos asociados con contratos ajenos.','INTERAFAS inició una revisión después de recibir reportes sobre información de cuentas de servicio que podía mostrarse al modificar referencias dentro de una solicitud web. El organismo señaló que aún determina el alcance y cuántos registros pudieron quedar expuestos.

Especialistas explican que este tipo de falla no depende de adivinar contraseñas, sino de verificar correctamente que cada usuario tenga permiso para consultar el objeto solicitado. Una referencia numérica predecible puede convertirse en un problema si el servidor confía únicamente en que el usuario “no debería cambiarla”.

Atención Ciudadana pidió a los usuarios reportar cualquier información que no corresponda a su cuenta y anunció una revisión de accesos recientes.','Unidad de Servicios','photo-control',48,0,1);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_03',0,'@pulso_flag_03','Conversación Metro','Metropolitano','Conversación','Me apareció información que claramente no era de mi cuenta. Eso sí me preocupa.',41,9,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_03');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_03',0,'@reaccion_flag_03','Voces Metropolitanas','Metropolitano','Conversación','No quiero descuento ni disculpa: quiero saber quién puede ver mis datos y por qué.',42,11,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_03');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_04',0,'tecnologia','respaldo-historico-expuesto','Aparece respaldo histórico del portal en una ubicación pública','El archivo conserva componentes de una versión anterior y reactiva dudas sobre la gestión de copias de seguridad.','Un respaldo histórico del portal de INTERAFAS fue localizado en una ruta accesible desde Internet. El archivo corresponde a una versión anterior de la aplicación y contiene recursos que ya no forman parte de la publicación principal.

Especialistas señalan que los respaldos web suelen convertirse en una fuente de información valiosa para un atacante porque pueden conservar código antiguo, configuraciones o estructuras de directorios. INTERAFAS indicó que el archivo fue retirado y que revisará los procedimientos de respaldo y despliegue.

La institución no ha confirmado que el contenido haya sido utilizado de forma maliciosa, pero abrió una revisión de registros para conocer cuántas veces fue descargado.','Mesa de Tecnología','photo-evidence',49,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_04',0,'@pulso_flag_04','Conversación Metro','Metropolitano','Conversación','El clásico backup.zip viviendo su mejor vida en producción 😭',48,11,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_04');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_04',0,'@meme_flag_04','Memes Metropolitanos','Metropolitano','Humor','Ya empezaron los memes sobre el hallazgo. Internet tarda menos que cualquier comité.',132,40,NULL,'ADMINISTRACIÓN DE RESPALDOS','Nivel: “déjalo ahí, nadie lo va a encontrar”.' WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@meme_flag_04');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_05',0,'tecnologia','errores-tecnicos-exponen-detalles','Mensajes de error del portal muestran detalles internos del sistema','Capturas compartidas por usuarios revelan rutas y nombres técnicos que no deberían formar parte de una respuesta pública.','Capturas del portal ciudadano muestran mensajes de error con detalles sobre componentes internos de la aplicación. Aunque los mensajes surgieron durante operaciones fallidas, especialistas consideran que la información técnica puede ayudar a perfilar la plataforma.

INTERAFAS explicó que los sistemas de producción deberían mostrar mensajes genéricos al usuario y enviar el detalle completo únicamente a registros internos. El equipo técnico revisará configuraciones de depuración y manejo de excepciones.

No se reportó pérdida de servicio. Sin embargo, el incidente se suma a una serie de señales que han motivado una revisión más amplia del ciclo de desarrollo y publicación.','Mesa de Tecnología','photo-cyber',50,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_05',0,'@pulso_flag_05','Conversación Metro','Metropolitano','Conversación','Los errores enseñan más del servidor que algunos manuales.',55,13,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_05');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_05',0,'@reaccion_flag_05','Voces Metropolitanas','Metropolitano','Conversación','Pantalla roja con ruta completa: gracias por el tour técnico, supongo.',60,17,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_05');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_06',0,'servicios','funcion-restringida-accesible','Función restringida aparece disponible para usuarios sin el rol previsto','La institución revisa controles de autorización después de detectar una operación que podía ejecutarse fuera del perfil correspondiente.','INTERAFAS investiga una inconsistencia en los controles de acceso del portal ciudadano después de detectar que una función reservada para ciertos perfiles podía ser solicitada por usuarios con permisos menores.

La diferencia entre ocultar un botón y bloquear realmente una operación en el servidor es clave, explicaron especialistas consultados. Si el control existe solo en la interfaz, una solicitud directa puede eludirlo.

La institución indicó que revisará todas las funciones sensibles y no únicamente la ruta reportada. Hasta ahora no ha informado afectaciones financieras ni cambios irreversibles.','Unidad de Servicios','photo-control',51,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_06',0,'@pulso_flag_06','Conversación Metro','Metropolitano','Conversación','Ocultar el botón no es seguridad. Hoy aprendimos todos.',62,15,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_06');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_06',0,'@reaccion_flag_06','Voces Metropolitanas','Metropolitano','Conversación','Si mi usuario no tiene permiso, el servidor debería decir no. Punto.',69,20,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_06');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_07',0,'investigacion','consulta-proveedores-responde-de-forma-anomala','Consulta de proveedores muestra comportamiento anómalo ante entradas manipuladas','El patrón de respuesta activa una revisión de seguridad sobre cómo el portal procesa búsquedas y filtros.','Una función de búsqueda relacionada con proveedores comenzó a ser revisada después de que pruebas controladas detectaran diferencias consistentes ante determinados valores de entrada. La aplicación no muestra errores directos, pero su comportamiento sugiere que la validación merece una revisión más profunda.

Especialistas explican que las vulnerabilidades de inyección no siempre producen mensajes evidentes; algunas solo pueden inferirse comparando respuestas, tiempos o condiciones. INTERAFAS informó que aplicará consultas parametrizadas y validaciones adicionales.

La revisión se mantiene en curso y la institución no ha confirmado acceso a información sensible.','Unidad de Investigación','photo-cyber',52,0,1);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_07',0,'@pulso_flag_07','Conversación Metro','Metropolitano','Conversación','La búsqueda de proveedores se comporta raro con ciertos caracteres. Qué casualidad.',69,17,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_07');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_07',0,'@reaccion_flag_07','Voces Metropolitanas','Metropolitano','Conversación','Cuando una búsqueda tarda justo distinto según lo que escribes… 👀',78,23,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_07');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_08',0,'comunidad','comentario-alterado-permanece-visible','Contenido enviado por usuarios reaparece sin el tratamiento esperado','Moderadores detectan que ciertas entradas permanecen almacenadas y se muestran a otros visitantes.','El equipo de moderación de INTERAFAS revisa una falla en el tratamiento de contenido enviado por usuarios después de observar que determinadas entradas permanecían almacenadas y podían reproducirse en visitas posteriores.

El problema pone el foco en la necesidad de separar texto de instrucciones interpretables por el navegador. Especialistas recuerdan que cualquier contenido aportado por terceros debe codificarse antes de mostrarse.

La institución deshabilitó temporalmente la función afectada mientras se revisan publicaciones existentes y controles de sanitización.','Comunidad','photo-press',53,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_08',0,'@pulso_flag_08','Conversación Metro','Metropolitano','Conversación','Ya vi el comentario extraño replicándose. Moderación va a tener tarde larga.',76,19,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_08');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_08',0,'@reaccion_flag_08','Voces Metropolitanas','Metropolitano','Conversación','Internet: “solo era un comentario”. Navegador: “entiendo, ejecutaré la creatividad”.',87,26,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_08');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_09',0,'investigacion','descargas-permiten-salir-de-ruta','Módulo de documentos permite solicitar rutas fuera del directorio previsto','INTERAFAS restringe descargas mientras revisa la forma en que la aplicación construye rutas de archivo.','El módulo de documentos de INTERAFAS fue restringido después de que una prueba interna demostrara que ciertas solicitudes podían intentar salir del directorio destinado a archivos públicos.

La institución explicó que los nombres proporcionados por el usuario nunca deberían convertirse directamente en rutas del sistema. La corrección incluirá listas permitidas, normalización de rutas y separación física de archivos sensibles.

El análisis forense busca determinar si hubo acceso efectivo a recursos no autorizados o únicamente intentos dentro del entorno de prueba.','Unidad de Investigación','photo-evidence',54,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_09',0,'@pulso_flag_09','Conversación Metro','Metropolitano','Conversación','Descargar documentos no debería sentirse como explorar el servidor completo.',83,21,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_09');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_09',0,'@reaccion_flag_09','Voces Metropolitanas','Metropolitano','Conversación','Ese módulo necesita límites, no fe en que nadie cambie la ruta.',96,29,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_09');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_10',0,'tecnologia','sesiones-requieren-revision','INTERAFAS revisa manejo de sesiones después de detectar inconsistencias','El organismo analiza duración, invalidación y controles asociados con la identidad del usuario.','INTERAFAS inició una revisión de su administración de sesiones después de detectar comportamientos que podían permitir que una sesión continuara siendo útil más tiempo del previsto o bajo condiciones distintas a las esperadas.

La gestión de sesiones es uno de los puntos más sensibles de un portal ciudadano porque concentra la identidad ya autenticada. Revocar correctamente sesiones, rotar identificadores y validar acciones críticas reduce el impacto de un posible robo o reutilización.

El organismo anunció cambios preventivos y podría forzar nuevos inicios de sesión mientras concluyen las pruebas.','Mesa de Tecnología','photo-control',55,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_10',0,'@pulso_flag_10','Conversación Metro','Metropolitano','Conversación','Me volvió a pedir iniciar sesión. Si es por seguridad, bien; avisen nada más.',90,23,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_10');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_10',0,'@reaccion_flag_10','Voces Metropolitanas','Metropolitano','Conversación','Prefiero volver a entrar que descubrir que una sesión dura para siempre.',105,32,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_10');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_11',0,'investigacion','api-no-documentada-aparece-en-revision','Una API no documentada aparece durante la revisión del portal','La ruta amplía el mapa de servicios internos y genera preguntas sobre inventario y exposición.','Una interfaz de programación no documentada públicamente fue localizada durante la revisión técnica de INTERAFAS. La ruta responde desde la misma infraestructura del portal y expone funciones que no aparecen en la navegación convencional.

Especialistas señalan que los servicios olvidados o sin inventario suelen escapar de controles de mantenimiento y monitoreo. INTERAFAS indicó que verificará propietario, propósito, autenticación y necesidad operativa de cada endpoint.

El hallazgo no implica por sí mismo una vulneración, pero amplía la superficie que el equipo deberá auditar.','Unidad de Investigación','photo-cyber',56,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_11',0,'@pulso_flag_11','Conversación Metro','Metropolitano','Conversación','¿Por qué hay una API que nadie menciona en la documentación pública?',97,25,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_11');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_11',0,'@reaccion_flag_11','Voces Metropolitanas','Metropolitano','Conversación','Inventario de sistemas: ese Excel que todos juran que existe.',114,35,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_11');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_12',0,'servicios','api-muestra-cuenta-distinta','API de servicios devuelve información asociada con otra cuenta','La revisión se centra en autorización por objeto y en la exposición potencial de información de usuarios.','La investigación del portal ciudadano detectó que una API podía devolver información de una cuenta de servicio distinta cuando se modificaba su identificador. INTERAFAS analiza el número de registros potencialmente consultables y los accesos realizados.

El hallazgo es relevante porque una aplicación puede autenticar correctamente a un usuario y aun así fallar al autorizar qué objetos específicos puede consultar. Ese tipo de error afecta directamente la confidencialidad de datos.

El organismo restringió temporalmente la función y notificó a sus equipos jurídico y de protección de datos para evaluar obligaciones adicionales.','Unidad de Servicios','photo-evidence',57,0,1);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_12',0,'@pulso_flag_12','Conversación Metro','Metropolitano','Conversación','Otra cuenta, mismos endpoints. Esto ya dejó de parecer un error visual.',104,27,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_12');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_12',0,'@reaccion_flag_12','Voces Metropolitanas','Metropolitano','Conversación','Si cambias un número y aparece otro usuario, tenemos problema.',123,38,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_12');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_13',0,'economia','flujo-pago-bajo-revision','Flujo de pago digital entra a revisión por una inconsistencia de lógica','Especialistas analizan si ciertos parámetros podían modificar el resultado esperado sin romper técnicamente la aplicación.','El módulo de pagos de INTERAFAS fue incluido en una revisión especial después de detectar una inconsistencia en la lógica con la que valida referencias, montos y estados de una operación.

A diferencia de un error puramente técnico, las fallas de lógica de negocio aparecen cuando el sistema ejecuta exactamente lo programado, pero permite secuencias que contradicen las reglas del servicio. La institución revisa si alguna transacción real pudo verse afectada.

INTERAFAS señaló que conciliará operaciones recientes y reforzará verificaciones del lado del servidor antes de confirmar pagos.','Economía','photo-distribution',58,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_13',0,'@pulso_flag_13','Conversación Metro','Metropolitano','Conversación','En pagos, “funciona” no significa “cumple las reglas”.',111,29,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_13');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_13',0,'@reaccion_flag_13','Voces Metropolitanas','Metropolitano','Conversación','La lógica de negocio también se hackea; no todo son puertos y contraseñas.',132,41,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_13');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_14',0,'investigacion','proveedores-permisos-cruzados','Revisión de contrataciones detecta permisos cruzados entre proveedor y contrato','El hallazgo abre preguntas sobre segregación de funciones y administración de terceros.','Una revisión del módulo de contrataciones detectó que ciertas relaciones entre proveedores, contratos y documentos podían consultarse o modificarse con permisos más amplios de lo necesario.

La administración de terceros es especialmente sensible porque concentra documentos, anexos y procesos que cruzan varias áreas. Especialistas recomiendan controles por rol, por objeto y trazabilidad de cada modificación.

INTERAFAS informó que Jurídico, Administración y Tecnologías participan ya en una revisión conjunta del módulo.','Unidad de Investigación','photo-press',59,0,0);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_14',0,'@pulso_flag_14','Conversación Metro','Metropolitano','Conversación','Contratos, proveedores y permisos mezclados: receta para auditoría de fin de semana.',118,31,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_14');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_14',0,'@reaccion_flag_14','Voces Metropolitanas','Metropolitano','Conversación','A ver si ahora sí publican quién puede tocar qué en contrataciones.',141,44,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_14');
INSERT IGNORE INTO news_articles (phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking) VALUES (0,'FLAG_15',0,'investigacion','configuracion-interna-revela-arquitectura','Archivo de configuración expone referencias a servicios internos de INTERAFAS','El hallazgo conecta por primera vez el portal público con componentes que no deberían ser visibles desde Internet.','La revisión del portal de INTERAFAS encontró referencias a nombres y servicios internos que permiten reconstruir parte de la arquitectura detrás de la aplicación pública. Aunque no constituyen acceso directo, las referencias son relevantes porque muestran dependencias que deberían permanecer separadas.

Especialistas consultados advierten que la información arquitectónica reduce la incertidumbre de un atacante y puede orientar intentos hacia sistemas más sensibles. INTERAFAS inició una revisión de secretos, configuraciones y segmentación.

El organismo aseguró que los componentes operacionales continúan bajo controles independientes, aunque confirmó que el hallazgo elevó la prioridad del análisis.','Unidad de Investigación','photo-scada',60,0,1);
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_15',0,'@pulso_flag_15','Conversación Metro','Metropolitano','Conversación','Ya están hablando de nombres internos y arquitectura. Esto escaló rápido.',125,33,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@pulso_flag_15');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_15',0,'@reaccion_flag_15','Voces Metropolitanas','Metropolitano','Conversación','Cuando el mapa interno aparece en un archivo público, el atacante deja de caminar a ciegas.',150,47,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@reaccion_flag_15');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_16',0,'@flag16_extra','Voces Metropolitanas','Metropolitano','Conversación','Cuadrillas afuera y el mapa cambiando. Ya no parece un reporte aislado.',356,109,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag16_extra');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_16',0,'@flag16_extra2','Voces Metropolitanas','Metropolitano','Conversación','Primero dijeron normalidad, ahora hablan de anomalías. Necesitamos tiempos y zonas claras.',379,118,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag16_extra2');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_17',0,'@flag17_extra','Voces Metropolitanas','Metropolitano','Conversación','Si ya confirmaron incidente tecnológico, publiquen qué servicios están aislados y cuáles siguen confiables.',367,113,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag17_extra');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_17',0,'@flag17_extra2','Voces Metropolitanas','Metropolitano','Conversación','La gente está llenando grupos de capturas. Comunicación oficial tiene que ir más rápido.',390,122,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag17_extra2');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_18',0,'@flag18_extra','Voces Metropolitanas','Metropolitano','Conversación','Cuando operación y tecnología empiezan a hablar juntos, sabes que el asunto ya cruzó fronteras internas.',378,117,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag18_extra');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_18',0,'@flag18_extra2','Voces Metropolitanas','Metropolitano','Conversación','¿Firmware en medio de una crisis? Esta temporada viene con demasiados giros.',401,126,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag18_extra2');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_19',0,'@flag19_extra','Voces Metropolitanas','Metropolitano','Conversación','Ya no es una falla: es una cadena. Y ahora todos quieren saber dónde empezó.',389,121,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag19_extra');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_19',0,'@flag19_extra2','Voces Metropolitanas','Metropolitano','Conversación','Contratos, accesos, sistemas internos… cada respuesta abre tres preguntas nuevas.',412,130,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag19_extra2');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_20',0,'@flag20_extra','Voces Metropolitanas','Metropolitano','Conversación','Esto ya dejó de ser “tema de sistemas”. Hay consecuencias en la calle.',400,125,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag20_extra');
INSERT INTO scenario_social_posts (phase_required,event_required,delay_seconds,handle,display_name,municipality,label,content,likes,shares,party,meme_title,meme_caption) SELECT 0,'FLAG_20',0,'@flag20_extra2','Voces Metropolitanas','Metropolitano','Conversación','Que investiguen todo, pero primero que estabilicen el servicio y atiendan a la gente.',423,134,NULL,NULL,NULL WHERE NOT EXISTS (SELECT 1 FROM scenario_social_posts WHERE handle='@flag20_extra2');


-- v0.5.0 · Auditor refinement, navigation telemetry and progressive hints
CREATE TABLE IF NOT EXISTS flag_hints (
  flag_number INT NOT NULL,
  hint_order TINYINT NOT NULL,
  hint_text VARCHAR(500) NOT NULL,
  PRIMARY KEY(flag_number,hint_order),
  CONSTRAINT fk_hint_catalog FOREIGN KEY(flag_number) REFERENCES flag_catalog(flag_number)
);
CREATE TABLE IF NOT EXISTS lab_hint_usage (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  student_id INT NOT NULL,
  flag_number INT NOT NULL,
  hint_order TINYINT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_attempt_hint(attempt_id,flag_number,hint_order),
  INDEX idx_hint_usage_attempt(attempt_id,created_at),
  CONSTRAINT fk_hint_usage_attempt FOREIGN KEY(attempt_id) REFERENCES lab_attempts(id),
  CONSTRAINT fk_hint_usage_student FOREIGN KEY(student_id) REFERENCES lab_students(id),
  CONSTRAINT fk_hint_usage_catalog FOREIGN KEY(flag_number) REFERENCES flag_catalog(flag_number)
);
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(1,1,'Empieza por una comprobación básica que muchos olvidan: revisa qué rutas pide el propio sitio que los buscadores no indexen.'),
(1,2,'Un servidor puede mostrar más de lo que la aplicación decidió enlazar. Revisa nombres y artefactos que parezcan residuales.'),
(2,1,'Mira más allá de lo que la página muestra en pantalla. Revisa también los recursos que el navegador carga para construirla.'),
(2,2,'La configuración de ejecución del frontend puede conservar referencias que el usuario normal nunca necesita abrir.'),
(3,1,'Observa qué identificador utiliza el portal cuando abre tu expediente.'),
(3,2,'Estar autenticado no significa que el servidor haya comprobado que el objeto solicitado te pertenece.'),
(4,1,'Una página actual puede tener versiones anteriores que nunca debieron quedar dentro del directorio público. Piensa en cómo editores y despliegues suelen nombrar esas copias.'),
(4,2,'Cuando cambia la extensión de un archivo dinámico, el servidor puede dejar de interpretarlo y entregar su contenido como un archivo ordinario.'),
(5,1,'Las funciones de exportación suelen recibir parámetros para decidir qué conjunto de datos entregar. Revisa la solicitud que genera el botón CSV y observa qué valor controla el contenido solicitado.'),
(5,2,'Un error de producción debería ser genérico. Si la respuesta empieza a hablar de archivos, líneas, excepciones o servicios internos, observa todo lo que revela.'),
(6,1,'Accede a la función reservada con tu sesión ciudadana y analiza la transacción HTTP completa. Además de lo que envía el navegador, revisa con atención la respuesta del servidor.'),
(6,2,'Si algún valor controlado por el cliente parece indicar el rol o privilegio de la sesión, prueba a modificar únicamente ese valor y repite la solicitud.'),
(7,1,'Si puedes distinguir una condición verdadera de una falsa, ya tienes un canal de comunicación con la base de datos aunque la aplicación no muestre resultados ni errores.'),
(7,2,'MySQL/MariaDB mantiene metadatos sobre bases, tablas y columnas. Investiga DATABASE() e information_schema antes de intentar localizar datos concretos.'),
(8,1,'Busca una entrada ciudadana que no solo se envíe, sino que quede almacenada y pueda consultarse después en otra vista.'),
(8,2,'Si consigues ejecutar JavaScript desde el contenido almacenado, inspecciona también el HTML de la vista de seguimiento. Puede contener información útil para acreditar la ejecución mediante una petición HTTP normal.'),
(9,1,'Observa qué parámetro usa la función de descarga para seleccionar el archivo. Prueba distintas profundidades de navegación de directorios y contrasta el resultado con archivos estándar de Linux como hostname, os-release o passwd.'),
(9,2,'Una vez confirmado que puedes leer fuera del directorio permitido, investiga otros archivos de texto estándar del sistema. En Linux, motd se utiliza para mensajes del sistema y puede aportar información adicional en este laboratorio.'),
(10,1,'Compara el valor de la cookie de sesión antes y después de iniciar sesión. Un cambio de estado de anónimo a autenticado debería ir acompañado de una rotación del identificador.'),
(10,2,'Si el identificador se conserva, reutiliza el valor observado antes del login desde un segundo cliente HTTP y revisa tanto el acceso obtenido como los encabezados de la respuesta.'),
(11,1,'No todos los endpoints aparecen en la interfaz.'),
(11,2,'Revisa recursos, scripts y patrones de rutas que sugieran una API no documentada.'),
(12,1,'Una API también debe validar la propiedad del objeto solicitado.'),
(12,2,'Compara respuestas al variar identificadores de cuentas de servicio.'),
(13,1,'Sigue el flujo completo de pago, no solo el formulario visible.'),
(13,2,'Busca qué valores confía el servidor entre una etapa y la siguiente.'),
(14,1,'Relaciona proveedores, contratos y permisos como entidades separadas.'),
(14,2,'Un vínculo válido entre dos registros no significa que el usuario esté autorizado a alterarlo.'),
(15,1,'La arquitectura interna suele asomarse en configuraciones, errores o documentación operativa.'),
(15,2,'Busca referencias a nombres de servicios, hosts o redes que el usuario común no necesita conocer.'),
(16,1,'Identifica dónde termina la aplicación pública y comienza la infraestructura operacional.'),
(16,2,'Busca una puerta de enlace o ruta que conecte el entorno IT con servicios internos.'),
(17,1,'Una interfaz de monitoreo debería exigir autorización propia.'),
(17,2,'Si encuentras el HMI, comprueba qué valida realmente antes de mostrar variables.'),
(18,1,'Analiza qué valida el actualizador antes de aceptar un paquete.'),
(18,2,'Integridad y autenticidad no son lo mismo: revisa manifiesto, hash y firma del firmware ficticio.'),
(19,1,'Esta bandera no depende de un solo fallo.'),
(19,2,'Relaciona lo descubierto en web, arquitectura, HMI y mantenimiento para reconstruir una cadena completa.'),
(20,1,'El objetivo final es demostrar impacto operacional simulado, no solo acceso.'),
(20,2,'Busca qué acción autorizada por la cadena previa modifica el estado de P-101 dentro del simulador.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);


-- v0.5.1 · Auditor support + progressive INTERAFAS news gating
ALTER TABLE news_articles ADD COLUMN IF NOT EXISTS interafas_related TINYINT(1) NOT NULL DEFAULT 0 AFTER breaking;
UPDATE news_articles SET interafas_related=1
WHERE event_required IS NOT NULL
   OR headline LIKE '%INTERAFAS%'
   OR subheadline LIKE '%INTERAFAS%'
   OR body LIKE '%INTERAFAS%';
UPDATE news_articles SET interafas_related=0
WHERE event_required IS NULL
  AND headline NOT LIKE '%INTERAFAS%'
  AND subheadline NOT LIKE '%INTERAFAS%'
  AND body NOT LIKE '%INTERAFAS%';
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


-- ===== v0.5.4 · Progressive opinion publication =====
-- Opinion pieces are released as the student advances through early/mid challenges.
UPDATE news_articles SET event_required='FLAG_03'
WHERE slug='entrevista-experta-infraestructura-no-se-protege-sola';
UPDATE news_articles SET event_required='FLAG_06'
WHERE slug='voces-calle-servicio-publico-confianza';
UPDATE news_articles SET event_required='FLAG_09'
WHERE slug='pingo-la-nube-no-arregla-tuberias';
UPDATE news_articles SET event_required='FLAG_12'
WHERE slug='reporte-especial-ciudad-dependencias-invisibles';
