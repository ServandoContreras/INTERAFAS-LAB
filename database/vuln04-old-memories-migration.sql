-- VULN 04 · OLD-MEMORIES
INSERT INTO flag_hints(flag_number,hint_order,hint_text) VALUES
(4,1,'Una página actual puede tener versiones anteriores que nunca debieron quedar dentro del directorio público. Piensa en cómo editores y despliegues suelen nombrar esas copias.'),
(4,2,'Cuando cambia la extensión de un archivo dinámico, el servidor puede dejar de interpretarlo y entregar su contenido como un archivo ordinario.')
ON DUPLICATE KEY UPDATE hint_text=VALUES(hint_text);
