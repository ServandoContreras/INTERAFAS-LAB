#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln20-now-you-control-migration.sql

echo "VULN 20 migration applied."
