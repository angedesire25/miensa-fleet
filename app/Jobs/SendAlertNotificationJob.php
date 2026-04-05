<?php

namespace App\Jobs;

use App\Models\Alert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job chargé d'envoyer les notifications d'une alerte sur ses canaux configurés.
 *
 * Canaux supportés (définis dans $alert->channels) :
 *   - in_app  → notification stockée en base, consommée par le frontend
 *   - email   → à brancher sur App\Mail\FleetAlertMail
 *   - sms     → à brancher sur le fournisseur SMS retenu (Twilio, OVH…)
 *
 * En cas d'échec de la queue : Laravel retente automatiquement ($tries fois).
 * Le `send_failed` est géré par AlertService::dispatchAlert() si le dispatch
 * lui-même échoue (avant même que le job soit traité).
 */
class SendAlertNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly Alert $alert,
    ) {}

    public function handle(): void
    {
        $channels = $this->alert->channels ?? ['in_app'];

        foreach ($channels as $channel) {
            match ($channel) {
                'in_app' => $this->sendInApp(),
                'email'  => $this->sendEmail(),
                'sms'    => $this->sendSms(),
                default  => Log::warning(
                    "SendAlertNotificationJob: canal inconnu « {$channel} »"
                    . " pour l'alerte #{$this->alert->id}."
                ),
            };
        }
    }

    // ── Canaux ─────────────────────────────────────────────────────────────

    private function sendInApp(): void
    {
        // La notification in-app est déjà persistée en base (l'alerte elle-même).
        // Le frontend la consomme via l'API — aucune action supplémentaire ici.
        Log::info("Alert in_app [{$this->alert->type}] #{$this->alert->id} : {$this->alert->title}");
    }

    private function sendEmail(): void
    {
        // TODO: Mail::to(config('fleet.alert_email'))->send(new FleetAlertMail($this->alert));
        Log::info(
            "Alert email [{$this->alert->type}] #{$this->alert->id}"
            . " — canal email non configuré."
        );
    }

    private function sendSms(): void
    {
        // TODO: brancher l'intégration SMS (Twilio, OVH…)
        Log::info(
            "Alert SMS [{$this->alert->type}] #{$this->alert->id}"
            . " — canal SMS non configuré."
        );
    }
}
