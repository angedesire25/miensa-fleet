@extends('layouts.dashboard')

@section('title', 'Garages')
@section('page-title', 'Garages agréés')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.stat-card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;padding:1.1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.stat-icon{width:42px;height:42px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-val{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;}
.stat-lbl{font-size:.75rem;color:#64748b;margin-top:.2rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .55rem;border-radius:99px;font-size:.7rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.filters-bar{display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;}
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
.garage-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem;padding:1.25rem;}
.garage-card{border:1.5px solid #e2e8f0;border-radius:.65rem;padding:1.1rem;transition:border-color .15s;}
.garage-card:hover{border-color:#10b981;}
.star{color:#f59e0b;}
.star.empty{color:#e2e8f0;}
.pagination-wrap{display:flex;justify-content:space-between;align-items:center;padding:.75rem 1.25rem;font-size:.82rem;color:#64748b;border-top:1px solid #f1f5f9;}
</style>

{{-- Statistiques --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="#3b82f6" stroke-width="1.8"/><polyline points="9,22 9,12 15,12 15,22" stroke="#3b82f6" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total garages</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['approuves'] }}</div><div class="stat-lbl">Approuvés</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#f59e0b" stroke-width="1.8"/><path d="M12 8v4M12 16h.01" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#f59e0b;">{{ $stats['en_attente'] }}</div><div class="stat-lbl">En attente</div></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Liste des garages</span>
        @can('garages.create')
        <a href="{{ route('garages.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Ajouter un garage
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
        <form method="GET" class="filters-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, ville, contact…" class="filter-input" style="min-width:200px;">
            <select name="type" class="filter-input">
                <option value="all">Tous types</option>
                <option value="general"     @selected(request('type')==='general')>Général</option>
                <option value="body_repair" @selected(request('type')==='body_repair')>Carrosserie</option>
                <option value="electrical"  @selected(request('type')==='electrical')>Électrique</option>
                <option value="tires"       @selected(request('type')==='tires')>Pneus</option>
                <option value="painting"    @selected(request('type')==='painting')>Peinture</option>
                <option value="glass"       @selected(request('type')==='glass')>Vitrage</option>
                <option value="specialized" @selected(request('type')==='specialized')>Spécialisé</option>
            </select>
            <select name="approved" class="filter-input">
                <option value="">Tous statuts</option>
                <option value="1" @selected(request('approved')==='1')>Approuvés</option>
                <option value="0" @selected(request('approved')==='0')>Non approuvés</option>
            </select>
            <button type="submit" class="btn btn-ghost">Filtrer</button>
            @if(request()->anyFilled(['q','type','approved']))
                <a href="{{ route('garages.index') }}" class="btn btn-ghost" style="color:#ef4444;">Effacer</a>
            @endif
        </form>
    </div>

    {{-- Grille de garages --}}
    <div class="garage-grid">
        @forelse($garages as $garage)
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
        <div class="garage-card">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.75rem;">
                <div>
                    <a href="{{ route('garages.show', $garage) }}" style="font-weight:700;color:#0f172a;text-decoration:none;font-size:.925rem;">{{ $garage->name }}</a>
                    <div style="font-size:.78rem;color:#64748b;margin-top:.1rem;">{{ $typeLabels[$garage->type] ?? $garage->type }}</div>
                </div>
                <span class="badge" style="{{ $garage->is_approved ? 'background:#f0fdf4;color:#166534;' : 'background:#fef3c7;color:#92400e;' }}">
                    {{ $garage->is_approved ? 'Approuvé' : 'En attente' }}
                </span>
            </div>

            @if($garage->city || $garage->address)
            <div style="font-size:.8rem;color:#64748b;margin-bottom:.5rem;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:.2rem;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="1.5"/></svg>
                {{ $garage->city }}@if($garage->address && $garage->city), @endif{{ $garage->address }}
            </div>
            @endif

            @if($garage->phone)
            <div style="font-size:.8rem;color:#64748b;margin-bottom:.5rem;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:.2rem;"><path d="M22 16.92v3a2 2 0 01-2.18 2A19.79 19.79 0 013.07 4.18 2 2 0 015.07 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L9.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="1.8"/></svg>
                {{ $garage->phone }}
            </div>
            @endif

            {{-- Note --}}
            @if($garage->rating)
            <div style="margin-bottom:.5rem;">
                @for($i=1;$i<=5;$i++)
                    <span class="{{ $i <= $garage->rating ? 'star' : 'star empty' }}">★</span>
                @endfor
            </div>
            @endif

            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.75rem;">
                <span style="font-size:.75rem;color:#94a3b8;">{{ $garage->repairs_count }} réparation(s)</span>
                <a href="{{ route('garages.show', $garage) }}" class="btn btn-ghost" style="padding:.3rem .65rem;font-size:.78rem;">Voir</a>
            </div>
        </div>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:3rem;color:#94a3b8;">Aucun garage trouvé.</div>
        @endforelse
    </div>

    @if($garages->hasPages())
    <div class="pagination-wrap">
        <span>{{ $garages->firstItem() }}–{{ $garages->lastItem() }} sur {{ $garages->total() }}</span>
        {{ $garages->links() }}
    </div>
    @endif
</div>
@endsection
