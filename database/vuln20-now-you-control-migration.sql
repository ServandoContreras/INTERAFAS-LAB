-- VULN 20 · NOW-YOU-CONTROL
-- Cierre del laboratorio: firmware manipulado -> cascada operacional simulada.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(20,1,'VULN-20 sólo se habilita después de completar correctamente la cadena 6/6 del reto anterior. Cuando la secuencia quede acreditada, observa qué cambia en el menú de Ingeniería del HMI.'),
(20,2,'Entra a Firmware. Si llegaste por la ruta correcta aparecerá una opción BACKUP. Descarga ese archivo antes de modificar nada: representa la configuración que el gateway acepta y te muestra la estructura exacta del paquete.'),
(20,3,'Compara el backup con el contexto técnico revelado al final de VULN-19. Conserva device, version y diagnostic. El dato relevante está en mode: existe un perfil legado identificado como CASCADE que nunca debería desplegarse en el gateway operacional.'),
(20,4,'Modifica únicamente el backup descargado para utilizar el perfil CASCADE y carga el JSON resultante desde la misma sección Firmware. Después del reinicio regresa inmediatamente al HMI.'),
(20,5,'No esperes un fallo instantáneo. Mantén abierto Overview, Process View o Alarms y observa la evolución de la telemetría. El incidente escala por etapas: aumentan presión, caudal, estados críticos y alarmas hasta llegar a miles.'),
(20,6,'Cuando el HMI alcance CATASTROPHIC STATE y la interfaz quede completamente en condición crítica, inspecciona en Network la última respuesta de telemetry.php. La evidencia final se entrega en sus Response Headers.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
