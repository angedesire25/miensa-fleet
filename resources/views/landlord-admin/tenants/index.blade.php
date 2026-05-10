@extends('landlord-admin.layouts.app')
@section('page-title', 'Tenants')

@push('styles')
<style>
    .filter-bar { display:flex; gap:.75rem; align-items:center; margin-bottom:1.5rem; }
    .filter-input { background:#1e293b; border:1px solid rgba(255,255,255,.1); border-radius:8px; color:#f1f5f9; padding:.55rem 1rem; font-size:.87rem; outline:none; font-family:inherit; }
    .filter-input:focus { border-color:#3b82f6; }
    .filter-select { background:#1e293b; border:1px solid rgba(255,255,255,.1); border-radius:8px; color:#94a3b8; padding:.55rem 1rem; font-size:.87rem; outline:none; font-family:inherit; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#f1f5f9;">
        {{ $tenants->total() }} tenant{{ $tenants->total() > 1 ? 's' : '' }}
    </h2>
    <a href="{{ route('admin.tenants.create') }}" class="btn-sm btn-primary" style="padding:.45rem 1rem;font-size:.85rem;">
        + Créer une société
    </a>
</div>

{{-- Filtres --}}
<form method="GET" class="filter-bar">
    <input type="text" name="q" class="filter-input" placeholder="Rechercher…" value="{{ request('q') }}" style="flex:1;max-width:320px;">
    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
        <option value="trial"     {{ request('status') === 'trial'     ? 'selected' : '' }}>Essai</option>
        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Résilié</option>
    </select>
    <button type="submit" class="btn-sm btn-primary">Filtrer</button>
</form>

<div class="a-card" style="padding:0;overflow:hidden;">
    <table class="a-table">
        <thead>
            <tr>
                <th>Société</th>
                <th>Domaine</th>
                <th>Plan</th>
                <th>Statut</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
            @php $statusMap = ['active'=>'badge-green','trial'=>'badge-yellow','suspended'=>'badge-red','cancelled'=>'badge-slate']; @endphp
            <tr>
                <td>
                    <div style="font-weight:500;color:#f1f5f9;">{{ $tenant->name }}</div>
                    <div style="font-size:.75rem;color:#475569;">{{ $tenant->contact_email ?? '—' }}</div>
                </td>
                <td style="font-size:.8rem;font-family:monospace;color:#64748b;">{{ $tenant->domain }}</td>
                <td>{{ $tenant->plan?->name ?? '—' }}</td>
                <td><span class="badge {{ $statusMap[$tenant->status] ?? 'badge-slate' }}">{{ $tenant->status }}</span></td>
                <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn-sm btn-slate">Voir</a>
                        <a href="{{ route('admin.tenants.impersonate', $tenant) }}" class="btn-sm btn-slate" target="_blank" title="Accéder au panel">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                        @if(in_array($tenant->status, ['suspended', 'trial']))
                            <form id="activate-form-{{ $tenant->id }}" method="POST" action="{{ route('admin.tenants.activate', $tenant) }}">
                                @csrf
                            </form>
                            <button type="button" class="btn-sm btn-green"
                                    onclick="confirmActivate({{ $tenant->id }}, '{{ addslashes($tenant->name) }}', '{{ $tenant->status }}')">
                                {{ $tenant->status === 'trial' ? 'Valider' : 'Activer' }}
                            </button>
                        @endif
                        @if(!in_array($tenant->status, ['suspended', 'cancelled']))
                            <form id="suspend-form-{{ $tenant->id }}" method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}">
                                @csrf
                                <input type="hidden" name="reason" id="suspend-reason-{{ $tenant->id }}">
                            </form>
                            <button type="button" class="btn-sm btn-red"
                                    onclick="confirmSuspend({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')">
                                Suspendre
                            </button>
                        @endif
                        @if($tenant->status !== 'cancelled')
                            <form id="delete-form-{{ $tenant->id }}" method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm_slug" id="delete-slug-{{ $tenant->id }}">
                                <input type="hidden" name="drop_database" id="delete-drop-{{ $tenant->id }}" value="0">
                            </form>
                            <button type="button" class="btn-sm" style="background:rgba(239,68,68,.1);color:#fca5a5;border:1px solid rgba(239,68,68,.2);"
                                    onclick="confirmDelete({{ $tenant->id }}, '{{ addslashes($tenant->name) }}', '{{ $tenant->slug }}', '{{ $tenant->database }}')">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:#475569;">Aucun tenant trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tenants->hasPages())
<div style="margin-top:1rem;display:flex;justify-content:flex-end;">
    {{ $tenants->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
function confirmActivate(id, name, status) {
    const label = status === 'trial' ? 'Valider l\'abonnement' : 'Réactiver';
    const text  = status === 'trial'
        ? `Le compte passera de trial à actif et <strong>${name}</strong> sera facturé.`
        : `Le compte de <strong>${name}</strong> sera réactivé.`;
    Swal.fire({
        title: label + ' ?',
        html: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonText: 'Annuler',
        confirmButtonText: label,
    }).then(r => { if (r.isConfirmed) document.getElementById('activate-form-' + id).submit(); });
}

function confirmSuspend(id, name) {
    Swal.fire({
        title: `Suspendre ${name} ?`,
        html: `Les utilisateurs ne pourront plus se connecter. Saisissez le motif&nbsp;:`,
        input: 'textarea',
        inputPlaceholder: 'Motif de la suspension…',
        inputAttributes: { maxlength: 500, rows: 3 },
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Suspendre',
        preConfirm: (val) => {
            if (!val || !val.trim()) {
                Swal.showValidationMessage('Le motif de suspension est obligatoire.');
                return false;
            }
            return val.trim();
        },
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('suspend-reason-' + id).value = r.value;
            document.getElementById('suspend-form-' + id).submit();
        }
    });
}

function confirmDelete(id, name, slug, dbName) {
    Swal.fire({
        title: 'Supprimer définitivement ?',
        html: `
            <p style="color:#94a3b8;margin-bottom:1rem;">
                Tapez <code style="background:#0f172a;padding:.15rem .4rem;border-radius:4px;color:#fde047;font-family:monospace;">${slug}</code>
                pour confirmer la suppression de <strong style="color:#f1f5f9;">${name}</strong>.
            </p>
            <input id="swal-slug-input" class="swal2-input" placeholder="${slug}" autocomplete="off">
            <label style="display:flex;align-items:center;gap:.5rem;margin-top:1rem;cursor:pointer;font-size:.85rem;color:#fca5a5;">
                <input type="checkbox" id="swal-drop-db" style="accent-color:#ef4444;">
                Supprimer aussi la base de données <code style="font-size:.78rem;">${dbName}</code>
            </label>
            <p style="font-size:.75rem;color:#475569;margin-top:.5rem;">
                ⚠ La suppression de la base est irréversible et efface toutes les données client.
            </p>`,
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Supprimer définitivement',
        focusConfirm: false,
        preConfirm: () => {
            const typed = document.getElementById('swal-slug-input').value;
            const drop  = document.getElementById('swal-drop-db').checked;
            if (typed !== slug) {
                Swal.showValidationMessage('Le sous-domaine saisi ne correspond pas.');
                return false;
            }
            return { slug: typed, drop };
        },
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delete-slug-' + id).value = r.value.slug;
            document.getElementById('delete-drop-' + id).value = r.value.drop ? '1' : '0';
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush
