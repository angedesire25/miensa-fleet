@extends('landlord-admin.layouts.app')

@section('title', 'Promotions')
@section('page-title', 'Promotions')
@section('breadcrumb', $tab === 'archives' ? 'Archives' : 'Liste')

@push('styles')
<style>
    .tabs { display:flex; gap:0; border-bottom:1px solid rgba(255,255,255,.07); margin-bottom:1.5rem; }
    .tab-btn {
        display: flex; align-items: center; gap: .45rem;
        padding: .6rem 1.25rem; font-size: .85rem; font-weight: 600;
        color: #64748b; text-decoration: none; border-bottom: 2px solid transparent;
        margin-bottom: -1px; transition: color .15s, border-color .15s;
    }
    .tab-btn:hover { color: #e2e8f0; }
    .tab-btn.active { color: #f1f5f9; border-bottom-color: #3b82f6; }
    .tab-count {
        font-size: .7rem; font-weight: 700; padding: .1rem .45rem;
        border-radius: 10px; background: rgba(255,255,255,.08);
    }
    .tab-btn.active .tab-count { background: rgba(59,130,246,.2); color: #93c5fd; }
</style>
@endpush

@section('content')

{{-- ── En-tête ──────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
    <p style="color:#64748b;font-size:.875rem;margin:0;">
        @if($tab === 'archives')
            Promotions archivées — plus affichées sur le site, conservées pour historique.
        @else
            Les promotions actives s'affichent sur la page tarifaire avec le prix barré et le prix réduit.
        @endif
    </p>
    @if($tab !== 'archives')
    <a href="{{ route('admin.promotions.create') }}"
       style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;background:#3b82f6;border-radius:8px;font-size:.83rem;font-weight:600;color:white;text-decoration:none;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle promotion
    </a>
    @endif
</div>

{{-- ── Onglets ──────────────────────────────────────────────────────── --}}
<div class="tabs">
    <a href="{{ route('admin.promotions.index') }}"
       class="tab-btn {{ $tab === 'actives' ? 'active' : '' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        Actives & à venir
        <span class="tab-count">{{ $countActives }}</span>
    </a>
    <a href="{{ route('admin.promotions.index', ['tab' => 'archives']) }}"
       class="tab-btn {{ $tab === 'archives' ? 'active' : '' }}">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
        Archives
        <span class="tab-count">{{ $countArchives }}</span>
    </a>
</div>

{{-- ── Tableau ──────────────────────────────────────────────────────── --}}
@if($promotions->isEmpty())
<div style="background:#1e293b;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:3rem;text-align:center;">
    <div style="font-size:2rem;margin-bottom:.75rem;">{{ $tab === 'archives' ? '📦' : '🏷️' }}</div>
    <div style="color:#64748b;font-size:.9rem;">
        {{ $tab === 'archives' ? 'Aucune promotion archivée.' : 'Aucune promotion créée pour l\'instant.' }}
    </div>
    @if($tab !== 'archives')
    <a href="{{ route('admin.promotions.create') }}"
       style="display:inline-block;margin-top:1rem;padding:.5rem 1.25rem;background:rgba(59,130,246,.12);color:#93c5fd;border-radius:8px;font-size:.83rem;font-weight:600;text-decoration:none;">
        Créer la première promotion
    </a>
    @endif
</div>
@else

<div style="background:#1e293b;border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden;">
    <table class="a-table">
        <thead>
            <tr>
                <th>Promotion</th>
                <th>Remise</th>
                <th>Plan ciblé</th>
                <th>Validité</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($promotions as $promo)
            @php
                $now    = now();
                $live   = !$promo->isArchived()
                    && $promo->is_active
                    && ($promo->starts_at === null || $promo->starts_at <= $now)
                    && ($promo->ends_at   === null || $promo->ends_at   >= $now);
            @endphp
            <tr style="{{ $promo->isArchived() ? 'opacity:.6;' : '' }}">
                <td>
                    <div style="font-weight:600;color:#f1f5f9;display:flex;align-items:center;gap:.5rem;">
                        {{ $promo->label }}
                        @if($promo->isArchived())
                        <span style="font-size:.68rem;background:rgba(100,116,139,.2);color:#64748b;padding:.1rem .45rem;border-radius:6px;font-weight:600;">archivée</span>
                        @endif
                    </div>
                    @if($promo->badge_text)
                    <span style="display:inline-block;margin-top:.25rem;background:rgba(251,191,36,.15);color:#fbbf24;font-size:.7rem;font-weight:700;padding:.1rem .5rem;border-radius:8px;">
                        {{ $promo->badge_text }}
                    </span>
                    @endif
                    @if($promo->description)
                    <div style="font-size:.78rem;color:#64748b;margin-top:.2rem;">{{ Str::limit($promo->description, 60) }}</div>
                    @endif
                    @if($promo->isArchived())
                    <div style="font-size:.73rem;color:#475569;margin-top:.2rem;">
                        Archivée le {{ $promo->archived_at->format('d/m/Y à H\hi') }}
                    </div>
                    @endif
                </td>
                <td style="color:#86efac;font-weight:700;">
                    {{ $promo->discountLabel() }}
                    @if($promo->billing_period !== 'all')
                    <div style="font-size:.73rem;color:#64748b;font-weight:400;">
                        {{ $promo->billing_period === 'monthly' ? 'Sur mensuel uniquement' : 'Sur annuel uniquement' }}
                    </div>
                    @endif
                </td>
                <td>
                    @if($promo->plan)
                        <span style="background:rgba(59,130,246,.12);color:#93c5fd;font-size:.78rem;padding:.2rem .55rem;border-radius:6px;font-weight:600;">
                            {{ $promo->plan->name }}
                        </span>
                    @else
                        <span style="color:#64748b;font-size:.82rem;">Tous les plans</span>
                    @endif
                </td>
                <td style="font-size:.8rem;color:#94a3b8;">
                    @if($promo->starts_at || $promo->ends_at)
                        @if($promo->starts_at)<div>Du {{ $promo->starts_at->format('d/m/Y') }}</div>@endif
                        @if($promo->ends_at)
                        <div>Au {{ $promo->ends_at->format('d/m/Y') }}</div>
                        @else
                        <div style="color:#64748b;">Sans limite</div>
                        @endif
                    @else
                        <span style="color:#64748b;">Toujours valide</span>
                    @endif
                </td>
                <td>
                    @if($promo->isArchived())
                        <span class="badge badge-slate">Archivée</span>
                    @elseif($live)
                        <span class="badge badge-green">En cours</span>
                    @elseif($promo->is_active)
                        <span class="badge badge-yellow">Hors période</span>
                    @else
                        <span class="badge badge-slate">Désactivée</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;align-items:center;">
                        @if($promo->isArchived())
                            {{-- Restaurer --}}
                            <form method="POST" action="{{ route('admin.promotions.unarchive', $promo) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-sm btn-green" title="Restaurer la promotion">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.85"/></svg>
                                    Restaurer
                                </button>
                            </form>
                            {{-- Supprimer définitivement --}}
                            <form method="POST" action="{{ route('admin.promotions.destroy', $promo) }}" style="display:inline;"
                                  onsubmit="return confirm('Supprimer définitivement cette promotion ? Cette action est irréversible.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-red" title="Supprimer définitivement">🗑</button>
                            </form>
                        @else
                            {{-- Modifier --}}
                            <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn-sm btn-slate" title="Modifier">✏️</a>
                            {{-- Activer / Désactiver --}}
                            <form method="POST" action="{{ route('admin.promotions.toggle', $promo) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-sm {{ $promo->is_active ? 'btn-slate' : 'btn-green' }}"
                                        title="{{ $promo->is_active ? 'Désactiver' : 'Activer' }}">
                                    {{ $promo->is_active ? '⏸' : '▶️' }}
                                </button>
                            </form>
                            {{-- Archiver --}}
                            <form method="POST" action="{{ route('admin.promotions.archive', $promo) }}" style="display:inline;"
                                  onsubmit="return confirm('Archiver cette promotion ? Elle ne sera plus visible sur le site mais restera consultable dans les archives.')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-sm btn-slate" title="Archiver">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
