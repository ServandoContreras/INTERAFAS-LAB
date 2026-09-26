-- VULN 19 · CHAIN-REACTION
-- El reto explota exposición excesiva de dependencias OT a una sesión de mantenimiento.

INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(19,1,'La sesión obtenida en el reto anterior no es la bandera. Ahora debes usarla para explorar el HMI. Busca una sección donde sea natural consultar inventario, relaciones o contexto de activos OT.'),
(19,2,'Dentro de Assets, selecciona el gateway metropolitano RTU-GW-07 y observa dos cosas al mismo tiempo: el panel de contexto y la solicitud XHR/Fetch que aparece en DevTools. El indicador ЦЕПОЧКА ЗАВИСИМОСТЕЙ te dirá si comenzaste correctamente.'),
(19,3,'La cadena correcta sigue una lógica operacional: comunicación/gateway → control → proceso → distribución → campo → zona de servicio. Evita saltos hacia sistemas de respaldo, HMI o servicios auxiliares. Si el indicador se vuelve rojo, regresa a RTU-GW-07 y reconstruye la secuencia.'),
(19,4,'En Saint Louis, desde el PLC debes elegir el equipo de proceso que representa el bombeo primario. Después sigue el elemento hidráulico que recibe su descarga, continúa por la válvula que alimenta la Zona A y termina en la zona de servicio asociada.'),
(19,5,'Cuando ЦЕПОЧКА ЗАВИСИМОСТЕЙ marque ЗАВЕРШЕНО · 6/6, no busques la bandera en el HTML. Abre en Network la última respuesta de asset-context.php y revisa sus Response Headers.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
