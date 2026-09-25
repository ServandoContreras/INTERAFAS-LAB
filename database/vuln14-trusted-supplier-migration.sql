-- VULN 14 · TRUSTED-SUPPLIER
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(14,1,'En Contrataciones abre Validar vínculo para cualquier contrato. Observa que la verificación recibe por separado contract_id y provider_id, ambos visibles en la propia solicitud.'),
(14,2,'Conserva contract_id y cambia sólo provider_id por otro proveedor que figure como Vigente en el padrón público. Si el sistema sigue mostrando Vínculo vigente, revisa los encabezados y piensa qué comprobación entre ambas entidades faltó.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
