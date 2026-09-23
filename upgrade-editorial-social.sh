#!/usr/bin/env bash
set -euo pipefail
DB_CID=$(docker compose ps -q db)
if [ -z "$DB_CID" ]; then echo "La base db no está activa." >&2; exit 1; fi
docker exec -i "$DB_CID" mariadb -uroot -p"${MARIADB_ROOT_PASSWORD:-root_lab_only}" interafas < database/editorial-social-migration.sql
echo "Actualización editorial/social aplicada."
