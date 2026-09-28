#!/bin/sh
set -eu

docker compose exec -T db mariadb -uinterafas -pinterafas_lab interafas < database/news-naturalization-migration.sql

echo "News naturalization migration applied."
