#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln03-not-yours-migration.sql

echo "VULN 03 migration applied."
