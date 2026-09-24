#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln04-old-memories-migration.sql

echo "VULN 04 migration applied."
