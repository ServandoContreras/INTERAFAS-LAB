#!/usr/bin/env bash
set -euo pipefail
if ! docker compose ps --status running db | grep -q db; then
  echo "La base de datos no está en ejecución. Primero ejecuta: docker compose up -d db"
  exit 1
fi
echo "Aplicando migración v0.4.2 (identidad, intentos y bitácora)..."
docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/student-log-migration.sql
echo "Migración completada. Ahora ejecuta: docker compose up -d --build"
