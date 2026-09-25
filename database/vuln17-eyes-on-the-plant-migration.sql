-- VULN 17 · EYES-ON-THE-PLANT
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(17,1,'Antes de cruzar correctamente el gateway, /operations/ debe responder como recurso no disponible. Repite VULN 16 desde el mismo navegador del laboratorio y observa que la respuesta exitosa establece un contexto temporal de puente y revela la ruta de monitoreo.'),
(17,2,'Inspecciona las cookies enviadas al HMI después del cruce. INTERAFAS_OPS_BRIDGE sólo demuestra que pasaste por el gateway; INTERAFAS_LAB_TOKEN identifica tu intento académico. Si no existe una identidad o rol OT adicional y aun así ves telemetría, documenta la autorización ausente y revisa los encabezados de la respuesta.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
