<?php

namespace App\Notifications;

use App\Models\VehicleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le demandeur de la décision prise sur sa demande de véhicule.
 *
 * Couvre deux cas :
 *   - approved : demande acceptée, véhicule attribué
 *   - rejected : demande refusée, motif fourni si disponible
 *
 * Destinataire : $vehicleRequest->requester
 *
 * Envoyée par : VehicleRequestObserver::updated()
 *               lorsque status passe à 'approved' ou 'rejected'
 */
class VehicleRequestDecidedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly VehicleRequest $vehicleRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vr       = $this->vehicleRequest;
        $approved = $vr->status === 'approved';
        $reviewer = $vr->reviewedBy?->name ?? 'le gestionnaire de flotte';
        $start    = $vr->datetime_start->format('d/m/Y à H:i');
        $end      = $vr->datetime_end_planned->format('d/m/Y à H:i');
        $url      = route('requests.show', $vr->id);

        $mail = (new MailMessage)
            ->subject($approved
                ? "✅ Demande de véhicule #{$vr->id} approuvée"
                : "❌ Demande de véhicule #{$vr->id} rejetée"
            )
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',');

        if ($approved) {
            $vehicle = $vr->vehicle;
            $mail
                ->line("Votre demande de véhicule a été **approuvée** par {$reviewer}.")
                ->line("**Destination :** {$vr->destination}")
                ->line("**Période :** du {$start} au {$end}")
                ->line("**Véhicule attribué :** " . ($vehicle
                    ? "{$vehicle->brand} {$vehicle->model} ({$vehicle->plate})"
                    : 'à confirmer'))
                ->line($vr->self_driving
                    ? "**Mode :** Auto-conduite"
                    : ($vr->driver ? "**Chauffeur :** {$vr->driver->full_name}" : "**Mode :** Sans chauffeur désigné"))
                ->when($vr->review_notes, fn($m) => $m->line("**Note du gestionnaire :** {$vr->review_notes}"))
                ->action('Voir ma demande', $url)
                ->line('Présentez-vous au service de flotte à l\'heure de départ prévue.');
        } else {
            $mail
                ->line("Votre demande de véhicule a été **rejetée** par {$reviewer}.")
                ->line("**Destination :** {$vr->destination}")
                ->line("**Période demandée :** du {$start} au {$end}")
                ->when($vr->review_notes, fn($m) => $m->line("**Motif :** {$vr->review_notes}"))
                ->action('Voir ma demande', $url)
                ->line('Vous pouvez soumettre une nouvelle demande si nécessaire.');
        }

        return $mail->salutation('Cordialement, MiensaFleet');
    }

    public function toDatabase(object $notifiable): array
    {
        $vr = $this->vehicleRequest;

        return [
            'type'               => 'vehicle_request_decided',
            'vehicle_request_id' => $vr->id,
            'decision'           => $vr->status,          // 'approved' | 'rejected'
            'destination'        => $vr->destination,
            'reviewer_name'      => $vr->reviewedBy?->name,
            'review_notes'       => $vr->review_notes,
            'vehicle_plate'      => $vr->vehicle?->plate,
            'url'                => route('requests.show', $vr->id),
        ];
    }
}
