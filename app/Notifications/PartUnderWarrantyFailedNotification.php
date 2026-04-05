<?php

namespace App\Notifications;

use App\Models\PartReplacement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie les gestionnaires de flotte qu'une pièce encore sous garantie
 * vient d'être déclarée défaillante.
 *
 * Envoyée par IncidentService::reportPartFailure() à tous les utilisateurs
 * ayant le rôle 'fleet_manager' lorsque under_warranty_at_failure = true.
 */
class PartUnderWarrantyFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PartReplacement $part,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $vehicle = $this->part->vehicle;
        $garage  = $this->part->replacedByGarage;
        $plate   = $vehicle?->plate ?? "véhicule #{$this->part->vehicle_id}";
        $days    = $this->part->days_until_failure ?? '?';

        $mail = (new MailMessage)
            ->subject("Pièce sous garantie défaillante — {$plate}")
            ->greeting('Alerte flotte')
            ->line("La pièce **{$this->part->part_name}** montée sur le véhicule **{$plate}** est tombée en panne.")
            ->line("**Durée de service :** {$days} jour(s).")
            ->line("**Garantie valide jusqu'au :** {$this->part->warranty_expiry->format('d/m/Y')}.");

        if ($garage) {
            $mail->line("**Garage responsable :** {$garage->name}.");
        }

        return $mail->line('Veuillez contacter le garage pour déclencher la prise en charge sous garantie.');
    }
}
