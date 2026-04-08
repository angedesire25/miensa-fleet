@extends('layouts.dashboard')

@section('title', $garage->name)
@section('page-title', 'Garage — ' . $garage->name)

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.stat-mini{text-align:center;padding:.85rem;}
.stat-mini .val{font-size:1.4rem;font-weight:800;color:#0f172a;}
.stat-mini .lbl{font-size:.72rem;color:#64748b;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;}
td{padding:.7rem 1rem;border-bottom:1px solid #f8fafc;font-size:.855rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
.star{color:#f59e0b;}
.star.empty{color:#e2e8f0;}
.spec-tag{display:inline-block;padding:.2rem .55rem;border-radius:99px;background:#f0fdf4;color:#166534;font-size:.72rem;font-weight:600;margin:.15rem;}
</style>

@php
$typeLabels = [
    'general'     => 'Général',
    'body_repair' => 'Carrosserie',
    'electrical'  => 'Électrique',
    'tires'       => 'Pneus',
    'painting'    => 'Peinture',
    'glass'       => 'Vitrage',
    'specialized' => 'Spécialisé',
];
@endphp

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <span class="badge" style="{{ $garage->is_approved ? 'background:#f0fdf4;color:#166534;' : 'background:#fef3c7;color:#92400e;' }}">
            {{ $garage->is_approved ? 'Approuvé' : 'En attente d\'approbation' }}
        </span>
        <span style="font-size:.8rem;color:#94a3b8;">{{ $typeLabels[$garage->type] ?? $garage->type }}</span>
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <a href="{{ route('garages.index') }}" class="btn btn-ghost">← Retour</a>
        @can('garages.edit')
            <form method="POST" action="{{ route('garages.toggle-approved', $garage) }}">
                @csrf
                <button type="submit" class="btn btn-ghost">
                    {{ $garage->is_approved ? 'Retirer l\'approbation' : 'Approuver' }}
                </button>
            </form>
            <a href="{{ route('garages.edit', $garage) }}" class="btn btn-primary">Modifier</a>
        @endcan
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;">
    {{-- Colonne principale --}}
    <div>
        {{-- Informations --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Informations</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;padding:1.25rem;">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Contact</div>
                    <div style="font-size:.875rem;color:#0f172a;">{{ $garage->contact_person ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Téléphone</div>
                    <div style="font-size:.875rem;color:#0f172a;">{{ $garage->phone ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Email</div>
                    <div style="font-size:.875rem;color:#0f172a;">{{ $garage->email ?? '—' }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Adresse</div>
                    <div style="font-size:.875rem;color:#0f172a;">
                        @if($garage->city || $garage->address)
                            {{ $garage->address }}@if($garage->address && $garage->city), @endif{{ $garage->city }}
                        @else —
                        @endif
                    </div>
                </div>
                @if($garage->rating)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Note</div>
                    <div>
                        @for($i=1;$i<=5;$i++)
                            <span class="{{ $i <= $garage->rating ? 'star' : 'star empty' }}" style="font-size:1.1rem;">★</span>
                        @endfor
                        <span style="font-size:.8rem;color:#64748b;">({{ $garage->rating }}/5)</span>
                    </div>
                </div>
                @endif
                @if($garage->specializations)
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Spécialisations</div>
                    <div>
                        @foreach($garage->specializations as $spec)
                            <span class="spec-tag">{{ ucfirst($spec) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @if($garage->notes)
            <div style="padding:0 1.25rem 1.25rem;">
                <div style="font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem;">Notes</div>
                <p style="font-size:.855rem;color:#374151;margin:0;white-space:pre-wrap;">{{ $garage->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Dernières réparations --}}
        <div class="card">
            <div class="card-head">
                <span class="card-title">Dernières réparations</span>
                <a href="{{ route('repairs.index', ['garage_id' => $garage->id]) }}" class="btn btn-ghost" style="padding:.3rem .65rem;font-size:.78rem;">
                    Voir tout ({{ $repairStats['total'] }})
                </a>
            </div>
            @if($garage->repairs->isEmpty())
                <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:.855rem;">Aucune réparation pour ce garage.</div>
            @else
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Véhicule</th>
                            <th>Sinistre</th>
                            <th>Envoyé le</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($garage->repairs as $repair)
                        @php
                            $rStatusColors = [
                                'sent'               => ['#eff6ff','#1e40af'],
                                'diagnosing'         => ['#fef3c7','#92400e'],
                                'repairing'          => ['#fff7ed','#9a3412'],
                                'waiting_parts'      => ['#ede9fe','#5b21b6'],
                                'completed'          => ['#f0fdf4','#166534'],
                                'returned'           => ['#f0fdf4','#166534'],
                                'returned_with_issue'=> ['#fee2e2','#991b1b'],
                            ];
                            $rStatusLabels = [
                                'sent'               => 'Envoyé',
                                'diagnosing'         => 'Diagnostic',
                                'repairing'          => 'En réparation',
                                'waiting_parts'      => 'Attente pièces',
                                'completed'          => 'Terminé',
                                'returned'           => 'Retourné',
                                'returned_with_issue'=> 'Retour problème',
                            ];
                            [$rBg,$rFg] = $rStatusColors[$repair->status] ?? ['#f8fafc','#64748b'];
                        @endphp
                        <tr>
                            <td style="font-weight:600;color:#64748b;">
                                <a href="{{ route('repairs.show', $repair) }}" style="color:#10b981;text-decoration:none;">#{{ $repair->id }}</a>
                            </td>
                            <td>{{ $repair->vehicle?->plate ?? '—' }}</td>
                            <td>
                                @if($repair->incident)
                                    <a href="{{ route('incidents.show', $repair->incident) }}" style="color:#10b981;text-decoration:none;">#{{ $repair->incident_id }}</a>
                                @else —
                                @endif
                            </td>
                            <td style="font-size:.8rem;white-space:nowrap;">{{ $repair->datetime_sent?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge" style="background:{{ $rBg }};color:{{ $rFg }};">{{ $rStatusLabels[$repair->status] ?? $repair->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Colonne latérale --}}
    <div>
        {{-- Stats réparations --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Statistiques</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;border-top:1px solid #f1f5f9;">
                <div class="stat-mini" style="border-right:1px solid #f1f5f9;">
                    <div class="val">{{ $repairStats['total'] }}</div>
                    <div class="lbl">Total</div>
                </div>
                <div class="stat-mini" style="border-right:1px solid #f1f5f9;">
                    <div class="val" style="color:#f59e0b;">{{ $repairStats['en_cours'] }}</div>
                    <div class="lbl">En cours</div>
                </div>
                <div class="stat-mini">
                    <div class="val" style="color:#10b981;">{{ $repairStats['terminees'] }}</div>
                    <div class="lbl">Terminées</div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        @can('garages.delete')
        <div class="card">
            <div class="card-head"><span class="card-title">Danger</span></div>
            <div style="padding:1.25rem;">
                <form method="POST" action="{{ route('garages.destroy', $garage) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn"
                            style="width:100%;justify-content:center;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;"
                            data-confirm="Supprimer le garage {{ $garage->name }} ? Cette action est irréversible.">
                        Supprimer le garage
                    </button>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
