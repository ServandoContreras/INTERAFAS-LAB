#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"
docker compose exec -T db mariadb -u root -p"${MARIADB_ROOT_PASSWORD:-interafas_root}" interafas < database/auditor-support-news-gating-migration.sql
echo "v0.5.1 aplicada: checklist/pistas en auditor y gating progresivo de noticias."
