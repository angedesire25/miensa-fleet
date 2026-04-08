<?php

namespace App\Notifications;

use App\Models\VehicleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie les approbateurs qu'une nouvelle demande de véhicule
 * vient d'être soumise et attend leur traitement.
 *
 * Destinataires : tous les utilisateurs ayant la permission
 * `vehicle_requests.approve` (fleet_manager, admin, super_admin…).
 *
 * Envoyée par : VehicleRequestController::store()
 */
class VehicleRequestSubmittedNotification extends Notification implements ShouldQueue
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
        $requester = $vr->requester;
        $start     = $vr->datetime_start->format('d/m/Y à H:i');
        $end       = $vr->datetime_end_planned->format('d/m/Y à H:i');
        $urgent    = $vr->is_urgent ? ' ⚠️ URGENTE' : '';
        $url       = route('requests.show', $vr->id);

        return (new MailMessage)
            ->subject("Nouvelle demande de véhicule{$urgent} — #{$vr->id}")
            ->greeting('Bonjour,')
            ->line("Une nouvelle demande de véhicule{$urgent} vient d'être soumise et attend votre validation.")
            ->line("**Demandeur :** " . ($requester?->name ?? 'Inconnu'))
            ->line("**Département :** " . ($requester?->department ?? '—'))
            ->line("**Destination :** {$vr->destination}")
            ->line("**Objet :** {$vr->purpose}")
            ->line("**Période :** du {$start} au {$end}")
            ->line("**Passagers :** {$vr->passengers}")
            ->action('Traiter la demande', $url)
            ->salutation('Cordialement, MiensaFleet');
    }

    public function toDatabase(object $notifiable): array
    {
        $vr = $this->vehicleRequest;

        return [
            'type'              => 'vehicle_request_submitted',
            'vehicle_request_id'=> $vr->id,
            'requester_name'    => $vr->requester?->name,
            'destination'       => $vr->destination,
            'is_urgent'         => $vr->is_urgent,
            'datetime_start'    => $vr->datetime_start->toIso8601String(),
            'url'               => route('requests.show', $vr->id),
        ];
    }
}
