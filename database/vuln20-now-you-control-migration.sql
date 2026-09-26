-- VULN 20 · NOW-YOU-CONTROL
-- Cierre del laboratorio: parámetro de firmware manipulado -> cascada operacional simulada.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(20,1,'VULN-20 sólo se habilita después de completar correctamente la cadena 6/6 del reto anterior. Cuando la secuencia quede acreditada, observa qué cambia en el menú de Ingeniería del HMI.'),
(20,2,'Entra a Firmware y descarga BACKUP. Ese archivo muestra la estructura exacta de la configuración aceptada por RTU-GW-07. Conserva el original y trabaja sobre una copia.'),
(20,3,'Busca en el backup un parámetro que represente directamente una condición física del proceso. pressure_setpoint_bar tiene un valor normal de ingeniería de 4.2 bar. No necesitas cambiar device, version, mode ni diagnostic.'),
(20,4,'Modifica únicamente pressure_setpoint_bar por un valor distinto del baseline y carga el JSON resultante. El problema que se demuestra es que el firmware acepta un setpoint crítico sin una validación adecuada de límites de ingeniería.'),
(20,5,'Regresa al HMI inmediatamente. La degradación ocurre de forma progresiva cada pocos segundos: crecen presión, caudal, alarmas y criticidad mientras cae la disponibilidad hasta que toda la interfaz entra en estado rojo.'),
(20,6,'Cuando aparezca CATASTROPHIC STATE no necesitas realizar ninguna otra acción. La bandera final se mostrará automáticamente, en negritas, junto al nombre INTERAFAS en la cabecera del sistema.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
