<?php

namespace App\Observers;

use App\Models\FuelRequest;
use Illuminate\Support\Facades\DB;

class FuelRequestObserver
{
    /**
     * Génère la référence unique avant création.
     * Format : FR-YYYYMM-XXXXX (ex: FR-202604-00017)
     */
    public function creating(FuelRequest $fuelRequest): void
    {
        if (empty($fuelRequest->reference)) {
            $fuelRequest->reference = $this->generateReference();
        }
    }

    // ── Méthodes privées ───────────────────────────────────────────────────

    private function generateReference(): string
    {
        $prefix = 'FR-' . now()->format('Ym') . '-';

        $last = DB::table('fuel_requests')
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->value('reference');

        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
