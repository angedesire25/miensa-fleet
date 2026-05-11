# MiensaFleet — Checklist de déploiement

## Création d'un nouveau client

> La base de données doit être créée manuellement sur votre hébergeur **avant** de créer la société dans l'interface.
> MiensaFleet ne crée pas de base de données — il se connecte à une base existante.

### Étapes

- [ ] **1. Créer la base chez votre hébergeur**
  - Connectez-vous sur le panel de votre hébergeur
  - Créez une base de données vide
  - Notez le **nom exact**, l'**hôte**, l'**identifiant** et le **mot de passe** fournis

- [ ] **2. Créer la société dans le panel admin**
  - Connectez-vous sur votre panel landlord
  - Menu **Tenants** → **Nouvelle société**
  - Remplir : Nom, Slug, Connexion base de données (hôte, identifiant, mot de passe), Contact, Plan
  - Cliquer **Créer et initialiser le panel**
  - Le système teste la connexion, applique les migrations et crée le compte admin

- [ ] **3. Vérifier le résultat**
  - La page de succès affiche l'email et le mot de passe temporaire
  - Tester la connexion sur `http://{slug}.votre-domaine.com`
  - Transmettre les identifiants au client de façon sécurisée

---

## Alternative via SSH (si l'interface web échoue)

```bash
# Le tenant doit déjà exister dans la base landlord
php artisan tenant:init {slug}

# Avec des options explicites
php artisan tenant:init geomatos \
  --email=admin@geomatos.com \
  --name="Administrateur Geomatos" \
  --force
```

La commande :
1. Charge les credentials du tenant depuis la base landlord
2. Vérifie que la base est accessible avec ces credentials
3. Applique toutes les migrations manquantes
4. Lance `RoleAndPermissionSeeder` (idempotent)
5. Crée ou réinitialise le compte super-admin
6. Affiche un tableau récapitulatif avec les identifiants

---

## Réinitialiser l'accès admin d'un tenant existant

Depuis le panel landlord → Tenants → [Société] → **Réinitialiser accès**

Ou via SSH :

```bash
php artisan tenant:init {slug} --force
```

Le bouton **Réinitialiser accès** gère aussi automatiquement :
- Les migrations manquantes (nouveaux modules ajoutés depuis la création)
- Le re-seed des rôles/permissions
- La création d'un admin si la base était vide

---

## Variables d'environnement requises en production

```env
APP_ENV=production
APP_URL=https://votre-domaine.com
LANDLORD_DOMAIN=votre-domaine.com

# Base centrale (landlord)
LANDLORD_DB_HOST=votre-hote-mysql
LANDLORD_DB_DATABASE=miensafleet_landlord
LANDLORD_DB_USERNAME=votre_utilisateur
LANDLORD_DB_PASSWORD=votre_mot_de_passe

# Connexion tenant par défaut (utilisée si le tenant n'a pas de credentials propres)
TENANT_DB_HOST=votre-hote-mysql
TENANT_DB_USERNAME=votre_utilisateur
TENANT_DB_PASSWORD=votre_mot_de_passe

# VAPID pour les Push Notifications
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:admin@votre-domaine.com
```

> **Note :** Chaque tenant peut avoir ses propres credentials de base de données, stockés chiffrés en base landlord. Les variables `TENANT_DB_*` servent de fallback pour les tenants sans credentials explicites.

---

## Checklist de mise en production initiale

- [ ] Variables `.env` complètes (voir ci-dessus)
- [ ] `php artisan key:generate --force`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Migrations landlord : `php artisan migrate --database=landlord --path=database/migrations/landlord --force`
- [ ] Seeder landlord (plans) : `php artisan db:seed --class=PlanSeeder --force`
- [ ] Compte super-admin landlord créé
- [ ] DNS : `*.votre-domaine.com` → IP serveur (wildcard)
- [ ] SSL : certificat wildcard actif
- [ ] Cron Laravel actif : `* * * * * php /path/to/artisan schedule:run`
- [ ] Permissions storage : `chmod -R 775 storage bootstrap/cache`
- [ ] `php artisan storage:link`
- [ ] `php artisan pwa:icons` (génère les icônes PWA)
