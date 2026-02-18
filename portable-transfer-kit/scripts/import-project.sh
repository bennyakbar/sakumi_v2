#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 2 ]]; then
  echo "Usage: bash portable-transfer-kit/scripts/import-project.sh <archive-path> <destination-path>"
  exit 1
fi

ARCHIVE_PATH="$1"
DEST_PATH="$2"
TMP_DIR="$(mktemp -d)"

cleanup() {
  rm -rf "${TMP_DIR}"
}
trap cleanup EXIT

if [[ ! -f "${ARCHIVE_PATH}" ]]; then
  echo "Archive not found: ${ARCHIVE_PATH}"
  exit 1
fi

echo "[1/4] Extract archive..."
case "${ARCHIVE_PATH}" in
  *.tar.gz|*.tgz)
    tar -xzf "${ARCHIVE_PATH}" -C "${TMP_DIR}"
    ;;
  *.zip)
    if ! command -v unzip >/dev/null 2>&1; then
      echo "Command 'unzip' is required for zip archives."
      exit 1
    fi
    unzip -q "${ARCHIVE_PATH}" -d "${TMP_DIR}"
    ;;
  *)
    echo "Unsupported archive format: ${ARCHIVE_PATH}"
    exit 1
    ;;
esac

BUNDLE_DIR="$(find "${TMP_DIR}" -maxdepth 1 -type d -name 'sakumi-transfer-*' | head -n 1)"
if [[ -z "${BUNDLE_DIR}" ]]; then
  echo "Invalid bundle format."
  exit 1
fi

echo "[2/4] Restore app files..."
mkdir -p "${DEST_PATH}"
if command -v rsync >/dev/null 2>&1; then
  rsync -a "${BUNDLE_DIR}/app/" "${DEST_PATH}/"
else
  cp -a "${BUNDLE_DIR}/app/." "${DEST_PATH}/"
fi

echo "[3/4] Restore storage public files..."
mkdir -p "${DEST_PATH}/storage/app/public"
if [[ -d "${BUNDLE_DIR}/storage-public" ]]; then
  cp -a "${BUNDLE_DIR}/storage-public/." "${DEST_PATH}/storage/app/public/" || true
fi

if [[ -f "${BUNDLE_DIR}/.env.transfer" && ! -f "${DEST_PATH}/.env" ]]; then
  cp "${BUNDLE_DIR}/.env.transfer" "${DEST_PATH}/.env"
fi

if [[ -f "${BUNDLE_DIR}/database.sqlite" ]]; then
  mkdir -p "${DEST_PATH}/database"
  cp "${BUNDLE_DIR}/database.sqlite" "${DEST_PATH}/database/sakumi_dummy.sqlite"
fi

if [[ -f "${BUNDLE_DIR}/database.sql" ]]; then
  cp "${BUNDLE_DIR}/database.sql" "${DEST_PATH}/database/import-database.sql"
fi

if [[ -f "${BUNDLE_DIR}/metadata.txt" ]]; then
  cp "${BUNDLE_DIR}/metadata.txt" "${DEST_PATH}/database/transfer-metadata.txt"
fi

echo "[4/4] Done."
echo "Next steps:"
echo "1) cd ${DEST_PATH}"
echo "2) composer install"
echo "3) npm install && npm run build"
echo "4) php artisan key:generate --force (if APP_KEY empty)"
echo "5) php artisan migrate --force"
echo "6) php artisan storage:link"
echo "7) If exists: import SQL from database/import-database.sql to your DB server"
