#!/bin/sh
set -eu

docker compose up -d --build auditor ot-sim

echo "Auditor ZIP export and OT reset support updated."
