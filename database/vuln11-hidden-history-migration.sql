-- VULN 11 · HIDDEN-HISTORY
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(11,1,'Mi portal actualiza parte de su estado mediante solicitudes en segundo plano. Revisa Network y no te limites al HTML visible.'),
(11,2,'En Firefox abre Red, filtra las solicitudes XHR/Fetch generadas por Mi portal y selecciona la que devuelve contexto de tu cuenta. Revisa la pestaña Respuesta completa: además de los datos visibles en pantalla, busca propiedades que representen enlaces o relaciones hacia otros recursos de la API.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
