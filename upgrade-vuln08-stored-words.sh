#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln08-stored-words-migration.sql

echo "VULN 08 migration applied."
