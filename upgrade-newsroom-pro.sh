#!/usr/bin/env bash
set -euo pipefail
DB_CONTAINER="$(docker compose ps -q db)"
if [ -z "$DB_CONTAINER" ]; then echo "La base de datos no está en ejecución. Ejecuta: docker compose up -d db"; exit 1; fi
docker exec -i "$DB_CONTAINER" mariadb -uinterafas -pinterafas_lab interafas < database/newsroom-pro-migration.sql
echo "Pulso Metropolitano actualizado a Newsroom Pro."
