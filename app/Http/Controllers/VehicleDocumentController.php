<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VehicleDocumentController extends Controller
{
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'type'              => ['required', 'in:insurance,technical_control,registration,transport_permit,other'],
            'document_number'   => ['nullable', 'string', 'max:100'],
            'issue_date'        => ['nullable', 'date'],
            'expiry_date'       => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:150'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'file'              => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')
                ->store("vehicles/{$vehicle->id}/documents", 'public');
        }

        // Statut automatique selon la date d'expiration
        $status = 'valid';
        if (!empty($data['expiry_date'])) {
            $expiry = \Carbon\Carbon::parse($data['expiry_date']);
            if ($expiry->isPast()) {
                $status = 'expired';
            } elseif ($expiry->diffInDays(now()) <= 30) {
                $status = 'expiring_soon';
            }
        }

        $vehicle->documents()->create([
            'type'              => $data['type'],
            'document_number'   => $data['document_number'] ?? null,
            'issue_date'        => $data['issue_date'] ?? null,
            'expiry_date'       => $data['expiry_date'] ?? null,
            'issuing_authority' => $data['issuing_authority'] ?? null,
            'notes'             => $data['notes'] ?? null,
            'file_path'         => $filePath,
            'status'            => $status,
            'created_by'        => Auth::id(),
        ]);

        return back()->with('swal_success', 'Document ajouté avec succès.');
    }

    public function destroy(Vehicle $vehicle, VehicleDocument $document): RedirectResponse
    {
        abort_if($document->vehicle_id !== $vehicle->id, 403);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('swal_success', 'Document supprimé.');
    }
}
