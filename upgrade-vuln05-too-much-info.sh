#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln05-too-much-info-migration.sql

echo "VULN 05 migration applied."
