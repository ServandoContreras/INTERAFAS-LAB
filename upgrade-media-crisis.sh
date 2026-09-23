#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < "$ROOT/database/media-crisis-migration.sql"
echo "Actualización Media Crisis aplicada."
