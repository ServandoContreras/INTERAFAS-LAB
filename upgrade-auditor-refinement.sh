#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT_DIR"
echo "Aplicando v0.5.0 · Auditor refinement..."
docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/auditor-refinement-migration.sql
echo "Migración aplicada."
