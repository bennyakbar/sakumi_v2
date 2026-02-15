#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$ROOT_DIR/.env"
BACKUP_DIR="$ROOT_DIR/storage/env-backups"

usage() {
  cat <<'EOF'
Usage:
  ./scripts/switch-env.sh dummy
  ./scripts/switch-env.sh real

Required files:
  .env.dummy  (APP_ENV=testing|local, DB_SAKUMI_MODE=dummy)
  .env.real   (APP_ENV=production,    DB_SAKUMI_MODE=real)
EOF
}

if [[ $# -ne 1 ]]; then
  usage
  exit 1
fi

TARGET="$1"
case "$TARGET" in
  dummy)
    SOURCE_ENV="$ROOT_DIR/.env.dummy"
    ;;
  real)
    SOURCE_ENV="$ROOT_DIR/.env.real"
    ;;
  *)
    echo "Invalid target: $TARGET"
    usage
    exit 1
    ;;
esac

if [[ ! -f "$SOURCE_ENV" ]]; then
  echo "Missing file: $SOURCE_ENV"
  exit 1
fi

mkdir -p "$BACKUP_DIR"
if [[ -f "$ENV_FILE" ]]; then
  TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
  cp "$ENV_FILE" "$BACKUP_DIR/.env.$TIMESTAMP.bak"
  echo "Backup created: $BACKUP_DIR/.env.$TIMESTAMP.bak"
fi

if [[ "$TARGET" == "real" ]]; then
  read -r -p "Type REAL to confirm switching to production config: " CONFIRM
  if [[ "$CONFIRM" != "REAL" ]]; then
    echo "Aborted."
    exit 1
  fi
fi

cp "$SOURCE_ENV" "$ENV_FILE"

# ── Validate APP_ENV ──
NEW_ENV="$(grep -E '^APP_ENV=' "$ENV_FILE" | head -n1 | cut -d'=' -f2 | tr -d '"' || true)"

if [[ "$TARGET" == "dummy" ]]; then
  if [[ "$NEW_ENV" != "testing" && "$NEW_ENV" != "local" ]]; then
    echo "Safety check failed: dummy config must use APP_ENV=testing or APP_ENV=local"
    exit 1
  fi
fi

if [[ "$TARGET" == "real" && "$NEW_ENV" != "production" ]]; then
  echo "Safety check failed: real config must use APP_ENV=production"
  exit 1
fi

# ── Validate DB_SAKUMI_MODE ──
NEW_MODE="$(grep -E '^DB_SAKUMI_MODE=' "$ENV_FILE" | head -n1 | cut -d'=' -f2 | tr -d '"' || true)"

if [[ "$NEW_MODE" != "$TARGET" ]]; then
  echo "Safety check failed: DB_SAKUMI_MODE=$NEW_MODE does not match target=$TARGET"
  exit 1
fi

# ── Clear Laravel caches ──
(
  cd "$ROOT_DIR"
  php artisan config:clear >/dev/null 2>&1 || true
  php artisan cache:clear >/dev/null 2>&1 || true
)

echo "Switched to $TARGET environment (APP_ENV=$NEW_ENV, DB_SAKUMI_MODE=$NEW_MODE)."

# ── Auto-migrate and seed for dummy mode ──
if [[ "$TARGET" == "dummy" ]]; then
  echo ""
  echo "Initializing dummy database..."

  DUMMY_DB="$ROOT_DIR/database/sakumi_dummy.sqlite"
  if [[ ! -f "$DUMMY_DB" ]]; then
    touch "$DUMMY_DB"
    echo "Created: $DUMMY_DB"
  fi

  (
    cd "$ROOT_DIR"

    echo "Running migrations..."
    php artisan migrate --force --no-interaction
    if [[ $? -ne 0 ]]; then
      echo "Migration failed!"
      exit 1
    fi

    echo "Running dummy seeders..."
    php artisan db:seed --class='Database\Seeders\Testing\DummyDatabaseSeeder' --force --no-interaction
    if [[ $? -ne 0 ]]; then
      echo "Seeding failed!"
      exit 1
    fi
  )

  echo ""
  echo "Dummy database ready."
fi
