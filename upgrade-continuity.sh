#!/usr/bin/env bash
set -euo pipefail
COMPOSE=${COMPOSE:-docker compose}
echo "[v0.4.6] Aplicando migración de continuidad narrativa..."
$COMPOSE exec -T db mariadb -uroot -p"${MARIADB_ROOT_PASSWORD:-root_lab_only}" interafas < database/continuity-migration.sql
echo "[v0.4.6] Migración aplicada."
