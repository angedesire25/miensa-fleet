<?php

namespace App\Multitenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * Identifie le tenant en deux passes :
 *
 * 1. Correspondance exacte sur le champ `domain` du tenant (domaine personnalisé).
 *    Permet à app.geomatos.com d'être à la fois le LANDLORD_DOMAIN ET l'URL du tenant.
 *    Ex : tenant Geomatos a domain = "app.geomatos.com"
 *
 * 2. Correspondance par sous-domaine (fallback pour les futurs tenants classiques).
 *    Ex : autreclient.app.geomatos.com → slug "autreclient"
 *
 * 3. Fallback local uniquement (DEV_TENANT_SLUG).
 *
 * Admin panel : admin.app.geomatos.com → "admin" est réservé → null → routes admin.php.
 */
class DomainTenantFinder extends TenantFinder
{
    /** Sous-domaines jamais attribués à un tenant */
    private const RESERVED = ['admin', 'www', 'api', 'mail', 'smtp', 'ftp'];

    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();

        // ── Passe 1 : domaine personnalisé exact ──────────────────────────────
        // Priorité maximale — couvre le cas où landlord_domain = app.geomatos.com
        // ET que le tenant Geomatos a domain = "app.geomatos.com".
        $tenant = Tenant::where('domain', $host)->first();
        if ($tenant) {
            return $tenant;
        }

        // ── Passe 2 : sous-domaine classique ──────────────────────────────────
        $landlordDomain   = config('multitenancy.landlord_domain');
        $landlordParts    = explode('.', $landlordDomain);
        $hostParts        = explode('.', $host);
        $expectedSegments = count($landlordParts) + 1;

        if (count($hostParts) === $expectedSegments) {
            $slug = $hostParts[0];

            if (in_array($slug, self::RESERVED, true)) {
                return null; // domaine réservé (admin.*, www.*, …)
            }

            $tenant = Tenant::where('slug', $slug)->first();
            if ($tenant) {
                return $tenant;
            }
        }

        // ── Passe 3 : fallback local uniquement ───────────────────────────────
        if (app()->isLocal()) {
            $devSlug = config('multitenancy.dev_tenant_slug');
            if ($devSlug) {
                return Tenant::where('slug', $devSlug)->first();
            }
        }

        return null;
    }
}
