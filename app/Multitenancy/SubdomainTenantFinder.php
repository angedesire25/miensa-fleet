<?php

namespace App\Multitenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * Identifie le tenant à partir du sous-domaine.
 *
 * Production :  geomatos.miensafleet.ci   →  slug "geomatos"
 * Dev Laragon : geomatos.miensafleet.test →  slug "geomatos"
 *
 * Fallback local (DEV_TENANT_SLUG) :
 *   Quand APP_ENV=local et que l'hôte ne correspond à aucun sous-domaine connu
 *   (ex: miensa-fleet.test généré par Laragon), on utilise le tenant défini
 *   par DEV_TENANT_SLUG dans .env. Aucune entrée hosts supplémentaire requise.
 */
class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host           = $request->getHost();
        $landlordDomain = config('multitenancy.landlord_domain');

        // Domaine racine landlord → pas de tenant
        if ($host === $landlordDomain) {
            return null;
        }

        // Extraire le slug depuis le sous-domaine (ex: geomatos.miensafleet.ci → "geomatos")
        $landlordParts   = explode('.', $landlordDomain);
        $hostParts       = explode('.', $host);
        $expectedSegments = count($landlordParts) + 1;

        if (count($hostParts) === $expectedSegments) {
            $slug = $hostParts[0];

            // Sous-domaines réservés — jamais des tenants
            if (in_array($slug, ['admin', 'www', 'api', 'mail', 'smtp', 'ftp'])) {
                return null;
            }

            $tenant = Tenant::where('slug', $slug)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // ── Fallback local uniquement ─────────────────────────────────────
        // Permet d'accéder au panel via l'URL Laragon auto-générée
        // (ex: miensa-fleet.test) sans modifier le fichier hosts.
        // Définir DEV_TENANT_SLUG=dev dans .env pour activer.
        if (app()->isLocal()) {
            $devSlug = config('multitenancy.dev_tenant_slug');
            if ($devSlug) {
                return Tenant::where('slug', $devSlug)->first();
            }
        }

        return null;
    }
}
