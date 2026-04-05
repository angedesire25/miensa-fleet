<?php

namespace App\Notifications;

use App\Models\Infraction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie un chauffeur que l'amende d'une infraction lui a été imputée.
 *
 * Envoyée par InfractionService::impute() lorsque $target = 'driver'
 * et qu'un driver_id est renseigné sur l'infraction.
 */
class InfractionImputedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Infraction $infraction,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $infraction = $this->infraction;
        $vehicle    = $infraction->vehicle;
        $amount     = $infraction->fine_amount
            ? number_format((float) $infraction->fine_amount, 2, ',', ' ') . ' €'
            : 'montant non renseigné';

        return (new MailMessage)
            ->subject("Infraction #{$infraction->id} imputée à votre charge")
            ->greeting('Bonjour,')
            ->line("Une infraction routière vous a été imputée.")
            ->line("**Type :** {$infraction->type}")
            ->line("**Date :** {$infraction->datetime_occurred->format('d/m/Y à H:i')}")
            ->line("**Lieu :** {$infraction->location}")
            ->line("**Véhicule :** " . ($vehicle ? "{$vehicle->brand} {$vehicle->model} ({$vehicle->plate})" : 'N/A'))
            ->line("**Montant de l'amende :** {$amount}")
            ->line("Pour toute question, rapprochez-vous de votre responsable de flotte.")
            ->salutation('Cordialement,');
    }
}
