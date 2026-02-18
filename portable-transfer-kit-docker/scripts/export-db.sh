#!/usr/bin/env bash
set -euo pipefail

OUT_FILE="${1:-./sakumi-docker-db-$(date +%Y%m%d-%H%M%S).sql}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KIT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

echo "Exporting PostgreSQL database to ${OUT_FILE}..."
docker compose -f "${KIT_DIR}/docker-compose.yml" exec -T db pg_dump -U sakumi -d sakumi --no-owner --no-privileges > "${OUT_FILE}"
echo "Export finished."
