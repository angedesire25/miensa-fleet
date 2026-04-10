@extends('layouts.dashboard')
@section('title', 'Nettoyage #' . $cleaning->id)
@section('page-title', 'Détail du nettoyage')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.card-body{padding:1.25rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .65rem;border-radius:99px;font-size:.75rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;}
.btn-danger{background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.info-row{display:flex;align-items:flex-start;gap:.75rem;padding:.65rem 0;border-bottom:1px solid #f8fafc;}
.info-row:last-child{border-bottom:none;}
.info-label{width:170px;font-size:.8rem;font-weight:600;color:#64748b;flex-shrink:0;}
.info-val{font-size:.875rem;color:#0f172a;flex:1;}
.action-card{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #bbf7d0;border-radius:.75rem;padding:1.25rem;margin-bottom:1.25rem;}
.action-card.amber{background:linear-gradient(135deg,#fffbeb,#fef3c7);border-color:#fde68a;}
.action-card.blue{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#bfdbfe;}
.proof-img{width:100%;max-height:280px;object-fit:contain;border-radius:.5rem;background:#f8fafc;border:1px solid #e2e8f0;}
</style>

{{-- ── Barre d'état + actions rapides ─────────────────────────────────── --}}
@php $sc = $cleaning->getStatusColor(); @endphp
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <span class="badge" style="background:{{ $sc[1] }};color:{{ $sc[0] }};font-size:.8rem;padding:.3rem .85rem;">
            <span style="width:7px;height:7px;border-radius:50%;background:{{ $sc[0] }};display:inline-block;"></span>
            {{ $cleaning->getStatusLabel() }}
        </span>
        <span style="color:#94a3b8;font-size:.8rem;">Nettoyage #{{ $cleaning->id }}</span>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @can('cleanings.edit')
        @if(!in_array($cleaning->status, ['completed','cancelled']))
        <a href="{{ route('cleanings.edit', $cleaning) }}" class="btn btn-ghost">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2"/></svg>
            Modifier
        </a>
        @endif
        @endcan
        <a href="{{ route('cleanings.index') }}" class="btn btn-ghost">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Retour
        </a>
    </div>
</div>

{{-- ── Action : Confirmation chauffeur / responsable ──────────────────── --}}
@can('cleanings.confirm')
@if($cleaning->status === 'scheduled')
@php
    $isResponsible = (Auth::user()->driver && Auth::user()->driver->id === $cleaning->driver_id)
                  || Auth::id() === $cleaning->user_id;
@endphp
@if($isResponsible || Auth::user()->hasAnyRole(['super_admin','admin','fleet_manager','controller']))
<div class="action-card">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div>
            <div style="font-weight:700;color:#166534;font-size:.9rem;margin-bottom:.25rem;">
                🚿 Nettoyage à confirmer
            </div>
            <div style="font-size:.82rem;color:#15803d;">
                Cliquez sur "Je confirme" pour indiquer que vous avez pris en charge ce nettoyage.
            </div>
        </div>
        <form method="POST" action="{{ route('cleanings.confirm', $cleaning) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                Je confirme
            </button>
        </form>
    </div>
</div>
@endif
@endif
@endcan

{{-- ── Action : Marquer comme effectué ────────────────────────────────── --}}
@can('cleanings.edit')
@if(in_array($cleaning->status, ['scheduled', 'confirmed']))
<div class="action-card blue">
    <div style="font-weight:700;color:#1d4ed8;font-size:.9rem;margin-bottom:.75rem;">
        ✅ Marquer comme effectué
    </div>
    <form method="POST" action="{{ route('cleanings.complete', $cleaning) }}" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:end;">
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:.3rem;">Notes de complétion</label>
                <textarea name="completion_notes" rows="2" placeholder="Observations, remarques…"
                    style="width:100%;padding:.5rem .75rem;border:1.5px solid #bfdbfe;border-radius:.4rem;font-size:.82rem;resize:vertical;outline:none;box-sizing:border-box;"></textarea>
            </div>
            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;display:block;margin-bottom:.3rem;">Photo preuve</label>
                <input type="file" name="completion_proof" accept="image/*"
                    style="font-size:.78rem;border:1.5px solid #bfdbfe;border-radius:.4rem;padding:.4rem .5rem;background:#fff;">
            </div>
        </div>
        <div style="margin-top:.75rem;text-align:right;">
            <button type="submit" class="btn btn-primary">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                Marquer effectué
            </button>
        </div>
    </form>
</div>
@endif
@endcan

{{-- ── Informations principales ─────────────────────────────────────────  --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">

    {{-- Infos nettoyage --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Détails du nettoyage</span>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-label">Véhicule</div>
                <div class="info-val">
                    <a href="{{ route('vehicles.show', $cleaning->vehicle_id) }}" style="font-weight:700;color:#10b981;text-decoration:none;">
                        {{ $cleaning->vehicle?->plate }}
                    </a>
                    <span style="color:#64748b;"> — {{ $cleaning->vehicle?->brand }} {{ $cleaning->vehicle?->model }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Date planifiée</div>
                <div class="info-val" style="font-weight:600;">
                    {{ $cleaning->scheduled_date->translatedFormat('l d F Y') }}
                    @if($cleaning->scheduled_date->isToday())
                        <span class="badge" style="background:#fef3c7;color:#d97706;margin-left:.4rem;">Aujourd'hui</span>
                    @elseif($cleaning->scheduled_date->isPast() && $cleaning->status !== 'completed')
                        <span class="badge" style="background:#fef2f2;color:#ef4444;margin-left:.4rem;">Dépassé</span>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Heure</div>
                <div class="info-val" style="font-family:monospace;">{{ $cleaning->scheduled_time }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Type</div>
                <div class="info-val">
                    @php $tc=['exterior'=>['#3b82f6','#eff6ff'],'interior'=>['#8b5cf6','#faf5ff'],'full'=>['#10b981','#f0fdf4']][$cleaning->cleaning_type] ?? ['#64748b','#f8fafc']; @endphp
                    <span class="badge" style="background:{{ $tc[1] }};color:{{ $tc[0] }};">{{ $cleaning->getTypeLabel() }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Responsable</div>
                <div class="info-val">
                    <div style="font-weight:600;">{{ $cleaning->getResponsibleName() }}</div>
                    @if($cleaning->driver)
                        <span class="badge" style="background:#f0fdf4;color:#16a34a;">Chauffeur professionnel</span>
                    @elseif($cleaning->responsible)
                        <span class="badge" style="background:#f5f3ff;color:#7c3aed;">Collaborateur</span>
                    @endif
                </div>
            </div>
            @if($cleaning->notes)
            <div class="info-row">
                <div class="info-label">Instructions</div>
                <div class="info-val" style="font-style:italic;color:#64748b;">{{ $cleaning->notes }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Planifié par</div>
                <div class="info-val">{{ $cleaning->createdBy?->name ?? '—' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Créé le</div>
                <div class="info-val">{{ $cleaning->created_at->translatedFormat('d M Y à H:i') }}</div>
            </div>
        </div>
    </div>

    {{-- Suivi / Complétion --}}
    <div>
        {{-- Confirmation --}}
        @if($cleaning->confirmed_at)
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-head">
                <span class="card-title" style="color:#f59e0b;">Confirmation</span>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Confirmé le</div>
                    <div class="info-val">{{ $cleaning->confirmed_at->translatedFormat('d M Y à H:i') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Confirmé par</div>
                    <div class="info-val">{{ $cleaning->confirmedBy?->name ?? '—' }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Complétion --}}
        @if($cleaning->status === 'completed')
        <div class="card">
            <div class="card-head">
                <span class="card-title" style="color:#10b981;">Nettoyage effectué ✓</span>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Effectué le</div>
                    <div class="info-val" style="font-weight:600;">{{ $cleaning->completed_at?->translatedFormat('d M Y à H:i') ?? '—' }}</div>
                </div>
                @if($cleaning->completion_notes)
                <div class="info-row">
                    <div class="info-label">Observations</div>
                    <div class="info-val">{{ $cleaning->completion_notes }}</div>
                </div>
                @endif
                @if($cleaning->completion_proof)
                <div style="margin-top:.75rem;">
                    <div style="font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:.4rem;">Photo de preuve</div>
                    <img src="{{ Storage::url($cleaning->completion_proof) }}" class="proof-img" alt="Preuve de nettoyage">
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Actions gestionnaire --}}
        @can('cleanings.edit')
        @if(in_array($cleaning->status, ['scheduled', 'confirmed']))
        <div class="card">
            <div class="card-head"><span class="card-title">Actions</span></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">
                <form method="POST" action="{{ route('cleanings.missed', $cleaning) }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;color:#d97706;"
                        onclick="return confirm('Marquer ce nettoyage comme manqué ?')">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/></svg>
                        Marquer comme manqué
                    </button>
                </form>
                <form method="POST" action="{{ route('cleanings.cancel', $cleaning) }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;color:#ef4444;"
                        onclick="return confirm('Annuler ce nettoyage ?')">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Annuler le nettoyage
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endcan
    </div>
</div>
@endsection
