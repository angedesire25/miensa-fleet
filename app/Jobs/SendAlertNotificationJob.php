<?php

namespace App\Jobs;

use App\Mail\FleetAlertMail;
use App\Models\Alert;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job chargé d'envoyer les notifications d'une alerte sur ses canaux configurés.
 *
 * Canaux supportés (définis dans $alert->channels) :
 *   - in_app  → alerte déjà persistée en base, visible dans le dropdown topbar
 *   - email   → FleetAlertMail envoyé à tous les gestionnaires actifs
 *                (users avec permission alerts.manage)
 *   - sms     → à brancher sur un fournisseur SMS (Twilio, OVH SMS…)
 *
 * En cas d'échec : Laravel retente $tries fois avec backoff exponentiel.
 * Le `send_failed` est positionné par AlertService::dispatchAlert() si le
 * dispatch lui-même échoue avant même l'exécution du job.
 */
class SendAlertNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 60; // secondes entre chaque tentative

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

    /**
     * Canal in_app : l'alerte est déjà en base.
     * Le badge topbar et le module Alertes la consomment directement.
     */
    private function sendInApp(): void
    {
        Log::info("Alert in_app [{$this->alert->type}] #{$this->alert->id} : {$this->alert->title}");
    }

    /**
     * Canal email : envoie FleetAlertMail à tous les utilisateurs actifs
     * ayant la permission `alerts.manage` (fleet_manager, admin, super_admin).
     *
     * Charge les relations vehicle et driver pour le template HTML avant envoi.
     */
    private function sendEmail(): void
    {
        // Charger les relations pour le template
        $this->alert->loadMissing(['vehicle', 'driver']);

        // Destinataires : tous les gestionnaires actifs avec permission alerts.manage
        $recipients = User::permission('alerts.manage')
            ->where('status', 'active')
            ->whereNotNull('email')
            ->get(['id', 'name', 'email']);

        if ($recipients->isEmpty()) {
            Log::warning("SendAlertNotificationJob: aucun destinataire pour l'alerte #{$this->alert->id}.");
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email, $recipient->name)
                    ->queue(new FleetAlertMail($this->alert));

                Log::info(
                    "Alert email [{$this->alert->type}] #{$this->alert->id}"
                    . " → {$recipient->email}"
                );
            } catch (\Throwable $e) {
                Log::error(
                    "SendAlertNotificationJob: échec email → {$recipient->email}"
                    . " pour alerte #{$this->alert->id} : {$e->getMessage()}"
                );
            }
        }
    }

    /**
     * Canal SMS : à brancher sur un fournisseur (Twilio, OVH SMS, etc.)
     * Implémentation future.
     */
    private function sendSms(): void
    {
        Log::info(
            "Alert SMS [{$this->alert->type}] #{$this->alert->id}"
            . " — canal SMS non configuré."
        );
    }
}
