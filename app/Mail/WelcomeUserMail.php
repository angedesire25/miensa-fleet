<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $installUrl;
    public string $loginUrl;

    public function __construct(
        public readonly User $user,
        public readonly string $temporaryPassword = '',
    ) {
        $this->installUrl = url('/install-app');
        $this->loginUrl   = url('/connexion');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur Miensa Fleet — Votre accès est prêt',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
