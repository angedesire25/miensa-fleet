#!/usr/bin/env bash
# =============================================================================
# MiensaFleet — Script de déploiement
# Usage : bash deploy.sh
# =============================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
UPLOADS_DIR="${STORAGE_PUBLIC_PATH:-}"

echo "▶ MiensaFleet — Déploiement"
echo "  Dossier app     : $APP_DIR"

# ── 1. Vérifier que STORAGE_PUBLIC_PATH est défini en production ──────────────
if [[ -z "$UPLOADS_DIR" ]]; then
    # Tenter de le lire depuis .env
    if [[ -f "$APP_DIR/.env" ]]; then
        UPLOADS_DIR=$(grep -E '^STORAGE_PUBLIC_PATH=' "$APP_DIR/.env" | cut -d'=' -f2- | tr -d '"' | tr -d "'")
    fi
fi

if [[ -n "$UPLOADS_DIR" ]]; then
    echo "  Uploads persistants : $UPLOADS_DIR"

    # Créer le dossier s'il n'existe pas encore
    mkdir -p "$UPLOADS_DIR"
    echo "  ✔ Dossier uploads prêt"
else
    echo "  ℹ  Pas de STORAGE_PUBLIC_PATH défini — utilisation de storage/app/public/ (mode local)"
fi

# ── 2. Pull du code source ────────────────────────────────────────────────────
echo "▶ Récupération du code (git pull)..."
git -C "$APP_DIR" pull --ff-only

# ── 3. Dépendances Composer ───────────────────────────────────────────────────
echo "▶ Composer install..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet

# ── 4. Compilation assets ─────────────────────────────────────────────────────
echo "▶ Build Vite..."
npm ci --silent
npm run build --silent

# ── 5. Migrations ─────────────────────────────────────────────────────────────
echo "▶ Migrations..."
php "$APP_DIR/artisan" migrate --force

# ── 6. Symlink storage ────────────────────────────────────────────────────────
echo "▶ Storage link..."
# Supprimer l'ancien symlink si présent
rm -f "$APP_DIR/public/storage"
php "$APP_DIR/artisan" storage:link

# ── 7. Caches ─────────────────────────────────────────────────────────────────
echo "▶ Optimisation des caches..."
php "$APP_DIR/artisan" config:cache
php "$APP_DIR/artisan" route:cache
php "$APP_DIR/artisan" view:cache

# ── 8. Permissions ────────────────────────────────────────────────────────────
echo "▶ Permissions..."
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
if [[ -n "$UPLOADS_DIR" ]]; then
    chmod -R 775 "$UPLOADS_DIR"
fi

echo ""
echo "✅ Déploiement terminé."
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Uploads stockés dans : ${UPLOADS_DIR:-$APP_DIR/storage/app/public}"
echo "  Symlink              : $APP_DIR/public/storage"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
