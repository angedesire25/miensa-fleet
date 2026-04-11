<?php

namespace App\Multitenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * Identifie le tenant à partir du sous-domaine.
 *
 * geomatos.miensafleet.ci  →  slug = "geomatos"  →  Tenant::where('slug', 'geomatos')
 *
 * En développement local (Laragon), les vhosts sont configurés ainsi :
 *   miensafleet.test          →  landlord (pas de tenant)
 *   geomatos.miensafleet.test →  tenant geomatos
 *
 * La variable LANDLORD_DOMAIN (.env) définit le domaine racine.
 * Par défaut : miensafleet.ci  (prod) ou  miensafleet.test (dev)
 */
class SubdomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        $host = $request->getHost();                   // ex: geomatos.miensafleet.ci

        $landlordDomain = config('multitenancy.landlord_domain'); // ex: miensafleet.ci

        // Pas de tenant sur le domaine racine lui-même
        if ($host === $landlordDomain) {
            return null;
        }

        // Extraire le premier segment = slug du tenant
        $parts = explode('.', $host);
        if (count($parts) < 2) {
            return null;
        }

        $slug = $parts[0]; // "geomatos"

        return Tenant::where('slug', $slug)->first();
    }
}
