#!/usr/bin/env bash
set -euo pipefail
if ! docker compose ps --status running db | grep -q db; then
  echo "La base de datos no está en ejecución. Primero ejecuta: docker compose up -d db"
  exit 1
fi
echo "Aplicando migración del Portal del Auditor..."
docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/auditor-migration.sql
echo "Migración completada. Puedes levantar todos los servicios con: docker compose up -d --build"
