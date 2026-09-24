-- VULN 13 · PAYMENT-PATH
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(13,1,'Abre un recibo pendiente y entra al flujo Pagar este recibo. Observa qué regla comunica la interfaz sobre el importe y revisa en Red qué campos se envían cuando confirmas la operación.'),
(13,2,'El navegador puede impedir editar un campo sin que eso signifique que el servidor lo valide. Conserva factura_id, cuenta y sesión, pero modifica sólo monto por un valor positivo menor al saldo. Después compara el pago registrado con el estado final del recibo y revisa los encabezados de la respuesta.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
