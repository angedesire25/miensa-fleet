<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSuspendedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Accès suspendu — {$this->tenant->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("L'accès à votre espace **{$this->tenant->name}** sur MiensaFleet a été temporairement suspendu.")
            ->line("**Motif :** {$this->reason}")
            ->line("Pour toute question, contactez notre support.")
            ->salutation("L'équipe MiensaFleet");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id'   => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'reason'      => $this->reason,
        ];
    }
}
