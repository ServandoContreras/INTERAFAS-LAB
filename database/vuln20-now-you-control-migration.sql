-- VULN 20 · NOW-YOU-CONTROL
-- Impacto operacional final simulado mediante abuso de una prueba de lazo de mantenimiento.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(20,1,'La cadena anterior ya te mostró qué activos forman el camino operacional. Regresa al extremo terminal de VULN-19 y revisa si el HMI ofrece alguna función adicional de mantenimiento una vez completado el recorrido.'),
(20,2,'La nueva función está diseñada como una prueba de lazo en modo SIMULATION. Ejecútala normalmente y observa en Network qué JSON envía el navegador, especialmente el campo que decide si la prueba se aplica o no.'),
(20,3,'La interfaz no te permitirá emitir una maniobra real. El reto consiste en comprobar si el servidor confía demasiado en un valor controlado por el cliente para separar simulación de ejecución. Conserva asset, state y sesión; modifica únicamente esa decisión.'),
(20,4,'El primer objetivo es detener temporalmente el bombeo primario simulado. Después observa el Process View y la telemetría: debe existir evidencia de caída de caudal, caída de presión y alarmas. No restaures inmediatamente; deja que el HMI registre el impacto.'),
(20,5,'Después de confirmar visualmente el impacto, recupera el proceso utilizando el mismo canal y devuelve el bombeo a ON. La bandera sólo se entrega si el sistema registró primero el impacto y después una recuperación correcta.'),
(20,6,'En la respuesta HTTP de la recuperación revisa los Response Headers. Documenta estado inicial, maniobra, valores degradados, alarmas, estado recuperado y por qué un parámetro del cliente nunca debe habilitar una transición de simulación a escritura real.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
