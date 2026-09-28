-- INTERAFAS-LAB · Naturalización editorial del portal Pulso Metropolitano
-- FLAGS 01–12: reemplazo por consecuencias observables.
-- FLAGS 13–20: se conservan notas existentes y se agregan coberturas complementarias.
SET NAMES utf8mb4;

-- FLAG 01
UPDATE news_articles SET
  category='local',
  slug='interafas-retira-archivos-publicados-por-error',
  headline='INTERAFAS retira varios archivos de su portal tras una publicación accidental',
  subheadline='Usuarios detectaron documentos que aparecieron durante unas horas en una sección pública; el organismo revisa qué información estuvo disponible.',
  body='Usuarios del portal de INTERAFAS reportaron que durante varias horas aparecieron documentos que no formaban parte del catálogo habitual de información pública. Algunos archivos desaparecieron posteriormente sin que existiera un aviso previo sobre su publicación.\n\nEl organismo confirmó que retiró preventivamente varios elementos mientras revisa su procedencia, el tiempo que permanecieron accesibles y si contenían información que debía mantenerse fuera del portal. La revisión incluye registros de publicación y accesos realizados durante la ventana señalada.\n\nINTERAFAS indicó que, por el momento, no existe evidencia de afectación al servicio de agua. El área de Transparencia anunció que publicará una relación de los documentos que deban permanecer disponibles y corregirá cualquier publicación realizada por error.',
  author='Redacción',
  hero_asset='photo-evidence',
  breaking=0
WHERE slug='archivo-publico-genera-preguntas';

-- FLAG 02
UPDATE news_articles SET
  category='tecnologia',
  slug='capturas-version-interna-portal-circulan-redes',
  headline='Capturas de una versión interna del portal comienzan a circular en redes',
  subheadline='Las imágenes muestran nombres de módulos y secciones que no aparecen normalmente en la navegación pública.',
  body='Capturas compartidas en redes sociales muestran referencias a secciones y nombres de módulos que no forman parte de la navegación habitual del portal de INTERAFAS. Las imágenes comenzaron a circular acompañadas de preguntas sobre si corresponden a funciones en desarrollo o a componentes retirados.\n\nLa institución señaló que revisa el material y que algunos nombres pueden pertenecer a elementos utilizados durante procesos de desarrollo y mantenimiento. También anunció una revisión para determinar qué información resulta innecesaria en las páginas entregadas al público.\n\nHasta ahora no se reportan cambios en el servicio ni afectaciones a cuentas ciudadanas relacionadas con estas capturas.',
  author='Mesa Digital',
  hero_asset='photo-cyber',
  breaking=0
WHERE slug='codigo-cliente-revela-pistas';

-- FLAG 03
UPDATE news_articles SET
  category='servicios',
  slug='usuarios-recibos-datos-otras-cuentas',
  headline='Usuarios denuncian que al consultar su recibo aparecieron datos de otras cuentas',
  subheadline='INTERAFAS investiga reportes de domicilios, consumos y saldos que no correspondían al titular de la sesión.',
  body='Usuarios del portal ciudadano denunciaron que, al consultar información de su servicio, aparecieron datos asociados con cuentas que no reconocen. Entre los elementos reportados se encuentran domicilios, consumos históricos y saldos correspondientes a otros contratos.\n\nINTERAFAS informó que restringió temporalmente algunas consultas mientras determina el número de personas afectadas y revisa los accesos realizados. El área de Protección de Datos fue incorporada al análisis para valorar las medidas de notificación que correspondan.\n\nAtención Ciudadana pidió a quienes encuentren información ajena no compartirla y reportar el incidente mediante los canales oficiales.',
  author='Unidad de Servicios',
  hero_asset='photo-control',
  breaking=1
WHERE slug='usuarios-reportan-datos-cruzados';

-- FLAG 04
UPDATE news_articles SET
  category='local',
  slug='documentos-retirados-reaparecen-portal',
  headline='Documentos eliminados hace meses reaparecen temporalmente en el portal de INTERAFAS',
  subheadline='Versiones antiguas de formularios y materiales institucionales volvieron a estar disponibles durante una revisión del sitio.',
  body='Usuarios detectaron que documentos retirados meses atrás volvieron a aparecer temporalmente entre los materiales disponibles en el portal de INTERAFAS. Entre ellos había versiones anteriores de formularios, instructivos y documentos institucionales que ya habían sido sustituidos.\n\nEl organismo indicó que realiza una revisión del historial de publicaciones y que retiró nuevamente los materiales mientras determina por qué volvieron a quedar accesibles. La institución también verificará si existen otras versiones antiguas disponibles desde enlaces que ya no aparecen en la navegación.\n\nNo se informó de cambios en trámites vigentes, aunque Atención Ciudadana pidió utilizar únicamente los formatos actualmente señalados como oficiales.',
  author='Redacción',
  hero_asset='photo-evidence',
  breaking=0
WHERE slug='respaldo-historico-expuesto';

-- FLAG 05
UPDATE news_articles SET
  category='servicios',
  slug='fallas-portal-interrumpen-tramites',
  headline='Fallas en el portal dejan a usuarios sin completar trámites durante varios minutos',
  subheadline='Ciudadanos compartieron capturas de pantallas de error mientras intentaban consultar recibos y dar seguimiento a solicitudes.',
  body='El portal ciudadano de INTERAFAS presentó errores intermitentes que impidieron a algunos usuarios completar consultas y trámites durante varios minutos. En redes sociales circularon capturas de mensajes inesperados y páginas que dejaban de responder antes de concluir una operación.\n\nEl organismo informó que el servicio continuó disponible de manera parcial y que su equipo técnico revisa la configuración responsable de mostrar información innecesaria durante determinados fallos. Los canales telefónicos y presenciales permanecieron abiertos.\n\nINTERAFAS recomendó no repetir pagos u operaciones que hubieran quedado en estado incierto hasta confirmar su situación dentro del historial de movimientos.',
  author='Unidad de Servicios',
  hero_asset='support-team.svg',
  breaking=0
WHERE slug='errores-tecnicos-exponen-detalles';

-- FLAG 06
UPDATE news_articles SET
  category='servicios',
  slug='usuarios-cambios-tramites-no-autorizados',
  headline='Usuarios reportan cambios en trámites que aseguran no haber autorizado',
  subheadline='Solicitudes y datos de algunos expedientes aparecen modificados; INTERAFAS revisa quién ejecutó las operaciones.',
  body='Varios usuarios reportaron modificaciones en solicitudes y expedientes digitales que aseguran no haber realizado. Los casos incluyen cambios de datos, movimientos de estatus y operaciones que normalmente requieren un perfil específico dentro del portal.\n\nINTERAFAS inició una revisión de la bitácora de acciones y restringió temporalmente algunas funciones mientras identifica qué cuentas y expedientes estuvieron involucrados. La institución señaló que los movimientos serán contrastados con registros de acceso y atención.\n\nLos ciudadanos afectados recibirán apoyo para restablecer la información correcta cuando se confirme que una modificación no fue solicitada por el titular.',
  author='Unidad de Servicios',
  hero_asset='photo-control',
  breaking=1
WHERE slug='funcion-restringida-accesible';

-- FLAG 07
UPDATE news_articles SET
  category='economia',
  slug='padron-proveedores-registros-inconsistentes',
  headline='Padrón de proveedores presenta registros incompletos y resultados inconsistentes',
  subheadline='Empresas reportaron diferencias al consultar contratos y referencias dentro del portal institucional.',
  body='Proveedores registrados ante INTERAFAS reportaron resultados inconsistentes al consultar información asociada con contratos y referencias administrativas. Algunas búsquedas mostraban registros incompletos, mientras que otras devolvían resultados diferentes al repetir la consulta.\n\nEl área administrativa informó que congeló temporalmente ciertas funciones de consulta mientras se compara la información del portal con los expedientes originales. Contraloría solicitó preservar los registros de cambios y accesos realizados durante el periodo señalado.\n\nLa institución aclaró que una inconsistencia en la consulta pública no implica por sí sola que los expedientes contractuales hayan sido alterados, por lo que la revisión continuará sobre las fuentes originales.',
  author='Economía',
  hero_asset='photo-press',
  breaking=0
WHERE slug='consulta-proveedores-responde-de-forma-anomala';

-- FLAG 08
UPDATE news_articles SET
  category='servicios',
  slug='interafas-suspende-comentarios-redirecciones',
  headline='INTERAFAS suspende su módulo de comentarios tras ventanas y redirecciones inesperadas',
  subheadline='Visitantes reportaron avisos no reconocidos y cambios de página al consultar algunas publicaciones.',
  body='INTERAFAS suspendió temporalmente el módulo de comentarios de su portal después de que usuarios reportaran ventanas inesperadas, mensajes ajenos al diseño institucional y redirecciones al consultar algunas publicaciones.\n\nEl organismo indicó que revisará el contenido almacenado en la sección antes de volver a habilitarla. También pidió a los usuarios no introducir contraseñas ni datos personales en ventanas que aparezcan fuera de los formularios oficiales.\n\nEl incidente se encuentra limitado al componente de interacción con usuarios y, hasta el momento, no se han informado afectaciones al servicio hidráulico.',
  author='Mesa Digital',
  hero_asset='photo-cyber',
  breaking=1
WHERE slug='comentario-alterado-permanece-visible';

-- FLAG 09
UPDATE news_articles SET
  category='investigacion',
  slug='documentos-internos-mezclados-publicos',
  headline='Documentos internos aparecen mezclados con archivos públicos en el portal',
  subheadline='La institución restringió temporalmente las descargas después de detectar materiales que no estaban destinados a consulta ciudadana.',
  body='Documentos de trabajo interno aparecieron entre archivos que podían descargarse desde una sección pública del portal de INTERAFAS. Los materiales incluyen plantillas, minutas y archivos administrativos que normalmente no forman parte del catálogo ciudadano.\n\nLa institución restringió temporalmente las descargas mientras revisa el inventario completo y determina qué documentos pudieron ser consultados. Jurídico y Transparencia participan en la clasificación del material.\n\nINTERAFAS señaló que cualquier documento cuya publicación sea obligatoria volverá a estar disponible una vez concluida la revisión, mientras que los archivos internos serán separados del repositorio público.',
  author='Unidad de Investigación',
  hero_asset='photo-evidence',
  breaking=1
WHERE slug='descargas-permiten-salir-de-ruta';

-- FLAG 10
UPDATE news_articles SET
  category='servicios',
  slug='usuarios-expedientes-modificados-no-autorizados',
  headline='Usuarios reportan modificaciones y cancelaciones en expedientes que no realizaron',
  subheadline='INTERAFAS fuerza el cierre de sesiones activas y pide revisar movimientos recientes dentro del portal ciudadano.',
  body='Usuarios del portal ciudadano reportaron que algunos expedientes mostraban modificaciones, cancelaciones o cambios de información que no recuerdan haber realizado. Los casos comenzaron a concentrarse durante la misma ventana de tiempo y motivaron una revisión de accesos recientes.\n\nINTERAFAS ordenó el cierre preventivo de sesiones activas y solicitó a los ciudadanos volver a autenticarse. También recomendó revisar movimientos recientes y reportar cualquier operación no reconocida para iniciar su reversión.\n\nEl organismo analiza si una sesión ya iniciada pudo continuar siendo utilizada bajo condiciones distintas a las esperadas. Por el momento no existe una cifra consolidada de expedientes afectados.',
  author='Unidad de Servicios',
  hero_asset='photo-control',
  breaking=1
WHERE slug='sesiones-requieren-revision';

-- FLAG 11
UPDATE news_articles SET
  category='tecnologia',
  slug='servicios-retirados-siguen-respondiendo',
  headline='Servicios retirados del portal continúan respondiendo desde aplicaciones antiguas',
  subheadline='Enlaces y funciones que ya no aparecen en la navegación siguen disponibles desde versiones anteriores del sistema.',
  body='Una revisión del ecosistema digital de INTERAFAS detectó que algunos servicios retirados de la navegación pública continuaban respondiendo desde aplicaciones y enlaces antiguos. Usuarios y personal técnico pudieron acceder a funciones que oficialmente ya no forman parte del portal vigente.\n\nEl organismo inició un inventario de aplicaciones históricas para determinar cuáles deben permanecer operativas y cuáles pueden ser retiradas definitivamente. La revisión incluye propietarios, responsables de mantenimiento y controles de acceso.\n\nTecnologías señaló que mantener servicios olvidados incrementa la complejidad de operación y dificulta conocer con precisión qué componentes siguen expuestos al público.',
  author='Mesa Digital',
  hero_asset='photo-cyber',
  breaking=0
WHERE slug='api-no-documentada-aparece-en-revision';

-- FLAG 12
UPDATE news_articles SET
  category='servicios',
  slug='clientes-notificaciones-datos-ajenos',
  headline='Clientes reciben notificaciones con domicilios y consumos que no les pertenecen',
  subheadline='INTERAFAS revisa el envío y consulta de información tras reportes de datos cruzados entre cuentas de servicio.',
  body='Clientes de INTERAFAS reportaron notificaciones y consultas que mostraban domicilios, consumos o referencias que no pertenecían a su contrato. Algunos casos fueron detectados al ingresar al portal y otros a partir de avisos generados por los servicios digitales.\n\nLa institución suspendió temporalmente determinadas consultas y comenzó a identificar las cuentas que pudieron recibir información ajena. El área de Protección de Datos participa en la revisión del alcance y de las medidas que deberán comunicarse a los afectados.\n\nINTERAFAS pidió no compartir capturas que contengan información de terceros y utilizar los canales oficiales para reportar cualquier dato que no corresponda al titular.',
  author='Unidad de Servicios',
  hero_asset='photo-evidence',
  breaking=1
WHERE slug='api-muestra-cuenta-distinta';

-- Coberturas complementarias FLAGS 13–20. Las notas existentes se conservan.
INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 0,'FLAG_13',0,'economia','pagos-recibos-montos-no-coinciden',
'INTERAFAS revisa pagos digitales tras detectar recibos liquidados por montos que no coinciden',
'Finanzas inició una conciliación extraordinaria después de encontrar diferencias entre importes pagados y estados registrados en algunas cuentas.',
'INTERAFAS inició una conciliación extraordinaria de operaciones digitales después de detectar cuentas cuyo estado de pago no coincide con el importe registrado. Algunos usuarios observaron recibos marcados como cubiertos pese a existir diferencias y otros reportaron saldos inesperados después de una operación.\n\nEl área financiera informó que revisará movimientos recientes antes de realizar ajustes y pidió conservar comprobantes. La institución señaló que ningún usuario perderá un pago correctamente acreditado mientras se reconstruye la secuencia de operaciones.\n\nLa revisión busca determinar si las diferencias obedecen a errores de procesamiento, secuencias incompletas o modificaciones no previstas dentro del flujo digital.',
'Economía','payment-online.svg',62,1,0,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='pagos-recibos-montos-no-coinciden');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 0,'FLAG_14',0,'investigacion','proveedor-anexos-contrato-ajeno',
'Proveedor denuncia que pudo consultar anexos de un contrato ajeno',
'Jurídico y Contraloría revisan permisos después de que una empresa reportara documentos correspondientes a otro procedimiento.',
'Un proveedor notificó a INTERAFAS que, durante una consulta de su expediente, tuvo acceso a anexos que aparentemente correspondían a otro contrato. La empresa informó el hallazgo sin descargar material adicional y pidió que se revisara la segregación entre expedientes.\n\nJurídico, Administración y Contraloría iniciaron una revisión de accesos para determinar qué documentos pudieron quedar visibles y durante cuánto tiempo. Los proveedores involucrados serán contactados si se confirma exposición de información.\n\nINTERAFAS señaló que la revisión no prejuzga la integridad de los procedimientos contractuales, pero reconoció que los permisos entre expedientes deben mantenerse estrictamente separados.',
'Unidad de Investigación','photo-press',63,1,0,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='proveedor-anexos-contrato-ajeno');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 0,'FLAG_15',0,'tecnologia','interafas-aisla-servicios-internos',
'INTERAFAS desconecta preventivamente varios servicios internos vinculados con su portal público',
'La medida busca reducir conexiones innecesarias mientras el organismo reconstruye el alcance del incidente digital.',
'INTERAFAS aisló preventivamente varios servicios internos después de detectar relaciones que no eran necesarias para mantener disponible el portal ciudadano. La medida forma parte de una revisión más amplia de dependencias entre aplicaciones públicas y componentes de uso interno.\n\nTecnologías indicó que algunos servicios permanecerán desconectados hasta confirmar que sus accesos y configuraciones corresponden con el diseño autorizado. Los trámites prioritarios serán atendidos mediante rutas alternas cuando sea necesario.\n\nLa institución señaló que el aislamiento busca evitar que una afectación en un componente público se propague hacia sistemas con funciones más sensibles.',
'Mesa Digital','photo-cyber',64,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='interafas-aisla-servicios-internos');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 1,'FLAG_16',0,'ultima','centro-control-restringe-accesos',
'Centro de control restringe accesos tras detectar una conexión operacional no reconocida',
'Operadores reforzaron verificaciones de origen y enviaron cuadrillas a estaciones prioritarias mientras se revisan registros.',
'El centro de operaciones de INTERAFAS restringió temporalmente accesos a sistemas de supervisión después de detectar una conexión que aparentaba provenir de un entorno autorizado, pero cuya procedencia deberá ser verificada.\n\nLa medida coincidió con el despliegue de cuadrillas para confirmar físicamente niveles, bombeo y presión en instalaciones prioritarias. Operadores señalaron que cualquier discrepancia entre pantalla y campo será tratada como una condición de contingencia.\n\nINTERAFAS no ha atribuido la conexión a una persona o grupo y mantiene bajo preservación los registros asociados.',
'Equipo de Última Hora','operations-room.svg',92,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='centro-control-restringe-accesos');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 2,'FLAG_17',0,'ultima','datos-bombas-tanques-circulan',
'Datos de bombas y niveles de tanques circulan en redes antes de ser publicados por INTERAFAS',
'Capturas con variables operativas comenzaron a difundirse mientras el organismo investigaba cómo terceros pudieron observar información de supervisión.',
'Capturas que muestran estados de bombas, niveles de tanques y otras variables del sistema comenzaron a circular en grupos y redes sociales antes de que INTERAFAS difundiera información equivalente por sus canales oficiales.\n\nEl organismo analiza el origen de las imágenes y pidió evitar su redistribución mientras se determina si corresponden a información real, histórica o manipulada. Personal operativo compara los valores difundidos con registros internos.\n\nLa institución reconoció que la exposición de información operacional, aun sin capacidad de modificación, puede aportar contexto sensible sobre el funcionamiento de la infraestructura.',
'Iván Rojas · Tecnología','photo-scada',106,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='datos-bombas-tanques-circulan');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 3,'FLAG_18',0,'ultima','estacion-mantenimiento-sin-orden',
'Una estación entra en modo de mantenimiento sin existir una orden programada',
'Operadores enviaron personal al sitio después de que un equipo reportara una condición de servicio que no aparecía en la programación del turno.',
'Una estación vinculada con la red metropolitana reportó una condición de mantenimiento sin que existiera una orden programada para ese periodo, confirmaron fuentes operativas de INTERAFAS. La discrepancia provocó el envío de personal para verificar físicamente el equipo y sus parámetros.\n\nMientras se realiza la revisión, se suspendieron cambios remotos no esenciales sobre el activo y se pidió validar cualquier actualización contra los registros de mantenimiento autorizados.\n\nEl organismo no ha informado si la condición fue causada por una acción humana, una actualización o un fallo del propio dispositivo, pero incorporó el evento a la investigación tecnológica en curso.',
'Carla Méndez · Enviada especial','photo-control',114,1,1,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='estacion-mantenimiento-sin-orden');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 3,'FLAG_19',0,'investigacion','mantenimiento-acceso-fuera-zona',
'Personal de mantenimiento pudo acceder a instalaciones fuera de su zona asignada, confirma INTERAFAS',
'La revisión detectó que una identidad de soporte tenía visibilidad sobre una cadena de activos mayor a la necesaria para su función.',
'INTERAFAS confirmó que una identidad utilizada para tareas de mantenimiento podía recorrer información y dependencias correspondientes a instalaciones fuera del ámbito inicialmente asignado. La institución revisa si esa capacidad fue utilizada durante la ventana del incidente.\n\nOperación y Tecnologías comenzaron a redefinir permisos por instalación, función y tipo de maniobra. También se revisarán las cuentas de terceros que participan en soporte especializado.\n\nLa investigación busca establecer qué acciones se realizaron efectivamente y cuáles eran únicamente posibles debido a la amplitud de los permisos disponibles.',
'Unidad de Investigación','photo-evidence',119,2,1,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='mantenimiento-acceso-fuera-zona');

INSERT INTO news_articles
(phase_required,event_required,delay_seconds,category,slug,headline,subheadline,body,author,hero_asset,priority,display_order,breaking,interafas_related)
SELECT 4,'FLAG_20',0,'ultima','variaciones-presion-aislan-sectores',
'Variaciones simultáneas de presión obligan a aislar sectores de la red metropolitana',
'Operadores reportaron caudal anormal, incremento de alarmas y caída de disponibilidad mientras cuadrillas ejecutaban maniobras locales.',
'INTERAFAS aisló preventivamente varios sectores de la red después de que el centro de operaciones registrara variaciones simultáneas de presión y caudal junto con un incremento abrupto de alarmas. La disponibilidad reportada por el sistema descendió mientras operadores trasladaban maniobras a campo.\n\nCuadrillas verificaron válvulas, estaciones de bombeo y puntos de presión para evitar que una condición transitoria se propagara a sectores adicionales. Servicios prioritarios fueron notificados para activar medidas preventivas.\n\nEl organismo investiga si los cambios observados responden a una secuencia de órdenes, una configuración alterada o una combinación de eventos operacionales. La recuperación se realizará de manera gradual para evitar nuevos transitorios.',
'Equipo de Última Hora','photo-control',155,2,1,1
WHERE NOT EXISTS (SELECT 1 FROM news_articles WHERE slug='variaciones-presion-aislan-sectores');

-- La nota del defacement conservará su título actual, pero utilizará captura real del portal.
UPDATE news_articles
SET hero_asset='snapshot-defacement'
WHERE slug='defacement-interafas-colectivo-umbral';

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
