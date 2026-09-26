#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/vuln19-chain-reaction-migration.sql

echo "VULN 19 migration applied."
