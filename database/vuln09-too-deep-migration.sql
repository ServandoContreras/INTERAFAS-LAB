-- VULN 09 · TOO-DEEP
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(9,1,'Observa qué parámetro usa la función de descarga para seleccionar el archivo. Comprueba si el servidor trabaja con una ruta construida a partir de ese valor.'),
(9,2,'Si logras salir del directorio de documentos ciudadanos, busca primero un archivo de configuración relacionado con el almacén documental. Puede indicarte qué recurso interno intentar después.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
