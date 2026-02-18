#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: bash portable-transfer-kit-docker/scripts/import-db.sh <sql-file>"
  exit 1
fi

SQL_FILE="$1"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KIT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

if [[ ! -f "${SQL_FILE}" ]]; then
  echo "SQL file not found: ${SQL_FILE}"
  exit 1
fi

echo "Importing ${SQL_FILE} into PostgreSQL container..."
docker compose -f "${KIT_DIR}/docker-compose.yml" exec -T db psql -U sakumi -d sakumi < "${SQL_FILE}"
echo "Import finished."
