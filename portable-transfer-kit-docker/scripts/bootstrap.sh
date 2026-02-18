#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KIT_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

echo "[1/6] Build and start containers..."
docker compose -f "${KIT_DIR}/docker-compose.yml" up -d --build

echo "[2/6] Install PHP dependencies..."
docker compose -f "${KIT_DIR}/docker-compose.yml" exec app composer install

echo "[3/6] Install JS dependencies..."
docker compose -f "${KIT_DIR}/docker-compose.yml" exec node sh -lc "npm install"

echo "[4/6] Build frontend assets..."
docker compose -f "${KIT_DIR}/docker-compose.yml" exec node sh -lc "npm run build"

echo "[5/6] Prepare app key, migration, storage link..."
docker compose -f "${KIT_DIR}/docker-compose.yml" exec app php artisan key:generate --force
docker compose -f "${KIT_DIR}/docker-compose.yml" exec app php artisan migrate --force
docker compose -f "${KIT_DIR}/docker-compose.yml" exec app php artisan storage:link || true

echo "[6/6] Done. App URL: http://localhost:8080"
