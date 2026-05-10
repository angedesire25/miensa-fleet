#!/usr/bin/env bash
# ════════════════════════════════════════════════════════════════════════════
# MiensaFleet — Script de déploiement production (OVH VPS / hébergement SSH)
# Usage : bash deploy.sh
# Pré-requis : PHP 8.2+, Composer, Node.js 20+, accès SSH, .env configuré
# ════════════════════════════════════════════════════════════════════════════

set -e

BLUE='\033[0;34m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
step() { echo -e "\n${BLUE}▶ $1${NC}"; }
ok()   { echo -e "${GREEN}✓ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠ $1${NC}"; }

step "1/10 — Vérification de l'environnement"
php -v | head -1
composer --version
node --version
ok "Environnement OK"

step "2/10 — Passage en mode maintenance"
php artisan down --secret="geomatos-deploy-$(date +%s)" || true

step "3/10 — Mise à jour du code"
git pull origin main
ok "Code à jour"

step "4/10 — Installation des dépendances PHP (production)"
composer install --no-dev --optimize-autoloader --no-interaction
ok "Dépendances PHP installées"

step "5/10 — Compilation des assets frontend"
npm ci --production=false
npm run build
ok "Assets compilés"

step "6/10 — Configuration Laravel"
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Caches régénérés"

step "7/10 — Migrations"
echo "  -> Migration base landlord (miensafleet_landlord)..."
php artisan migrate --database=landlord --path=database/migrations/landlord --force

echo "  -> Migration base tenant (miensafleet_geomatos)..."
php artisan tenants:artisan "migrate --force" --tenant=geomatos 2>/dev/null \
    || php artisan migrate --database=tenant --force
ok "Migrations appliquées"

step "8/10 — Lien symbolique storage"
php artisan storage:link --force 2>/dev/null || warn "storage:link deja present"

step "9/10 — Permissions fichiers"
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;
chmod 664 bootstrap/cache/*.php 2>/dev/null || true
ok "Permissions OK"

step "10/10 — Sortie du mode maintenance"
php artisan up

echo -e "\n${GREEN}================================================${NC}"
echo -e "${GREEN}  Deploiement termine !${NC}"
echo -e "${GREEN}  Panel fleet : https://app.geomatos.com${NC}"
echo -e "${GREEN}  Panel admin : https://admin.app.geomatos.com${NC}"
echo -e "${GREEN}================================================${NC}\n"
