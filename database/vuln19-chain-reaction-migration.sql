-- VULN 19 · CHAIN-REACTION
-- El reto explota exposición excesiva de dependencias OT a una sesión de mantenimiento.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(19,1,'Ya tienes una sesión de mantenimiento. Ahora trabaja dentro del HMI: abre el inventario de activos y revisa qué contexto adicional aparece al seleccionar RTU-GW-07.'),
(19,2,'No te limites a leer el campo Related systems. Selecciona uno de los sistemas relacionados y continúa siguiendo relaciones de control, proceso y distribución. La cadena correcta parte del gateway metropolitano y atraviesa la celda de Saint Louis.'),
(19,3,'Conserva abierta la pestaña Red de DevTools mientras recorres dependencias. Cuando alcances el extremo terminal de la cadena, revisa los encabezados de la última respuesta del servicio de contexto de activos.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
