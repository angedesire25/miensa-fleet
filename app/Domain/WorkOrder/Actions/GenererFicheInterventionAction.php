<?php

namespace App\Domain\WorkOrder\Actions;

use App\Models\Repair;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Spatie\Multitenancy\Models\Tenant;

class GenererFicheInterventionAction
{
    /**
     * Génère la fiche DI PDF pour une réparation.
     *
     * Retourne la réponse HTTP de téléchargement. Si le PDF a déjà été stocké
     * (fiche_di_path renseigné) et que le fichier existe encore, il est renvoyé
     * directement sans re-génération.
     */
    public function handle(Repair $repair): Response
    {
        $repair->loadMissing(['vehicle', 'garage', 'incident', 'faultCodes']);

        if (empty($repair->di_number)) {
            $repair->update(['di_number' => Repair::generateDiNumber($repair->vehicle_id)]);
            $repair->refresh();
        }

        $filename = 'DI_' . $repair->di_number . '.pdf';
        $tenant   = Tenant::current();

        // ── Chemin de stockage (S3 ou local selon config) ───────────────────
        $storagePath = ($tenant ? $tenant->id : 'shared') . '/fiches/' . $filename;

        // Si déjà stocké et toujours présent, renvoyer directement
        if ($repair->fiche_di_path && Storage::exists($repair->fiche_di_path)) {
            $content = Storage::get($repair->fiche_di_path);
            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // ── Génération PDF ───────────────────────────────────────────────────
        $logoPath = $this->resolveLogo();

        $pdf = Pdf::loadView('pdf.demande_intervention', [
            'repair'   => $repair,
            'logoPath' => $logoPath,
            'tenant'   => $tenant,
            'subtitle' => null,
        ])
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont'          => 'Arial',
            'dpi'                  => 150,
        ]);

        $content = $pdf->output();

        // ── Stockage ─────────────────────────────────────────────────────────
        Storage::put($storagePath, $content);
        $repair->updateQuietly(['fiche_di_path' => $storagePath]);

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveLogo(): ?string
    {
        $candidates = [
            public_path('images/logo.png'),
            public_path('images/logo.svg'),
            public_path('uploads/logo.png'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
