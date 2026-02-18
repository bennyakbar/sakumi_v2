#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
OUT_ROOT="${1:-${PROJECT_ROOT}/_portable_exports}"
TS="$(date +%Y%m%d-%H%M%S)"
WORK_DIR="${OUT_ROOT}/sakumi-transfer-${TS}"
APP_DIR="${WORK_DIR}/app"

mkdir -p "${APP_DIR}"

read_env() {
  local key="$1"
  local env_file="${PROJECT_ROOT}/.env"
  if [[ ! -f "${env_file}" ]]; then
    echo ""
    return
  fi
  (grep -E "^${key}=" "${env_file}" || true) | tail -n 1 | cut -d '=' -f 2- | sed "s/^['\"]//; s/['\"]$//"
}

echo "[1/5] Copy source code..."
tar -C "${PROJECT_ROOT}" \
  --exclude=".git" \
  --exclude="node_modules" \
  --exclude="vendor" \
  --exclude="_portable_exports" \
  --exclude="storage/logs/*" \
  --exclude="bootstrap/cache/*.php" \
  -cf - . | tar -C "${APP_DIR}" -xf -

if [[ -f "${PROJECT_ROOT}/.env" ]]; then
  cp "${PROJECT_ROOT}/.env" "${WORK_DIR}/.env.transfer"
fi

echo "[2/5] Copy uploaded files (storage/app/public)..."
mkdir -p "${WORK_DIR}/storage-public"
if [[ -d "${PROJECT_ROOT}/storage/app/public" ]]; then
  cp -a "${PROJECT_ROOT}/storage/app/public/." "${WORK_DIR}/storage-public/" || true
fi

echo "[3/5] Export database (best effort)..."
DB_MODE="$(read_env DB_SAKUMI_MODE)"
DB_CONN="$(read_env DB_CONNECTION)"
DB_HOST="$(read_env DB_HOST)"
DB_PORT="$(read_env DB_PORT)"
DB_USER="$(read_env DB_USERNAME)"
DB_PASS="$(read_env DB_PASSWORD)"
DB_NAME="$(read_env DB_DATABASE)"
DB_REAL_NAME="$(read_env DB_REAL_DATABASE)"
DB_REAL_USER="$(read_env DB_REAL_USERNAME)"
DB_REAL_PASS="$(read_env DB_REAL_PASSWORD)"

{
  echo "timestamp=${TS}"
  echo "db_mode=${DB_MODE:-unknown}"
  echo "db_connection=${DB_CONN:-unknown}"
} > "${WORK_DIR}/metadata.txt"

if [[ "${DB_MODE}" == "dummy" ]] || [[ "${DB_CONN}" == "sqlite" ]]; then
  SQLITE_FILE="$(read_env DB_DATABASE)"
  if [[ -z "${SQLITE_FILE}" ]]; then
    SQLITE_FILE="${PROJECT_ROOT}/database/sakumi_dummy.sqlite"
  fi
  if [[ ! -f "${SQLITE_FILE}" ]]; then
    SQLITE_FILE="${PROJECT_ROOT}/database/sakumi_dummy.sqlite"
  fi
  if [[ -f "${SQLITE_FILE}" ]]; then
    cp "${SQLITE_FILE}" "${WORK_DIR}/database.sqlite"
  else
    echo "warning=sqlite_file_not_found" >> "${WORK_DIR}/metadata.txt"
  fi
elif [[ "${DB_MODE}" == "real" ]] || [[ "${DB_CONN}" == "pgsql" ]]; then
  if command -v pg_dump >/dev/null 2>&1; then
    PG_DB="${DB_REAL_NAME:-$DB_NAME}"
    PG_USER="${DB_REAL_USER:-$DB_USER}"
    PG_PASS="${DB_REAL_PASS:-$DB_PASS}"
    PGPASSWORD="${PG_PASS}" pg_dump \
      -h "${DB_HOST:-127.0.0.1}" \
      -p "${DB_PORT:-5432}" \
      -U "${PG_USER}" \
      -d "${PG_DB}" \
      --no-owner --no-privileges \
      -f "${WORK_DIR}/database.sql" || echo "warning=pg_dump_failed" >> "${WORK_DIR}/metadata.txt"
  else
    echo "warning=pg_dump_not_found" >> "${WORK_DIR}/metadata.txt"
  fi
elif [[ "${DB_CONN}" == "mysql" || "${DB_CONN}" == "mariadb" ]]; then
  if command -v mysqldump >/dev/null 2>&1; then
    MYSQL_PWD="${DB_PASS}" mysqldump \
      -h "${DB_HOST:-127.0.0.1}" \
      -P "${DB_PORT:-3306}" \
      -u "${DB_USER}" \
      "${DB_NAME}" > "${WORK_DIR}/database.sql" || echo "warning=mysqldump_failed" >> "${WORK_DIR}/metadata.txt"
  else
    echo "warning=mysqldump_not_found" >> "${WORK_DIR}/metadata.txt"
  fi
else
  echo "warning=unknown_db_profile" >> "${WORK_DIR}/metadata.txt"
fi

echo "[4/5] Build archive..."
mkdir -p "${OUT_ROOT}"
TAR_PATH="${OUT_ROOT}/sakumi-transfer-${TS}.tar.gz"
tar -C "${OUT_ROOT}" -czf "${TAR_PATH}" "sakumi-transfer-${TS}"

ZIP_PATH="${OUT_ROOT}/sakumi-transfer-${TS}.zip"
if command -v zip >/dev/null 2>&1; then
  (
    cd "${OUT_ROOT}"
    zip -rq "${ZIP_PATH}" "sakumi-transfer-${TS}"
  )
  echo "[5/5] Done: ${TAR_PATH} and ${ZIP_PATH}"
else
  echo "[5/5] Done: ${TAR_PATH} (zip not generated: command 'zip' not found)"
fi
