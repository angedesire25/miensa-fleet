<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Demande d'Intervention {{ $repair->di_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9pt;
        color: #1a1a2e;
        background: #fff;
    }
    /* ── Mise en page ── */
    .page {
        width: 100%;
        padding: 14mm 14mm 10mm 14mm;
    }
    /* ── En-tête ── */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6mm;
    }
    .header-logo-cell {
        width: 38%;
        vertical-align: middle;
        padding-right: 8mm;
    }
    .header-logo-cell img {
        max-width: 140px;
        max-height: 52px;
    }
    .header-logo-text {
        font-size: 16pt;
        font-weight: bold;
        color: #059669;
        letter-spacing: -0.5px;
    }
    .header-title-cell {
        vertical-align: middle;
        text-align: center;
        padding: 6px 12px;
        background: linear-gradient(135deg, #059669, #047857);
        border-radius: 6px;
    }
    .header-title-main {
        font-size: 14pt;
        font-weight: bold;
        color: #fff;
        letter-spacing: 0.5px;
    }
    .header-title-sub {
        font-size: 8pt;
        color: rgba(255,255,255,0.85);
        margin-top: 2px;
    }
    .header-di-cell {
        width: 28%;
        vertical-align: middle;
        text-align: right;
        padding-left: 8mm;
    }
    .di-badge {
        display: inline-block;
        background: #f0fdf4;
        border: 1.5px solid #059669;
        border-radius: 5px;
        padding: 5px 10px;
        text-align: center;
    }
    .di-badge-label {
        font-size: 6.5pt;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .di-badge-number {
        font-size: 11pt;
        font-weight: bold;
        color: #059669;
        font-family: 'Courier New', monospace;
        letter-spacing: 0.5px;
    }
    .di-badge-date {
        font-size: 7pt;
        color: #64748b;
        margin-top: 2px;
    }
    /* ── Ligne séparatrice ── */
    .divider {
        border: none;
        border-top: 2px solid #059669;
        margin-bottom: 5mm;
    }
    /* ── Bloc générique ── */
    .block {
        margin-bottom: 5mm;
    }
    .block-title {
        font-size: 8.5pt;
        font-weight: bold;
        color: #fff;
        background: #0f172a;
        padding: 4px 10px;
        border-radius: 3px 3px 0 0;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .block-body {
        border: 1px solid #cbd5e1;
        border-top: none;
        border-radius: 0 0 4px 4px;
        padding: 0;
    }
    /* ── Grille champs ── */
    .fields-table {
        width: 100%;
        border-collapse: collapse;
    }
    .fields-table td {
        padding: 4px 8px;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
    }
    .fields-table tr:last-child td {
        border-bottom: none;
    }
    .fields-table .field-label {
        font-size: 7pt;
        font-weight: bold;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        width: 32%;
        padding-top: 5px;
    }
    .fields-table .field-value {
        font-size: 8.5pt;
        color: #0f172a;
    }
    .fields-table .field-value.mono {
        font-family: 'Courier New', monospace;
        font-size: 8pt;
    }
    .fields-table .field-value.bold {
        font-weight: bold;
    }
    /* ── Grille 2 colonnes ── */
    .col2-table {
        width: 100%;
        border-collapse: collapse;
    }
    .col2-table td {
        width: 50%;
        padding: 0;
        vertical-align: top;
    }
    .col2-table td + td {
        border-left: 1px solid #cbd5e1;
    }
    /* ── Badge statut ── */
    .status-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 7.5pt;
        font-weight: bold;
    }
    .status-sent          { background: #eff6ff; color: #1e40af; }
    .status-diagnosing    { background: #fef3c7; color: #92400e; }
    .status-repairing     { background: #fff7ed; color: #9a3412; }
    .status-waiting_parts { background: #ede9fe; color: #5b21b6; }
    .status-completed     { background: #f0fdf4; color: #166534; }
    .status-returned      { background: #f0fdf4; color: #166534; }
    /* ── Tableau codes panne ── */
    .fc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 8pt;
    }
    .fc-table th {
        background: #f8fafc;
        font-size: 7pt;
        font-weight: bold;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 4px 7px;
        text-align: left;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .fc-table td {
        padding: 4px 7px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }
    .fc-table tr:last-child td {
        border-bottom: none;
    }
    .fc-table .code-cell {
        font-family: 'Courier New', monospace;
        font-size: 7.5pt;
        font-weight: bold;
        color: #059669;
        white-space: nowrap;
        width: 8%;
    }
    .fc-table .cat-cell {
        width: 13%;
    }
    .cat-badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 99px;
        font-size: 6.5pt;
        font-weight: bold;
    }
    .cat-anomaly   { background: #fef3c7; color: #92400e; }
    .cat-breakdown { background: #fee2e2; color: #991b1b; }
    .cat-wear      { background: #f3f4f6; color: #374151; }
    .cat-accident  { background: #ede9fe; color: #5b21b6; }
    .cat-other     { background: #e0f2fe; color: #075985; }
    .res-badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 99px;
        font-size: 6.5pt;
        font-weight: bold;
        white-space: nowrap;
    }
    .res-pending     { background: #fef3c7; color: #92400e; }
    .res-resolved    { background: #d1fae5; color: #065f46; }
    .res-partial     { background: #e0e7ff; color: #3730a3; }
    .res-deferred    { background: #f3f4f6; color: #374151; }
    .res-not_covered { background: #fee2e2; color: #991b1b; }
    .empty-row td {
        color: #94a3b8;
        font-style: italic;
        font-size: 7.5pt;
        padding: 8px 7px;
        text-align: center;
    }
    /* ── Zone signatures ── */
    .sig-section {
        margin-top: 5mm;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
    }
    .sig-section-title {
        background: #0f172a;
        color: #fff;
        font-size: 8.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 4px 10px;
        border-radius: 3px 3px 0 0;
    }
    .sig-grid {
        width: 100%;
        border-collapse: collapse;
    }
    .sig-grid td {
        width: 25%;
        vertical-align: top;
        padding: 8px 10px;
        border-right: 1px solid #e2e8f0;
        text-align: center;
    }
    .sig-grid td:last-child {
        border-right: none;
    }
    .sig-title {
        font-size: 7pt;
        font-weight: bold;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }
    .sig-subtitle {
        font-size: 6.5pt;
        color: #94a3b8;
        margin-bottom: 6px;
    }
    .sig-image-box {
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        height: 52px;
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .sig-image-box img {
        max-width: 100%;
        max-height: 50px;
    }
    .sig-empty-box {
        border: 1.5px dashed #cbd5e1;
        border-radius: 4px;
        height: 52px;
        background: #fafafa;
    }
    .sig-date {
        font-size: 6.5pt;
        color: #94a3b8;
        margin-top: 4px;
    }
    /* ── Pied de page ── */
    .footer {
        margin-top: 7mm;
        border-top: 1px solid #e2e8f0;
        padding-top: 4px;
        display: flex;
        justify-content: space-between;
        font-size: 6.5pt;
        color: #94a3b8;
    }
    /* ── Helpers ── */
    .text-muted    { color: #94a3b8; font-style: italic; }
    .text-green    { color: #059669; }
    .text-bold     { font-weight: bold; }
    .mt2           { margin-top: 2px; }
    .nowrap        { white-space: nowrap; }
</style>
</head>
<body>
<div class="page">

    {{-- ════════════════════════════════════════════════════════
         BLOC 1 — EN-TÊTE
         ════════════════════════════════════════════════════════ --}}
    <table class="header-table">
        <tr>
            {{-- Logo / Nom société --}}
            <td class="header-logo-cell">
                @if($logoPath && file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo">
                @else
                    <span class="header-logo-text">MiensaFleet</span>
                @endif
                @if($tenant)
                    <div style="font-size:7pt;color:#64748b;margin-top:3px;">{{ $tenant->name }}</div>
                @endif
            </td>
            {{-- Titre document --}}
            <td class="header-title-cell">
                <div class="header-title-main">DEMANDE D'INTERVENTION</div>
                <div class="header-title-sub">Bon de réparation — Gestion de flotte</div>
            </td>
            {{-- Numéro DI --}}
            <td class="header-di-cell">
                <div class="di-badge">
                    <div class="di-badge-label">Réf. DI</div>
                    <div class="di-badge-number">{{ $repair->di_number }}</div>
                    <div class="di-badge-date">Émis le {{ $repair->created_at->format('d/m/Y') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ════════════════════════════════════════════════════════
         BLOC 2 — IDENTIFICATION DU VÉHICULE
         ════════════════════════════════════════════════════════ --}}
    <div class="block">
        <div class="block-title">&#9660; Identification du véhicule</div>
        <div class="block-body">
            <table class="col2-table">
                <tr>
                    {{-- Colonne gauche --}}
                    <td>
                        <table class="fields-table">
                            <tr>
                                <td class="field-label">Immatriculation</td>
                                <td class="field-value bold mono">{{ $repair->vehicle->plate ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Marque / Modèle</td>
                                <td class="field-value">{{ ($repair->vehicle->brand ?? '') . ' ' . ($repair->vehicle->model ?? '') }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Type carrosserie</td>
                                <td class="field-value">{{ $repair->vehicle_type_body ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Kilométrage départ</td>
                                <td class="field-value">{{ $repair->km_at_departure ? number_format($repair->km_at_departure, 0, ',', ' ') . ' km' : '—' }}</td>
                            </tr>
                        </table>
                    </td>
                    {{-- Colonne droite --}}
                    <td>
                        <table class="fields-table">
                            <tr>
                                <td class="field-label">Garage</td>
                                <td class="field-value bold">{{ $repair->garage->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Statut</td>
                                <td class="field-value">
                                    @php
                                        $statusLabels = [
                                            'sent'               => 'Envoyé',
                                            'diagnosing'         => 'Diagnostic',
                                            'repairing'          => 'En réparation',
                                            'waiting_parts'      => 'Attente pièces',
                                            'completed'          => 'Terminé',
                                            'returned'           => 'Retourné',
                                            'returned_with_issue'=> 'Retour avec réserve',
                                        ];
                                    @endphp
                                    <span class="status-badge status-{{ $repair->status }}">
                                        {{ $statusLabels[$repair->status] ?? $repair->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="field-label">Réf. OR initiale</td>
                                <td class="field-value mono">{{ $repair->or_initial_reference ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Kilométrage retour</td>
                                <td class="field-value">{{ $repair->km_at_return ? number_format($repair->km_at_return, 0, ',', ' ') . ' km' : '—' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         BLOC 3 — NATURE & DATES D'INTERVENTION
         ════════════════════════════════════════════════════════ --}}
    <div class="block">
        <div class="block-title">&#9660; Nature & dates d'intervention</div>
        <div class="block-body">
            <table class="col2-table">
                <tr>
                    <td>
                        <table class="fields-table">
                            <tr>
                                <td class="field-label">Type d'intervention</td>
                                <td class="field-value">
                                    @php
                                        $repairTypeLabels = [
                                            'corrective'    => 'Corrective (Retour Atelier)',
                                            'preventive'    => 'Réglementaire (Révision)',
                                            'warranty'      => 'Sous Garantie',
                                            'recall'        => 'Rappel Constructeur',
                                            'body_repair'   => 'Carrosserie',
                                            'mechanical'    => 'Mécanique',
                                            'electrical'    => 'Électrique',
                                            'tire'          => 'Pneumatiques',
                                            'painting'      => 'Peinture',
                                            'glass'         => 'Vitrerie',
                                            'full_service'  => 'Révision complète',
                                            'other'         => 'Autre',
                                        ];
                                    @endphp
                                    {{ $repairTypeLabels[$repair->repair_type] ?? $repair->repair_type }}
                                </td>
                            </tr>
                            <tr>
                                <td class="field-label">Date d'envoi garage</td>
                                <td class="field-value">{{ $repair->datetime_sent?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Disponibilité souhaitée</td>
                                <td class="field-value{{ $repair->is_overdue ? ' text-bold' : '' }}" style="{{ $repair->is_overdue ? 'color:#ef4444;' : '' }}">
                                    {{ $repair->availability_date_requested?->format('d/m/Y') ?? '—' }}
                                    @if($repair->is_overdue)
                                        <span style="font-size:6.5pt;"> &#9650; DÉPASSÉE</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="field-label">Durée immobilisation</td>
                                <td class="field-value">
                                    @if($repair->immobilization_days !== null)
                                        {{ $repair->immobilization_days }} jour{{ $repair->immobilization_days > 1 ? 's' : '' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table class="fields-table">
                            <tr>
                                <td class="field-label">Date retour effectif</td>
                                <td class="field-value">{{ $repair->actual_exit_date?->format('d/m/Y') ?? $repair->datetime_returned?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Sinistre lié</td>
                                <td class="field-value">{{ $repair->incident ? '#' . $repair->incident->id : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Devis (FCFA)</td>
                                <td class="field-value">{{ $repair->quote_amount ? number_format((float)$repair->quote_amount, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="field-label">Facture (FCFA)</td>
                                <td class="field-value bold">{{ $repair->invoice_amount ? number_format((float)$repair->invoice_amount, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Diagnostic --}}
            @if($repair->diagnosis)
            <div style="padding:6px 8px;border-top:1px solid #f1f5f9;background:#fafafa;">
                <div style="font-size:7pt;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Diagnostic général</div>
                <div style="font-size:8pt;color:#0f172a;line-height:1.5;">{{ $repair->diagnosis }}</div>
            </div>
            @endif

            {{-- Travaux réalisés --}}
            @if($repair->work_performed)
            <div style="padding:6px 8px;border-top:1px solid #f1f5f9;">
                <div style="font-size:7pt;font-weight:bold;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:3px;">Travaux réalisés</div>
                <div style="font-size:8pt;color:#0f172a;line-height:1.5;">{{ $repair->work_performed }}</div>
            </div>
            @endif

            {{-- Notes --}}
            @if($repair->notes)
            <div style="padding:6px 8px;border-top:1px solid #f1f5f9;background:#fffbeb;">
                <div style="font-size:7pt;font-weight:bold;color:#92400e;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Notes</div>
                <div style="font-size:8pt;color:#78350f;line-height:1.4;">{{ $repair->notes }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         BLOC 4 — INVENTAIRE DES DYSFONCTIONNEMENTS
         ════════════════════════════════════════════════════════ --}}
    <div class="block">
        <div class="block-title">&#9660; Inventaire des dysfonctionnements</div>
        <div class="block-body">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Catégorie</th>
                        <th style="width:28%;">Dysfonctionnement déclaré</th>
                        <th style="width:28%;">Diagnostic garage / Travaux</th>
                        <th>Résolution</th>
                        <th style="text-align:right;">Coût</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repair->faultCodes as $fc)
                    <tr>
                        <td class="code-cell">{{ $fc->code }}</td>
                        <td class="cat-cell">
                            @php
                                $catClass = [
                                    'anomaly'   => 'cat-anomaly',
                                    'breakdown' => 'cat-breakdown',
                                    'wear'      => 'cat-wear',
                                    'accident'  => 'cat-accident',
                                    'other'     => 'cat-other',
                                ][$fc->category] ?? 'cat-other';
                            @endphp
                            <span class="cat-badge {{ $catClass }}">{{ $fc->category_label }}</span>
                        </td>
                        <td style="font-size:8pt;">{{ $fc->label }}</td>
                        <td style="font-size:7.5pt;color:#374151;">
                            @if($fc->garage_diagnosis)
                                <span style="font-weight:bold;">Diag :</span> {{ $fc->garage_diagnosis }}<br>
                            @endif
                            @if($fc->work_performed)
                                <span style="font-weight:bold;">Trav. :</span> {{ $fc->work_performed }}
                            @endif
                            @if(!$fc->garage_diagnosis && !$fc->work_performed)
                                <span style="color:#94a3b8;font-style:italic;">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $resClass = [
                                    'pending'     => 'res-pending',
                                    'resolved'    => 'res-resolved',
                                    'partial'     => 'res-partial',
                                    'deferred'    => 'res-deferred',
                                    'not_covered' => 'res-not_covered',
                                ][$fc->resolution_status] ?? 'res-pending';
                            @endphp
                            <span class="res-badge {{ $resClass }}">{{ $fc->resolution_label }}</span>
                        </td>
                        <td style="text-align:right;font-size:8pt;white-space:nowrap;">
                            {{ $fc->fault_cost ? number_format((float)$fc->fault_cost, 0, ',', ' ') . ' FCFA' : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row">
                        <td colspan="6">Aucun code dysfonctionnement enregistré sur cette DI.</td>
                    </tr>
                    @endforelse
                    {{-- Ligne total si au moins un coût renseigné --}}
                    @if($repair->faultCodes->whereNotNull('fault_cost')->isNotEmpty())
                    <tr style="background:#f8fafc;font-weight:bold;">
                        <td colspan="5" style="text-align:right;font-size:8pt;color:#374151;padding:4px 7px;">Total codes panne :</td>
                        <td style="text-align:right;font-size:8.5pt;color:#059669;padding:4px 7px;white-space:nowrap;">
                            {{ number_format((float)$repair->faultCodes->sum('fault_cost'), 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         BLOC 5 — SIGNATURES
         ════════════════════════════════════════════════════════ --}}
    <div class="sig-section">
        <div class="sig-section-title">&#9660; Signatures & approbations</div>
        <table class="sig-grid">
            <tr>
                {{-- Signature entreprise (départ) --}}
                <td>
                    <div class="sig-title">Entreprise</div>
                    <div class="sig-subtitle">Responsable remettant</div>
                    @if($repair->signature_company_path && Storage::disk('public')->exists($repair->signature_company_path))
                        <div class="sig-image-box">
                            <img src="{{ Storage::disk('public')->path($repair->signature_company_path) }}">
                        </div>
                    @else
                        <div class="sig-empty-box"></div>
                    @endif
                    <div class="sig-date">Date : {{ $repair->datetime_sent?->format('d/m/Y') ?? '__ /__ /____' }}</div>
                </td>
                {{-- Signature garage (réception) --}}
                <td>
                    <div class="sig-title">Garage</div>
                    <div class="sig-subtitle">Responsable réception</div>
                    @if($repair->signature_garage_path && Storage::disk('public')->exists($repair->signature_garage_path))
                        <div class="sig-image-box">
                            <img src="{{ Storage::disk('public')->path($repair->signature_garage_path) }}">
                        </div>
                    @else
                        <div class="sig-empty-box"></div>
                    @endif
                    <div class="sig-date">Date : __ /__ /____</div>
                </td>
                {{-- Signature entreprise (retour) --}}
                <td>
                    <div class="sig-title">Entreprise</div>
                    <div class="sig-subtitle">Responsable réceptionnant</div>
                    @if($repair->signature_company_exit_path && Storage::disk('public')->exists($repair->signature_company_exit_path))
                        <div class="sig-image-box">
                            <img src="{{ Storage::disk('public')->path($repair->signature_company_exit_path) }}">
                        </div>
                    @else
                        <div class="sig-empty-box"></div>
                    @endif
                    <div class="sig-date">Date : {{ $repair->actual_exit_date?->format('d/m/Y') ?? $repair->datetime_returned?->format('d/m/Y') ?? '__ /__ /____' }}</div>
                </td>
                {{-- Signature garage (sortie) --}}
                <td>
                    <div class="sig-title">Garage</div>
                    <div class="sig-subtitle">Responsable remettant</div>
                    @if($repair->signature_garage_exit_path && Storage::disk('public')->exists($repair->signature_garage_exit_path))
                        <div class="sig-image-box">
                            <img src="{{ Storage::disk('public')->path($repair->signature_garage_exit_path) }}">
                        </div>
                    @else
                        <div class="sig-empty-box"></div>
                    @endif
                    <div class="sig-date">Date : __ /__ /____</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── Pied de page ── --}}
    <table style="width:100%;margin-top:5mm;border-top:1px solid #e2e8f0;padding-top:3mm;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="font-size:6.5pt;color:#94a3b8;">
                Document généré le {{ now()->format('d/m/Y à H:i') }} — MiensaFleet &copy; {{ now()->year }}
            </td>
            <td style="font-size:6.5pt;color:#94a3b8;text-align:center;">
                {{ $repair->di_number }} &mdash; {{ $repair->vehicle->plate ?? '' }}
            </td>
            <td style="font-size:6.5pt;color:#94a3b8;text-align:right;">
                Document confidentiel — usage interne
            </td>
        </tr>
    </table>

</div>
</body>
</html>
