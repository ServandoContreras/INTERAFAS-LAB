-- VULN 10 · WHO-ARE-YOU
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(10,1,'Compara el valor de la cookie de sesión antes y después de iniciar sesión. Un cambio de estado de anónimo a autenticado debería ir acompañado de una rotación del identificador.'),
(10,2,'Si el identificador se conserva, reutiliza el valor observado antes del login desde un segundo cliente HTTP y revisa tanto el acceso obtenido como los encabezados de la respuesta.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
