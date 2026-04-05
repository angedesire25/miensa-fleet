@extends('layouts.dashboard')

@section('title', 'Utilisateurs')
@section('page-title', 'Administration — Utilisateurs')

@section('content')
<style>
.stat-row { display:grid; grid-template-columns:repeat(4,1fr); gap:.85rem; margin-bottom:1.5rem; }
.stat-mini { background:#fff; border-radius:.65rem; border:1px solid #e2e8f0; padding:1rem 1.25rem; display:flex; align-items:center; gap:.85rem; }
.stat-mini-icon { width:40px; height:40px; border-radius:.5rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.stat-mini-val { font-size:1.5rem; font-weight:700; color:#0f172a; line-height:1; }
.stat-mini-lbl { font-size:.75rem; color:#64748b; margin-top:.15rem; }

.card { background:#fff; border-radius:.75rem; border:1px solid #e2e8f0; overflow:hidden; }
.card-head { padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }

.filter-form { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.filter-input { padding:.5rem .85rem; border:1.5px solid #e2e8f0; border-radius:.4rem; font-size:.825rem; color:#374151; outline:none; transition:border-color .15s; }
.filter-input:focus { border-color:#10b981; }
.filter-select { padding:.5rem .85rem; border:1.5px solid #e2e8f0; border-radius:.4rem; font-size:.825rem; color:#374151; background:#fff; outline:none; cursor:pointer; }

.btn { padding:.5rem 1rem; border-radius:.4rem; font-size:.825rem; font-weight:600; border:none; cursor:pointer; transition:opacity .15s; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; }
.btn-primary { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.btn-primary:hover { opacity:.9; }
.btn-ghost { background:#f8fafc; color:#374151; border:1px solid #e2e8f0; }
.btn-ghost:hover { background:#f1f5f9; }
.btn-danger { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.btn-danger:hover { background:#fee2e2; }
.btn-sm { padding:.35rem .7rem; font-size:.775rem; }

.table { width:100%; border-collapse:collapse; }
.table th { padding:.65rem 1rem; text-align:left; font-size:.72rem; font-weight:600; color:#94a3b8; letter-spacing:.06em; text-transform:uppercase; background:#f8fafc; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
.table td { padding:.8rem 1rem; font-size:.85rem; color:#374151; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.table tr:last-child td { border-bottom:none; }
.table tr:hover td { background:#fafafa; }

.badge { display:inline-flex; align-items:center; gap:.25rem; padding:.18rem .55rem; border-radius:99px; font-size:.7rem; font-weight:600; }
.badge-green  { background:#f0fdf4; color:#16a34a; }
.badge-red    { background:#fef2f2; color:#dc2626; }
.badge-gray   { background:#f1f5f9; color:#64748b; }
.badge-orange { background:#fffbeb; color:#d97706; }

.user-avatar { width:34px; height:34px; border-radius:50%; object-fit:cover; }
.user-initials { width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg,#10b981,#059669); display:flex; align-items:center; justify-content:center; color:#fff; font-size:.75rem; font-weight:700; flex-shrink:0; }
.user-cell { display:flex; align-items:center; gap:.7rem; }

.alert-flash { padding:.75rem 1rem; border-radius:.45rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:.6rem; font-size:.875rem; }
.alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }

.pagination-wrap { padding:.85rem 1.25rem; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
.pagination-info { font-size:.8rem; color:#94a3b8; }
</style>

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
    <div>
        <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Gestion des utilisateurs</h1>
        <p style="color:#64748b;font-size:.85rem;margin:.15rem 0 0;">Création, modification et contrôle des accès</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
        Nouvel utilisateur
    </a>
</div>

@if(session('new_password'))
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.75rem;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#d97706" stroke-width="1.8"/></svg>
        <div>
            <p style="font-weight:700;color:#92400e;margin:0 0 .25rem;">Nouveau mot de passe généré pour {{ session('new_password_user') }}</p>
            <p style="margin:0;color:#78350f;font-size:.875rem;">Communiquez ce mot de passe à l'utilisateur : <strong style="font-family:monospace;font-size:1rem;letter-spacing:.05em;">{{ session('new_password') }}</strong></p>
            <p style="margin:.3rem 0 0;color:#92400e;font-size:.78rem;">⚠ Ce mot de passe n'est affiché qu'une seule fois.</p>
        </div>
    </div>
@endif

{{-- Stats rapides --}}
<div class="stat-row">
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#eff6ff;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="#2563eb" stroke-width="1.8"/><circle cx="9" cy="7" r="4" stroke="#2563eb" stroke-width="1.8"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="#2563eb" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-mini-val">{{ $stats['total'] }}</div><div class="stat-mini-lbl">Total comptes</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#f0fdf4;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#16a34a" stroke-width="1.8"/><path d="M9 12l2 2 4-4" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-mini-val" style="color:#16a34a;">{{ $stats['actifs'] }}</div><div class="stat-mini-lbl">Actifs</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fffbeb;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#d97706" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-mini-val" style="color:#d97706;">{{ $stats['suspendus'] }}</div><div class="stat-mini-lbl">Suspendus</div></div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon" style="background:#fef2f2;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="#dc2626" stroke-width="1.8"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="#dc2626" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-mini-val" style="color:#dc2626;">{{ $stats['supprimes'] }}</div><div class="stat-mini-lbl">Archivés</div></div>
    </div>
</div>

{{-- Tableau principal --}}
<div class="card">
    <div class="card-head">
        {{-- Filtres --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-form">
            <div style="position:relative;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:#9ca3af;"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher…" class="filter-input" style="padding-left:2rem;width:200px;">
            </div>
            <select name="role" class="filter-select">
                <option value="">Tous les rôles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ ['super_admin'=>'Super Admin','admin'=>'Admin','fleet_manager'=>'Resp. Flotte','controller'=>'Contrôleur','director'=>'Directeur','collaborator'=>'Collaborateur','driver_user'=>'Chauffeur'][$role->name] ?? $role->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="filter-select">
                <option value="">Tous statuts</option>
                <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>Actif</option>
                <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspendu</option>
            </select>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#64748b;cursor:pointer;">
                <input type="checkbox" name="avec_supprimes" value="1" {{ request('avec_supprimes') ? 'checked' : '' }} style="accent-color:#10b981;">
                Inclure archivés
            </label>
            <button type="submit" class="btn btn-ghost btn-sm">Filtrer</button>
            @if(request()->hasAny(['q','role','status','avec_supprimes']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm" style="color:#94a3b8;">✕ Réinitialiser</a>
            @endif
        </form>

        <div style="font-size:.8rem;color:#94a3b8;">{{ $users->total() }} résultat(s)</div>
    </div>

    @if($users->isEmpty())
        <div style="padding:3rem;text-align:center;color:#94a3b8;">
            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" style="margin:0 auto .75rem;display:block;opacity:.4;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.5"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.5"/></svg>
            Aucun utilisateur trouvé.
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Service</th>
                    <th>Statut</th>
                    <th>Dernière connexion</th>
                    <th>Créé le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $roleColors = ['super_admin'=>['#B91C1C','#fef2f2'],'admin'=>['#1D4ED8','#eff6ff'],'fleet_manager'=>['#047857','#f0fdf4'],'controller'=>['#D97706','#fffbeb'],'director'=>['#7C3AED','#faf5ff'],'collaborator'=>['#0891B2','#ecfeff'],'driver_user'=>['#64748B','#f8fafc']];
                $roleLabels = ['super_admin'=>'Super Admin','admin'=>'Admin','fleet_manager'=>'Resp. Flotte','controller'=>'Contrôleur','director'=>'Directeur','collaborator'=>'Collaborateur','driver_user'=>'Chauffeur'];
                @endphp
                @foreach($users as $user)
                    @php
                        $roleName  = $user->getRoleNames()->first() ?? '';
                        $rColors   = $roleColors[$roleName] ?? ['#64748b','#f8fafc'];
                        $isDeleted = $user->trashed();
                    @endphp
                    <tr style="{{ $isDeleted ? 'opacity:.5;' : '' }}">
                        <td>
                            <div class="user-cell">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" class="user-avatar" alt="">
                                @else
                                    <div class="user-initials">{{ strtoupper(substr($user->name,0,2)) }}</div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.users.show', $user) }}" style="font-weight:600;color:#0f172a;text-decoration:none;">
                                        {{ $user->name }}
                                        @if($isDeleted) <span style="font-size:.7rem;color:#dc2626;">(archivé)</span> @endif
                                    </a>
                                    <div style="font-size:.77rem;color:#94a3b8;">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($roleName)
                                <span class="badge" style="background:{{ $rColors[1] }};color:{{ $rColors[0] }};">
                                    {{ $roleLabels[$roleName] ?? $roleName }}
                                </span>
                            @else
                                <span class="badge badge-gray">—</span>
                            @endif
                        </td>
                        <td style="color:#64748b;font-size:.82rem;">{{ $user->department ?? '—' }}</td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge badge-green">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                                    Actif
                                </span>
                            @else
                                <span class="badge badge-orange">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#d97706;display:inline-block;"></span>
                                    Suspendu
                                </span>
                            @endif
                        </td>
                        <td style="font-size:.8rem;color:#64748b;">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                        </td>
                        <td style="font-size:.8rem;color:#64748b;">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:.4rem;">
                                @if($isDeleted)
                                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-sm" title="Restaurer">
                                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-6.3 2.6L3 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            Restaurer
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm" title="Voir">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-sm" title="Modifier">
                                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Archiver {{ addslashes($user->name) }} ?" data-title="Archiver l'utilisateur ?" data-btn-text="Archiver">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Archiver">
                                                <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6M9 6V4h6v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="pagination-wrap">
                <span class="pagination-info">
                    Affichage {{ $users->firstItem() }}–{{ $users->lastItem() }} sur {{ $users->total() }}
                </span>
                {{ $users->links('pagination::simple-tailwind') }}
            </div>
        @endif
    @endif
</div>

@endsection
