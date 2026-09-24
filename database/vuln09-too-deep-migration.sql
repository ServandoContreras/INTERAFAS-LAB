-- VULN 09 · TOO-DEEP
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(9,1,'Observa qué parámetro usa la función de descarga para seleccionar el archivo. Prueba distintas profundidades de navegación de directorios y contrasta el resultado con archivos estándar de Linux como hostname, os-release o passwd.'),
(9,2,'Una vez confirmado que puedes leer fuera del directorio permitido, investiga otros archivos de texto estándar del sistema. En Linux, motd se utiliza para mensajes del sistema y puede aportar información adicional en este laboratorio.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
