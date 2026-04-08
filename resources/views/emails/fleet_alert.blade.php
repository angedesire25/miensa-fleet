<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $alert->title }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #0f172a;
        }

        .wrapper {
            max-width: 600px;
            margin: 32px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
        }

        .header {
            padding: 28px 32px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .logo-text {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }

        .logo-text span {
            color: #10b981;
        }

        .severity-bar {
            height: 4px;
        }

        .body {
            padding: 32px;
        }

        .alert-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 16px;
        }

        .alert-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px;
        }

        .alert-message {
            font-size: 15px;
            color: #475569;
            line-height: 1.6;
            margin: 0 0 24px;
        }

        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            font-size: 13px;
            color: #0f172a;
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .footer {
            padding: 20px 32px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }

        .footer a {
            color: #64748b;
        }
    </style>
</head>

<body>
    <div class="wrapper">

        {{-- Header --}}
        <div class="header">
            <div class="header-logo">
                <div class="logo-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24">
                        <path d="M3 17h2l1-3h12l1 3h2" stroke="white" stroke-width="1.8" stroke-linecap="round" />
                        <circle cx="7.5" cy="18.5" r="1.5" fill="white" />
                        <circle cx="16.5" cy="18.5" r="1.5" fill="white" />
                    </svg>
                </div>
                <span class="logo-text">Miensa<span>Fleet</span></span>
            </div>
        </div>

        {{-- Barre de sévérité --}}
        <div class="severity-bar" style="background:{{ $severityColor }};"></div>

        {{-- Corps --}}
        <div class="body">

            {{-- Badge sévérité --}}
            <span class="alert-badge" style="background:{{ $severityColor }}20;color:{{ $severityColor }};">
                {{ $severityLabel }}
            </span>

            <h1 class="alert-title">{{ $alert->title }}</h1>
            <p class="alert-message">{{ $alert->message }}</p>

            {{-- Fiche infos --}}
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Type d'alerte</span>
                    <span class="info-value">{{ $typeLabel }}</span>
                </div>
                @if ($alert->vehicle)
                    <div class="info-row">
                        <span class="info-label">Véhicule</span>
                        <span class="info-value">{{ $alert->vehicle->brand }} {{ $alert->vehicle->model }} —
                            {{ $alert->vehicle->plate }}</span>
                    </div>
                @endif
                @if ($alert->driver)
                    <div class="info-row">
                        <span class="info-label">Conducteur</span>
                        <span class="info-value">{{ $alert->driver->full_name }}</span>
                    </div>
                @endif
                @if ($alert->due_date)
                    <div class="info-row">
                        <span class="info-label">Échéance</span>
                        <span class="info-value"
                            style="color:{{ $severityColor }};">{{ $alert->due_date->format('d/m/Y') }}</span>
                    </div>
                @endif
                @if ($alert->days_remaining !== null)
                    <div class="info-row">
                        <span class="info-label">Jours restants</span>
                        <span class="info-value" style="color:{{ $severityColor }};">
                            @if ($alert->days_remaining < 0)
                                Dépassé de {{ abs($alert->days_remaining) }}j
                            @elseif($alert->days_remaining === 0)
                                Aujourd'hui
                            @else
                                {{ $alert->days_remaining }}j
                            @endif
                        </span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Générée le</span>
                    <span class="info-value">{{ $alert->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            {{-- CTA --}}
            <a href="{{ $url }}" class="btn">Voir l'alerte →</a>

            <p style="font-size:13px;color:#94a3b8;margin:0;">
                Vous recevez ce message car vous êtes gestionnaire de flotte sur MiensaFleet.
                Connectez-vous pour traiter cette alerte.
            </p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p style="margin:0 0 4px;">MiensaFleet — Gestion de flotte-developpé par ADN</p>
            <p style="margin:0;">
                <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
            </p>
        </div>

    </div>
</body>

</html>
