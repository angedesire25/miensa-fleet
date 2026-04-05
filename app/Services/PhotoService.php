<?php

namespace App\Services;

use App\Models\VehiclePhoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhotoService
{
    /**
     * Uploade une photo et crée l'entrée VehiclePhoto associée.
     *
     * Chemin de stockage : vehicles/{vehicleId}/{context}/{uuid}.{ext}
     * Disque : public (storage/app/public/)
     *
     * Si le contexte est 'vehicle_profile', toute photo de profil précédente
     * est supprimée (fichier + BDD) avant d'enregistrer la nouvelle.
     *
     * @throws ValidationException Si le fichier ne respecte pas les contraintes
     *                             (mimes : jpeg, jpg, png, webp — max 5 Mo)
     */
    public function upload(
        UploadedFile $file,
        int          $vehicleId,
        string       $context,
        ?Model       $photoable = null,
        ?string      $caption   = null,
    ): VehiclePhoto {
        // ── Validation ──────────────────────────────────────────────────────
        $validator = Validator::make(
            ['file' => $file],
            ['file' => 'required|file|mimes:jpeg,jpg,png,webp|max:5120'],
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // ── Stockage physique ────────────────────────────────────────────────
        $extension = $file->getClientOriginalExtension();
        $uuid      = (string) Str::uuid();
        $filePath  = $file->storeAs(
            "vehicles/{$vehicleId}/{$context}",
            "{$uuid}.{$extension}",
            'public',
        );

        // ── Remplacement de la photo de profil existante ─────────────────────
        // Un seul profil par véhicule : on supprime l'ancien avant d'insérer
        if ($context === 'vehicle_profile') {
            VehiclePhoto::where('vehicle_id', $vehicleId)
                ->where('context', 'vehicle_profile')
                ->each(fn(VehiclePhoto $old) => $this->delete($old));
        }

        // ── Persistance BDD ──────────────────────────────────────────────────
        return VehiclePhoto::create([
            'vehicle_id'     => $vehicleId,
            'photoable_type' => $photoable ? get_class($photoable) : null,
            'photoable_id'   => $photoable?->getKey(),
            'context'        => $context,
            'file_path'      => $filePath,
            'original_name'  => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'size_kb'        => (int) ceil($file->getSize() / 1024),
            'caption'        => $caption,
            'taken_at'       => now(),
            'uploaded_by'    => auth()->id(),
        ]);
    }

    /**
     * Uploade plusieurs photos en une seule opération.
     *
     * Délègue chaque fichier à upload(). La collection retournée conserve
     * l'ordre des fichiers d'entrée.
     *
     * @param  UploadedFile[]  $files
     * @throws ValidationException Si l'un des fichiers est invalide
     */
    public function uploadMultiple(
        array   $files,
        int     $vehicleId,
        string  $context,
        ?Model  $photoable = null,
    ): Collection {
        return collect($files)->map(
            fn(UploadedFile $file) => $this->upload($file, $vehicleId, $context, $photoable),
        );
    }

    /**
     * Supprime une photo : fichier physique puis entrée BDD.
     *
     * Si le fichier n'existe pas sur le disque, la suppression BDD
     * est tout de même effectuée pour maintenir la cohérence.
     */
    public function delete(VehiclePhoto $photo): void
    {
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();
    }

    /**
     * Retourne toutes les photos d'un véhicule pour un contexte donné,
     * triées de la plus récente à la plus ancienne.
     */
    public function getByContext(int $vehicleId, string $context): Collection
    {
        return VehiclePhoto::where('vehicle_id', $vehicleId)
            ->where('context', $context)
            ->latest()
            ->get();
    }

    /**
     * Retourne toutes les photos liées à un modèle Eloquent via la relation
     * polymorphique (photoable_type / photoable_id).
     *
     * Exemple : $photoService->getForModel($incident)
     *           → toutes les photos dont photoable est cet Incident
     */
    public function getForModel(Model $model): Collection
    {
        return VehiclePhoto::where('photoable_type', get_class($model))
            ->where('photoable_id', $model->getKey())
            ->latest()
            ->get();
    }
}
