# INTERAFAS-LAB v0.5.3.2 — VULN01 Deployment Residue

VULN 01 fue rediseñada como un error natural de despliegue.

- No existe una página especial para el reto.
- La ruta normal `/assets/docs/public/` sirve PDFs usados por Transparencia.
- Apache deja habilitado accidentalmente el listado del directorio.
- El equipo web olvidó `release_20260407_rc3.zip` bajo el document root.
- El ZIP contiene archivos plausibles de una publicación real.
- La bandera está en `release/deploy/release-check.env` como `SMOKE_TEST_TOKEN`.
- `robots.txt` ya no revela la ubicación.

Flag: `UPSLP_CNOIV-OPEN-DOOR-01`


## v0.5.3.3 — VULN01 route via robots.txt
La primera vulnerabilidad se descubre desde `/robots.txt`, que excluye `/assets/docs/public/revision/`. La carpeta quedó accesible con listado de directorio y contiene residuos técnicos de una publicación, incluido `portal-release_20260407_rc3.zip`. La bandera se conserva como `SMOKE_TEST_TOKEN` dentro de `release/deploy/release-check.env`.
