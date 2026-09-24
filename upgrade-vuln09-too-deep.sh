#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln09-too-deep-migration.sql

echo "VULN 09 migration applied."
