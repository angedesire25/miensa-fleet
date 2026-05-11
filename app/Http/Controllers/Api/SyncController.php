<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\TripLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    /**
     * Synchronise un lot de fiches d'inspection enregistrées hors ligne.
     *
     * Payload attendu :
     *   { "items": [ { vehicle_id, inspection_type, inspected_at, ... }, ... ] }
     */
    public function syncInspections(Request $request): JsonResponse
    {
        $items = $request->input('items', []);

        if (! is_array($items) || empty($items)) {
            return response()->json(['synced' => 0, 'errors' => ['Aucun élément reçu']], 422);
        }

        $synced = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            $validator = Validator::make($item, [
                'vehicle_id'      => 'required|integer|exists:vehicles,id',
                'inspection_type' => 'required|in:departure,return,routine',
                'inspected_at'    => 'required|date',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'index'    => $index,
                    'messages' => $validator->errors()->all(),
                ];
                continue;
            }

            try {
                $data = $this->buildInspectionData($item);
                Inspection::create($data);
                $synced++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'index'   => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'synced' => $synced,
            'errors' => $errors,
        ]);
    }

    /**
     * Synchronise un lot de journaux de trajet enregistrés hors ligne.
     *
     * Payload attendu :
     *   { "items": [ { vehicle_id, driver_id, datetime_start, ... }, ... ] }
     */
    public function syncTripLogs(Request $request): JsonResponse
    {
        $items = $request->input('items', []);

        if (! is_array($items) || empty($items)) {
            return response()->json(['synced' => 0, 'errors' => ['Aucun élément reçu']], 422);
        }

        $synced = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            $validator = Validator::make($item, [
                'vehicle_id'     => 'required|integer|exists:vehicles,id',
                'datetime_start' => 'required|date',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'index'    => $index,
                    'messages' => $validator->errors()->all(),
                ];
                continue;
            }

            try {
                $data = array_intersect_key($item, array_flip((new TripLog)->getFillable()));
                $data['user_id'] = Auth::id();
                TripLog::create($data);
                $synced++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'index'   => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'synced' => $synced,
            'errors' => $errors,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildInspectionData(array $item): array
    {
        $allowed = [
            'vehicle_id', 'driver_id', 'inspection_type', 'inspected_at',
            'location', 'km', 'fuel_level_pct',
            'oil_level', 'coolant_level', 'brake_fluid_level', 'oil_notes', 'oil_change_status',
            'oil_change_date', 'oil_change_km', 'oil_change_next_date', 'oil_change_next_km',
            'tire_pressure', 'tire_notes',
            'lights_status', 'lights_notes', 'brakes_status', 'brakes_notes',
            'registration_present', 'insurance_status', 'insurance_expiry',
            'technical_control_status', 'technical_control_expiry',
            'body_notes', 'general_observations',
        ];

        $data = array_intersect_key($item, array_flip($allowed));

        $data['inspector_id'] = Auth::id();
        $data['status']       = ($item['action'] ?? 'submit') === 'draft' ? 'draft' : 'submitted';
        $data['body_photos']  = [];

        if (Auth::user()->hasAnyRole(['driver_user', 'collaborator'])) {
            $data['driver_id'] = Auth::user()->driver_id;
        }

        return $data;
    }
}
