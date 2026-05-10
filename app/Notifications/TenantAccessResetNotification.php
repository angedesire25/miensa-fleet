<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantAccessResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly string $newPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Vos accès MiensaFleet ont été réinitialisés — {$this->tenant->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vos identifiants de connexion sur **{$this->tenant->name}** ont été réinitialisés par l'administrateur.")
            ->line("**Email :** {$notifiable->email}")
            ->line("**Mot de passe temporaire :** `{$this->newPassword}`")
            ->action("Accéder au panel", "http://{$this->tenant->domain}/login")
            ->line("Modifiez ce mot de passe dès votre première connexion.")
            ->salutation("L'équipe MiensaFleet");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'action'    => 'access_reset',
        ];
    }
}
