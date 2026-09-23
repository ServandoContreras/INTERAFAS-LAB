#!/usr/bin/env bash
set -euo pipefail
DB_CONTAINER=$(docker compose ps -q db)
if [ -z "$DB_CONTAINER" ]; then echo 'El servicio db no está iniciado.' >&2; exit 1; fi
docker exec -i "$DB_CONTAINER" mariadb -uroot -proot_lab_only interafas < database/all-flags-media-migration.sql
echo 'Migración v0.4.8 aplicada.'
