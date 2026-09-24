-- VULN 11 · HIDDEN-HISTORY
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(11,1,'Mi portal actualiza parte de su estado mediante solicitudes en segundo plano. Revisa Network y no te limites al HTML visible.'),
(11,2,'Cuando encuentres una respuesta JSON relacionada con tu cuenta, inspecciona todos sus campos. Algunas APIs devuelven relaciones o enlaces que la interfaz decide no mostrar.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
