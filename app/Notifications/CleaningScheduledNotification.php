<?php

namespace App\Notifications;

use App\Models\VehicleCleaning;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie le chauffeur / responsable qu'un nettoyage est planifié pour son véhicule.
 * Envoyée par : CleaningController::store() et ::update()
 */
class CleaningScheduledNotification extends Notification
{
    public function __construct(
        public readonly VehicleCleaning $cleaning,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $vehicle = $this->cleaning->vehicle;
        return [
            'type'         => 'cleaning_scheduled',
            'cleaning_id'  => $this->cleaning->id,
            'vehicle_plate'=> $vehicle?->plate,
            'vehicle_label'=> trim(($vehicle?->brand ?? '') . ' ' . ($vehicle?->model ?? '')),
            'scheduled_date'=> $this->cleaning->scheduled_date->translatedFormat('l d F Y'),
            'scheduled_time'=> $this->cleaning->scheduled_time,
            'cleaning_type' => $this->cleaning->getTypeLabel(),
            'url'          => route('cleanings.show', $this->cleaning->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $c       = $this->cleaning;
        $vehicle = $c->vehicle;
        $date    = $c->scheduled_date->translatedFormat('l d F Y');
        $url     = route('cleanings.show', $c->id);

        return (new MailMessage)
            ->subject("🚿 Nettoyage planifié — {$vehicle?->plate} le {$date}")
            ->greeting('Bonjour ' . ($notifiable->name ?? $notifiable->full_name ?? '') . ',')
            ->line("Un nettoyage **{$c->getTypeLabel()}** est prévu pour le véhicule **{$vehicle?->plate}** ({$vehicle?->brand} {$vehicle?->model}).")
            ->line("📅 **Date :** {$date} à {$c->scheduled_time}")
            ->when($c->notes, fn($m) => $m->line("📝 **Instructions :** {$c->notes}"))
            ->action('Voir le planning', $url)
            ->line('Merci de confirmer votre prise en charge via l\'application.')
            ->salutation('Cordialement, MiensaFleet');
    }
}
