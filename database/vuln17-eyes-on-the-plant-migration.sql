-- VULN 17 · EYES-ON-THE-PLANT
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(17,1,'El gateway del reto anterior devuelve una ruta de monitoreo. Ábrela primero en el mismo navegador donde iniciaste tu intento del laboratorio y después compárala con una ventana privada o un cliente que no comparta cookies.'),
(17,2,'Revisa las cookies de localhost en el navegador. INTERAFAS_LAB_TOKEN identifica tu intento académico y usa path=/; las cookies no se aíslan por número de puerto. Si esa misma cookie basta para entrar al HMI, documenta qué autorización operacional faltó y revisa los encabezados de la respuesta.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
