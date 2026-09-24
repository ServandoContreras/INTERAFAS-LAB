-- VULN 14 · TRUSTED-SUPPLIER
-- Actualiza las pistas progresivas para instalaciones existentes.
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(14,1,'En Gestión de proveedores entra a Revisar contratos para un proveedor que tenga contratos asociados. La pantalla sólo pretende confirmar vínculos existentes; observa qué identificadores conserva cada formulario al enviar Confirmar relación.'),
(14,2,'Intercepta o edita una confirmación: conserva csrf y contract_id, pero sustituye provider_id por el ID de otro proveedor válido que puedas observar en el padrón. Si el servidor acepta la nueva combinación, revisa los encabezados de la respuesta y comprueba después a qué proveedor quedó asociado el contrato.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
