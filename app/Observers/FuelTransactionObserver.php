<?php

namespace App\Observers;

use App\Models\FuelTransaction;
use Illuminate\Support\Facades\DB;

class FuelTransactionObserver
{
    /**
     * Génère la référence unique avant création.
     * Format : FT-YYYYMM-XXXXX (ex: FT-202604-00017)
     */
    public function creating(FuelTransaction $transaction): void
    {
        if (empty($transaction->reference)) {
            $transaction->reference = $this->generateReference();
        }
    }

    /**
     * Après création, met à jour les statistiques carburant du véhicule
     * et passe la demande associée en "fulfilled" si applicable.
     */
    public function created(FuelTransaction $transaction): void
    {
        $this->updateVehicleFuelStats($transaction);
        $this->fulfillRelatedRequest($transaction);
    }

    // ── Méthodes privées ───────────────────────────────────────────────────

    private function generateReference(): string
    {
        $prefix = 'FT-' . now()->format('Ym') . '-';

        $last = DB::table('fuel_transactions')
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->value('reference');

        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Met à jour les champs carburant agrégés du véhicule.
     *
     * - total_liters_consumed : cumul des litres
     * - total_fuel_cost : cumul du montant
     * - km_last_fill / date_last_fill : dernier plein connu
     * - consumption_real : moyenne pondérée des consommations calculées
     */
    private function updateVehicleFuelStats(FuelTransaction $transaction): void
    {
        $stats = DB::table('fuel_transactions')
            ->where('vehicle_id', $transaction->vehicle_id)
            ->whereNull('deleted_at')
            ->selectRaw('
                SUM(liters) as total_liters,
                SUM(total_amount) as total_cost,
                MAX(odometer_km) as last_km,
                AVG(CASE WHEN consumption_per_100km IS NOT NULL THEN consumption_per_100km END) as avg_consumption
            ')
            ->first();

        DB::table('vehicles')
            ->where('id', $transaction->vehicle_id)
            ->update([
                'total_liters_consumed' => $stats->total_liters ?? 0,
                'total_fuel_cost'       => $stats->total_cost ?? 0,
                'km_last_fill'          => $stats->last_km,
                'date_last_fill'        => $transaction->fueled_at,
                'consumption_real'      => $stats->avg_consumption,
            ]);
    }

    /**
     * Si la transaction est liée à une demande approuvée,
     * passe cette demande en "fulfilled".
     */
    private function fulfillRelatedRequest(FuelTransaction $transaction): void
    {
        if (! $transaction->fuel_request_id) {
            return;
        }

        DB::table('fuel_requests')
            ->where('id', $transaction->fuel_request_id)
            ->where('status', 'approved')
            ->update(['status' => 'fulfilled']);
    }
}
