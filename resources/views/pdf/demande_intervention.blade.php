<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>DI {{ $repair->di_number }}</title>
<style>
@page { size: A4 portrait; margin: 10mm 10mm 42mm 10mm; }
body { font-family: Arial, sans-serif; font-size: 9px; color: #000; margin: 0; padding: 0; }
table { width: 100%; border-collapse: collapse; }
td, th { border: 0.5px solid #000; padding: 3px 5px; vertical-align: top; }
.gras { font-weight: bold; }
.italic { font-style: italic; }
.centre { text-align: center; }
.droite { text-align: right; }
.fond-gris { background-color: #f0f0f0; }
.titre-section {
    font-weight: bold; font-style: italic;
    padding: 4px 5px; border: 0.5px solid #000;
    margin-top: 6px; margin-bottom: 0; font-size: 9px;
    background-color: #f8f8f8;
}
.ligne-signature { border-bottom: 0.5px solid #000; margin: 0 10px; margin-top: 55px; }
.pied-fixe { position: fixed; bottom: 0; left: 0; right: 0; }
</style>
</head>
<body>

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
    $faultCodes = $repair->faultCodes;
    $minDysf    = 5;
    $minDiag    = 4;
@endphp

{{-- ══════════════════════════════════════════════════════════
     PIED DE PAGE FIXÉ — visible en bas sur chaque page
     ══════════════════════════════════════════════════════════ --}}
<div class="pied-fixe">
    <table>
        <tr>
            {{-- Signature entreprise (entrée) --}}
            <td style="width:33.33%;">
                <div class="italic" style="font-size:8px; margin-bottom:4px;">
                    Signature {{ $tenant->name ?? '' }} (Entrée Garage)
                </div>
                <div style="height:80px;"></div>
            </td>
            {{-- Signature atelier (entrée) --}}
            <td style="width:33.33%;">
                <div class="italic" style="font-size:8px; margin-bottom:4px;">
                    Signature Atelier (Entrée Garage)
                </div>
                <div style="height:80px;"></div>
            </td>
            {{-- Dates --}}
            <td style="width:33.33%;">
                <div class="italic" style="font-size:8px;">Date de mise à disponibilité souhaitée</div>
                @if($repair->availability_date_requested)
                    <div class="gras" style="margin-bottom:4px;">{{ $repair->availability_date_requested->format('d/m/Y') }}</div>
                @else
                    <div style="height:14px; margin-bottom:4px;"></div>
                @endif
                <div style="border-top:0.5px solid #000; margin: 4px 0;"></div>
                <div class="italic" style="font-size:8px;">Date de sortie</div>
                @if($repair->actual_exit_date)
                    <div>{{ $repair->actual_exit_date->format('d/m/Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════════════════════════
     EN-TÊTE (3 colonnes)
     ══════════════════════════════════════════════════════════ --}}
<table style="margin-bottom:5px;">
    <tr>
        {{-- Gauche 25% : Logo ou nom garage --}}
        <td style="width:25%; border:none; vertical-align:middle; padding:0 6px 0 0;">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" style="max-width:90px; max-height:45px;">
            @else
                <span class="gras" style="font-size:11px;">{{ $repair->garage->name ?? ($tenant->name ?? 'FLOTTE') }}</span>
            @endif
        </td>
        {{-- Centre 50% : Nom société --}}
        <td style="width:50%; border:none; text-align:center; vertical-align:middle; padding:0 6px;">
            <div style="font-size:16px; font-weight:bold;">{{ $tenant->name ?? 'GESTION DE FLOTTE' }}</div>
            @if(!empty($subtitle))
                <div style="font-size:8px; margin-top:2px;">{{ $subtitle }}</div>
            @endif
        </td>
        {{-- Droite 25% : Version --}}
        <td style="width:25%; border:0.5px solid #000; text-align:right; vertical-align:top; padding:3px 5px;">
            <span style="font-size:7px;">VERSION 001/{{ now()->format('m-Y') }} - {{ $tenant->slug ?? '' }}</span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     SÉPARATEUR — Numéro DI + Date
     ══════════════════════════════════════════════════════════ --}}
<table style="margin-bottom:5px;">
    <tr>
        <td style="width:60%;">
            <span class="gras">Demande d'intervention N°&nbsp;&nbsp;&nbsp;&nbsp;{{ $repair->di_number }}</span>
        </td>
        <td style="width:40%; text-align:right;">
            <span class="gras">Du&nbsp;&nbsp;&nbsp;&nbsp;{{ $repair->created_at->format('d/m/Y') }}</span>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     BLOC VÉHICULE + BLOC ATELIER (côte à côte)
     ══════════════════════════════════════════════════════════ --}}
<table style="margin-bottom:5px;">
    <tr>
        {{-- Identification du Véhicule (60%) --}}
        <td style="width:60%; padding:0; vertical-align:top;">
            <table>
                <tr>
                    <th colspan="4" style="font-style:italic; font-weight:bold; border-left:none; border-top:none; border-right:none; border-bottom:0.5px solid #000; padding:3px 5px; background-color:#f8f8f8;">
                        Identification du Véhicule
                    </th>
                </tr>
                <tr>
                    <td style="border-left:none; border-top:none; border-bottom:0.5px solid #000; border-right:0.5px solid #000; width:28%;">Immatriculation</td>
                    <td style="border-left:none; border-top:none; border-bottom:0.5px solid #000; border-right:0.5px solid #000; width:22%; font-weight:bold;">{{ $repair->vehicle->plate ?? '—' }}</td>
                    <td style="border-left:none; border-top:none; border-bottom:0.5px solid #000; border-right:0.5px solid #000; width:28%;">Index KM</td>
                    <td style="border-left:none; border-top:none; border-bottom:0.5px solid #000; border-right:none; width:22%;">{{ $repair->km_at_departure ? number_format($repair->km_at_departure, 0, ',', ' ') : '—' }}</td>
                </tr>
                <tr>
                    <td style="border-left:none; border-top:none; border-bottom:none; border-right:0.5px solid #000;">Marque/Model</td>
                    <td style="border-left:none; border-top:none; border-bottom:none; border-right:0.5px solid #000; font-weight:bold;">{{ trim(($repair->vehicle->brand ?? '') . ' ' . ($repair->vehicle->model ?? '')) ?: '—' }}</td>
                    <td style="border-left:none; border-top:none; border-bottom:none; border-right:0.5px solid #000;">Type</td>
                    <td style="border-left:none; border-top:none; border-bottom:none; border-right:none; font-weight:bold;">{{ $repair->vehicle_type_body ?? '—' }}</td>
                </tr>
            </table>
        </td>
        {{-- Atelier/Garage Mandaté (40%) --}}
        <td style="width:40%; padding:0; vertical-align:top;">
            <table>
                <tr>
                    <th colspan="2" style="font-style:italic; font-weight:bold; border-left:none; border-top:none; border-right:none; border-bottom:0.5px solid #000; padding:3px 5px; background-color:#f8f8f8;">
                        Atelier/Garage Mandaté
                    </th>
                </tr>
                <tr>
                    <td colspan="2" style="border-left:none; border-top:none; border-bottom:0.5px solid #000; border-right:none; text-align:center; font-weight:bold; font-size:12px; padding:8px 5px; vertical-align:middle;">
                        {{ $repair->garage->name ?? '—' }}
                    </td>
                </tr>
                <tr>
                    <td style="border-left:none; border-top:none; border-bottom:none; border-right:0.5px solid #000; width:50%;">Date d'entrée</td>
                    <td style="border-left:none; border-top:none; border-bottom:none; border-right:none; font-weight:bold;">{{ $repair->datetime_sent?->format('d/m/Y') ?? '—' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     TYPE D'INTERVENTION
     ══════════════════════════════════════════════════════════ --}}
<div class="titre-section">Type d'intervention</div>
<table style="margin-bottom:5px;">
    <tr>
        <th style="width:40%;" class="fond-gris">Type d'intervention</th>
        <th style="width:30%;" class="fond-gris">Réf. OR Initial</th>
        <th style="width:30%;" class="fond-gris">Durée d'immobilisation Initiale</th>
    </tr>
    <tr>
        <td class="gras">{{ $repairTypeLabels[$repair->repair_type] ?? $repair->repair_type }}</td>
        <td>{{ $repair->or_initial_reference ?? 'N/A' }}</td>
        <td>
            @if(!empty($repair->duree_immobilisation))
                {{ $repair->duree_immobilisation }}
            @elseif($repair->immobilization_days !== null)
                {{ $repair->immobilization_days }} jour{{ $repair->immobilization_days > 1 ? 's' : '' }}
            @else
                N/A
            @endif
        </td>
    </tr>
</table>

{{-- ══════════════════════════════════════════════════════════
     INVENTAIRE DES DYSFONCTIONNEMENTS
     ══════════════════════════════════════════════════════════ --}}
<div class="titre-section">Inventaire des dysfonctionnements</div>
<table style="margin-bottom:5px;">
    <tr>
        <th style="width:25%;" class="fond-gris">Code Intervention</th>
        <th class="fond-gris">Libellé</th>
    </tr>
    @foreach($faultCodes as $fc)
    <tr>
        <td style="height:22px;">{{ $fc->code }}</td>
        <td style="height:22px;">{{ $fc->label }}</td>
    </tr>
    @endforeach
    @for($i = $faultCodes->count(); $i < $minDysf; $i++)
    <tr>
        <td style="height:22px;">&nbsp;</td>
        <td style="height:22px;">&nbsp;</td>
    </tr>
    @endfor
</table>

{{-- ══════════════════════════════════════════════════════════
     DIAGNOSTIC GARAGE
     ══════════════════════════════════════════════════════════ --}}
<div class="titre-section">Diagnostic Garage</div>
<table>
    <tr>
        <th style="width:25%;" class="fond-gris">Code Intervention</th>
        <th class="fond-gris">Travaux réalisés</th>
    </tr>
    @foreach($faultCodes as $fc)
    <tr>
        <td style="height:60px; vertical-align:top; padding:3px 5px;">
            <div>{{ $fc->code }}</div>
            <div class="ligne-signature"></div>
        </td>
        <td style="height:60px; vertical-align:top; padding:3px 5px;">
            <div>{{ $fc->work_performed ?? '' }}</div>
            <div class="ligne-signature"></div>
        </td>
    </tr>
    @endforeach
    @for($i = $faultCodes->count(); $i < $minDiag; $i++)
    <tr>
        <td style="height:60px; vertical-align:top; padding:3px 5px;">
            <div class="ligne-signature"></div>
        </td>
        <td style="height:60px; vertical-align:top; padding:3px 5px;">
            <div class="ligne-signature"></div>
        </td>
    </tr>
    @endfor
</table>

</body>
</html>
