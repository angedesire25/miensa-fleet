<?php

namespace App\Mail;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email envoyé aux gestionnaires de flotte lors d'une alerte système.
 *
 * Couvre : documents expirants, vidanges dues, réparations en retard,
 *          infractions impayées, retours dépassés, etc.
 *
 * Destinataires : tous les User actifs avec permission `alerts.manage`
 *                 → sélection faite dans SendAlertNotificationJob::sendEmail()
 */
class FleetAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public readonly string $severityColor;
    public readonly string $severityLabel;
    public readonly string $typeLabel;
    public readonly string $url;

    /** Labels lisibles par type d'alerte */
    private const TYPE_LABELS = [
        'document_expiring'          => 'Document expirant',
        'insurance_expiring'         => 'Assurance expirante',
        'insurance_expired'          => 'Assurance expirée',
        'technical_control_expiring' => 'Visite technique expirante',
        'technical_control_expired'  => 'Visite technique expirée',
        'license_expiring'           => 'Permis expirant',
        'license_expired'            => 'Permis expiré',
        'medical_fitness_due'        => 'Visite médicale requise',
        'maintenance_due'            => 'Maintenance requise',
        'oil_change_due'             => 'Vidange à prévoir',
        'oil_change_overdue'         => 'Vidange dépassée',
        'vehicle_idle'               => 'Véhicule immobilisé',
        'infraction_unpaid'          => 'Amende impayée',
        'assignment_conflict'        => 'Conflit affectation',
        'inspection_overdue'         => 'Inspection en retard',
        'fuel_anomaly'               => 'Anomalie carburant',
        'part_warranty_expiring'     => 'Garantie pièce expirante',
        'repair_overdue'             => 'Réparation en retard',
        'vehicle_return_overdue'     => 'Retour en retard',
        'request_pending_timeout'    => 'Demande sans réponse',
    ];

    public function __construct(public readonly Alert $alert)
    {
        $this->severityColor = match ($alert->severity) {
            'critical' => '#dc2626',
            'warning'  => '#d97706',
            default    => '#2563eb',
        };

        $this->severityLabel = match ($alert->severity) {
            'critical' => 'Critique',
            'warning'  => 'Avertissement',
            default    => 'Information',
        };

        $this->typeLabel = self::TYPE_LABELS[$alert->type] ?? ucfirst(str_replace('_', ' ', $alert->type));

        $this->url = route('alerts.show', $alert->id);
    }

    public function envelope(): Envelope
    {
        $prefix = match ($this->alert->severity) {
            'critical' => '[🔴 CRITIQUE]',
            'warning'  => '[🟡 AVERTISSEMENT]',
            default    => '[🔵 INFO]',
        };

        return new Envelope(
            subject: "{$prefix} {$this->alert->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fleet_alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
