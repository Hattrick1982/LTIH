#!/usr/bin/env bash
set -euo pipefail

# Clean redeploy for production server.
# Usage:
#   APP_DIR=/var/www/ltih WEB_USER=www-data ./php-app/bin/redeploy-clean-main.sh

REPO_URL="${REPO_URL:-https://github.com/Hattrick1982/LTIH.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="${APP_DIR:-/var/www/ltih}"
WEB_USER="${WEB_USER:-www-data}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-}"
NGINX_SERVICE="${NGINX_SERVICE:-nginx}"
SKIP_RESTART="${SKIP_RESTART:-0}"

TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="${APP_DIR}_backup_${TIMESTAMP}"

log() {
  printf '[redeploy] %s\n' "$1"
}

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1" >&2
    exit 1
  fi
}

require_cmd git
require_cmd composer
require_cmd php

if [[ -e "$APP_DIR" ]]; then
  log "Backup maken: $APP_DIR -> $BACKUP_DIR"
  mv "$APP_DIR" "$BACKUP_DIR"
fi

log "Clone branch '$BRANCH' uit $REPO_URL"
git clone --branch "$BRANCH" --single-branch "$REPO_URL" "$APP_DIR"

PHP_APP_DIR="$APP_DIR/php-app"
if [[ ! -d "$PHP_APP_DIR" ]]; then
  echo "php-app directory niet gevonden in clone." >&2
  exit 1
fi

if [[ -f "$BACKUP_DIR/php-app/.env" && ! -f "$PHP_APP_DIR/.env" ]]; then
  log "Bestaande .env terugzetten vanuit backup"
  cp "$BACKUP_DIR/php-app/.env" "$PHP_APP_DIR/.env"
fi

log "Composer install"
cd "$PHP_APP_DIR"
composer install --no-dev --optimize-autoloader

log "Storage directories aanmaken"
mkdir -p storage/tmp/uploads storage/tmp/assessments
chmod -R 775 storage || true

if command -v chown >/dev/null 2>&1; then
  if [[ "$(id -u)" -eq 0 ]]; then
    chown -R "${WEB_USER}:${WEB_USER}" storage || true
  else
    log "Tip: run als root voor chown ${WEB_USER}:${WEB_USER} op storage"
  fi
fi

if [[ "$SKIP_RESTART" != "1" && -x "$(command -v systemctl || true)" ]]; then
  if [[ -n "$PHP_FPM_SERVICE" ]]; then
    log "Services herstarten: $PHP_FPM_SERVICE, $NGINX_SERVICE"
    systemctl restart "$PHP_FPM_SERVICE" "$NGINX_SERVICE"
  else
    log "PHP_FPM_SERVICE niet gezet, nginx wel herstarten"
    systemctl restart "$NGINX_SERVICE" || true
    log "Tip: zet PHP_FPM_SERVICE, bv. php8.2-fpm"
  fi
else
  log "Service restart overgeslagen (SKIP_RESTART=1 of geen systemctl)"
fi

DEPLOY_COMMIT="$(git -C "$APP_DIR" rev-parse --short HEAD)"
log "Klaar. Live code commit: $DEPLOY_COMMIT"
log "Controle: git -C $APP_DIR rev-parse --short HEAD"
log "Controle: curl -s http://127.0.0.1/assessment/new?room=stairs_hall | grep -E 'Maak foto|Kies uit galerij'"
