<?php

namespace App\Notifications;

use App\Models\Inspection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie l'auteur d'une fiche de contrôle qu'elle a été renvoyée
 * pour correction par un gestionnaire ou un contrôleur de flotte.
 *
 * Destinataire : l'utilisateur ayant créé la fiche (inspector)
 *
 * Envoyée par : InspectionController::reject()
 */
class InspectionRejectedNotification extends Notification
{

    public function __construct(
        public readonly Inspection $inspection,
        public readonly ?string $rejectionReason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'             => 'inspection_rejected',
            'inspection_id'    => $this->inspection->id,
            'vehicle_plate'    => $this->inspection->vehicle?->plate,
            'vehicle_label'    => trim(($this->inspection->vehicle?->brand ?? '') . ' ' . ($this->inspection->vehicle?->model ?? '')),
            'inspection_type'  => $this->inspection->inspection_type,
            'rejection_reason' => $this->rejectionReason,
            'url'              => route('inspections.edit', $this->inspection->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $insp     = $this->inspection;
        $vehicle  = $insp->vehicle;
        $typeMap  = ['departure' => 'Départ', 'return' => 'Retour', 'routine' => 'Routine'];
        $typeLabel = $typeMap[$insp->inspection_type] ?? ucfirst($insp->inspection_type);
        $url      = route('inspections.edit', $insp->id);

        $mail = (new MailMessage)
            ->subject("⚠️ Fiche de contrôle #{$insp->id} — Corrections demandées")
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre fiche de contrôle **{$typeLabel}** pour le véhicule **{$vehicle?->plate}** ({$vehicle?->brand} {$vehicle?->model}) a été renvoyée pour correction.");

        if ($this->rejectionReason) {
            $mail->line("**Motif indiqué :** {$this->rejectionReason}");
        }

        return $mail
            ->action('Corriger ma fiche', $url)
            ->line('Veuillez apporter les corrections nécessaires et resoumettre la fiche.')
            ->salutation('Cordialement, MiensaFleet');
    }
}
