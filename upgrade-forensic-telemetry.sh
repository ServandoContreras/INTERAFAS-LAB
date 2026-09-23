#!/usr/bin/env bash
set -euo pipefail
COMPOSE=${COMPOSE:-docker compose}
$COMPOSE exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/forensic-telemetry-migration.sql
printf '\nMigración v0.4.3 aplicada.\n'
