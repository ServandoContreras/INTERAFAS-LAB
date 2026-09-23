# INTERAFAS LAB v0.5.2 — Checklist persistente y feed social cronológico

Cambios principales:
- Checklist interactivo por reto con checkbox persistente por intento.
- Cada marca/desmarca se guarda en MariaDB y se registra en la bitácora.
- Conversación digital ordenada por momento real de desbloqueo, más reciente primero.
- Las publicaciones muestran hora cuando existe un momento de desbloqueo calculable.

Actualización desde v0.5.1:
```bash
docker compose up -d db
./upgrade-checklist-social.sh
docker compose up -d --build
```
