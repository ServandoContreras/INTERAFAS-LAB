#!/usr/bin/env bash
set -euo pipefail
docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/checklist-state-migration.sql
echo "v0.5.2 migration applied."
