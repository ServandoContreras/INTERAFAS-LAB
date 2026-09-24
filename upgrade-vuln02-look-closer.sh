#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln02-look-closer-migration.sql

echo "VULN 02 migration applied."
