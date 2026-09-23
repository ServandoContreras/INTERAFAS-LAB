# INTERAFAS LAB v0.5.1 · Guided Auditor + Progressive News

Cambios principales:
- Checklist metodológico por cada uno de los 20 retos en Banderas.
- Archivo especial de pistas solicitadas agrupado por reto.
- El selector del reto conserva su valor al solicitar la siguiente pista.
- Pulso Metropolitano no muestra contenido relacionado con INTERAFAS antes de la primera bandera.
- Las notas de cada flag aparecen únicamente al reclamarse su evento.
- La pestaña y módulo Especial INTERAFAS se desbloquean con FLAG_10 y agrupan retrospectivamente la cobertura previa.

Actualización desde v0.5.0:
```bash
docker compose up -d db
./upgrade-auditor-support.sh
docker compose up -d --build
```
