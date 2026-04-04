<?php

namespace App\Observers;

use App\Models\DriverDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverDocumentObserver
{
    /**
     * Après chaque création ou mise à jour d'un document chauffeur,
     * recalcule le statut en fonction de expiry_date.
     *
     * Règles :
     *   - expiry_date passée             → expired
     *   - expiry_date entre J et J+30    → expiring_soon
     *   - expiry_date au-delà de J+30   → valid
     *   - expiry_date null               → pas de recalcul (statut inchangé)
     *
     * Utilise DB::table() pour éviter de déclencher à nouveau l'événement
     * saved et créer une boucle infinie.
     */
    public function saved(DriverDocument $document): void
    {
        if ($document->expiry_date === null) {
            return;
        }

        $newStatus = $this->computeStatus($document->expiry_date);

        if ($newStatus === $document->status) {
            return; // Rien à mettre à jour, évite une écriture inutile
        }

        DB::table('driver_documents')
            ->where('id', $document->id)
            ->update(['status' => $newStatus]);
    }

    // ── Méthode privée ─────────────────────────────────────────────────────

    private function computeStatus(Carbon $expiryDate): string
    {
        $today  = now()->startOfDay();
        $expiry = $expiryDate->copy()->startOfDay();

        if ($expiry->lt($today)) {
            return 'expired';
        }

        if ($expiry->lte($today->copy()->addDays(30))) {
            return 'expiring_soon';
        }

        return 'valid';
    }
}
