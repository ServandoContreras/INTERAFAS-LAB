#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln06-wrong-role-migration.sql

echo "VULN 06 migration applied."
