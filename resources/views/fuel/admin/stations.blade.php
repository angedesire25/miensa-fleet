@extends('layouts.dashboard')

@section('title', 'Stations carburant')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Stations')

@section('content')
<div class="page-content">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Stations carburant</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.2rem 0 0;">Gérez les stations référencées dans la flotte.</p>
        </div>
        <div style="display:flex;gap:.6rem;">
            <a href="{{ route('fuel.admin.dashboard') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#374151;font-size:.85rem;text-decoration:none;">
                ← Tableau de bord
            </a>
            <a href="{{ route('fuel.admin.station-create') }}"
               style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:.45rem;font-size:.85rem;font-weight:600;text-decoration:none;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Ajouter une station
            </a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.65rem;padding:.85rem 1rem;margin-bottom:1.25rem;color:#166534;font-size:.875rem;">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filtres --}}
    <form method="GET" style="display:flex;gap:.6rem;margin-bottom:1.25rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, ville, marque…"
               style="padding:.45rem .75rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.85rem;color:#374151;min-width:200px;">
        <button type="submit"
                style="padding:.45rem .9rem;background:#0f172a;color:#fff;border:none;border-radius:.45rem;font-size:.85rem;cursor:pointer;">
            Rechercher
        </button>
    </form>

    {{-- Grille --}}
    @if($stations->isEmpty())
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:3rem;text-align:center;">
        <p style="color:#94a3b8;">Aucune station référencée.</p>
        <a href="{{ route('fuel.admin.station-create') }}" style="display:inline-block;margin-top:.75rem;padding:.5rem 1rem;background:#10b981;color:#fff;border-radius:.45rem;text-decoration:none;font-size:.85rem;font-weight:600;">
            Ajouter la première station
        </a>
    </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
        @foreach($stations as $station)
        <div style="background:#fff;border:1px solid {{ $station->is_active ? '#e2e8f0' : '#fecaca' }};border-radius:.75rem;padding:1.25rem;position:relative;">
            {{-- Statut --}}
            <span style="position:absolute;top:.75rem;right:.75rem;padding:.15rem .55rem;border-radius:99px;font-size:.7rem;font-weight:600;
                         color:{{ $station->is_active ? '#059669' : '#dc2626' }};
                         background:{{ $station->is_active ? 'rgba(5,150,105,.1)' : 'rgba(220,38,38,.1)' }};">
                {{ $station->is_active ? 'Active' : 'Inactive' }}
            </span>

            <div style="margin-bottom:.75rem;">
                <div style="font-size:.95rem;font-weight:700;color:#0f172a;padding-right:60px;">{{ $station->name }}</div>
                @if($station->brand)
                <div style="font-size:.78rem;color:#10b981;font-weight:600;">{{ $station->brand }}</div>
                @endif
            </div>

            @if($station->city || $station->address)
            <div style="font-size:.8rem;color:#64748b;margin-bottom:.5rem;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:.3rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                {{ collect([$station->city, $station->address])->filter()->implode(' — ') }}
            </div>
            @endif

            @if($station->phone)
            <div style="font-size:.8rem;color:#64748b;margin-bottom:.5rem;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:.3rem;"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.001 2.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="1.8"/></svg>
                {{ $station->phone }}
            </div>
            @endif

            {{-- Types carburant --}}
            @if($station->fuel_types)
            <div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.75rem;">
                @foreach($station->fuel_types as $ft)
                <span style="padding:.15rem .5rem;background:#f1f5f9;border-radius:99px;font-size:.7rem;color:#374151;font-weight:500;">
                    {{ ['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'][$ft] ?? $ft }}
                </span>
                @endforeach
            </div>
            @endif

            {{-- Stats --}}
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.75rem;">
                {{ $station->fuel_transactions_count }} transaction(s)
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:.5rem;">
                <a href="{{ route('fuel.admin.station-edit', $station) }}"
                   style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.3rem;padding:.4rem;background:#f1f5f9;color:#374151;border-radius:.35rem;font-size:.78rem;text-decoration:none;font-weight:500;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.8"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8"/></svg>
                    Modifier
                </a>
                <form method="POST" action="{{ route('fuel.admin.station-destroy', $station) }}" onsubmit="return confirmDelete(event, '{{ $station->name }}')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="display:inline-flex;align-items:center;justify-content:center;padding:.4rem .6rem;background:#fff;border:1px solid #fecaca;color:#ef4444;border-radius:.35rem;font-size:.78rem;cursor:pointer;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="1.8"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="1.8"/><path d="M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="1.8"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    @if($stations->hasPages())
    <div style="margin-top:1rem;">{{ $stations->links() }}</div>
    @endif
    @endif

</div>

<script>
function confirmDelete(e, name) {
    e.preventDefault();
    const form = e.target;
    Swal.fire({
        title: 'Supprimer la station ?',
        text: `"${name}" sera archivée.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#ef4444',
    }).then(r => { if (r.isConfirmed) form.submit(); });
    return false;
}
</script>
@endsection
