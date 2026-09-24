#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln07-silent-answer-migration.sql

echo "VULN 07 migration applied."
