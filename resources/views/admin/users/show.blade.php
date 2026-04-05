@extends('layouts.dashboard')

@section('title', $user->name)
@section('page-title', 'Administration — Fiche utilisateur')

@section('content')
@php
    $roleColors = ['super_admin'=>['#B91C1C','#fef2f2'],'admin'=>['#1D4ED8','#eff6ff'],'fleet_manager'=>['#047857','#f0fdf4'],'controller'=>['#D97706','#fffbeb'],'director'=>['#7C3AED','#faf5ff'],'collaborator'=>['#0891B2','#ecfeff'],'driver_user'=>['#64748B','#f8fafc']];
    $roleLabels = ['super_admin'=>'Super Administrateur','admin'=>'Administrateur','fleet_manager'=>'Responsable de Flotte','controller'=>'Contrôleur','director'=>'Directeur','collaborator'=>'Collaborateur','driver_user'=>'Chauffeur'];
    $roleName   = $user->getRoleNames()->first() ?? '';
    $rColors    = $roleColors[$roleName] ?? ['#64748b','#f8fafc'];
@endphp

<style>
.card { background:#fff; border-radius:.75rem; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:1rem; }
.card-head { padding:.9rem 1.25rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:.6rem; }
.card-title { font-size:.9rem; font-weight:700; color:#0f172a; }
.card-body { padding:1.25rem 1.5rem; }
.dl { display:grid; grid-template-columns:160px 1fr; gap:.55rem 1rem; }
.dt { font-size:.8rem; color:#94a3b8; font-weight:500; display:flex; align-items:center; }
.dd { font-size:.875rem; color:#0f172a; font-weight:500; }
.btn { padding:.5rem 1rem; border-radius:.45rem; font-size:.825rem; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; transition:opacity .15s; }
.btn-primary { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.btn-ghost { background:#f8fafc; color:#374151; border:1.5px solid #e2e8f0; }
.btn-ghost:hover { background:#f1f5f9; }
.btn-orange { background:#fffbeb; color:#92400e; border:1.5px solid #fde68a; }
.btn-danger { background:#fef2f2; color:#dc2626; border:1.5px solid #fecaca; }
.badge { display:inline-flex; align-items:center; gap:.25rem; padding:.2rem .6rem; border-radius:99px; font-size:.72rem; font-weight:600; }
.activity-item { display:flex; align-items:flex-start; gap:.75rem; padding:.7rem 0; border-bottom:1px solid #f8fafc; }
.activity-item:last-child { border-bottom:none; }
.activity-dot { width:8px; height:8px; border-radius:50%; background:#10b981; flex-shrink:0; margin-top:.35rem; }
.alert-flash { padding:.75rem 1rem; border-radius:.45rem; margin-bottom:1.25rem; display:flex; align-items:flex-start; gap:.6rem; font-size:.875rem; }
.alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
</style>

{{-- Fil d'ariane --}}
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('admin.users.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Utilisateurs</a>
    <span>›</span>
    <span style="color:#374151;">{{ $user->name }}</span>
</div>

@if(session('new_password'))
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.75rem;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#d97706" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="#d97706" stroke-width="2" stroke-linecap="round"/></svg>
        <div>
            <p style="font-weight:700;color:#92400e;margin:0 0 .25rem;">Nouveau mot de passe généré</p>
            <p style="margin:0;color:#78350f;font-size:.875rem;">Communiquez ce mot de passe à l'utilisateur : <strong style="font-family:monospace;font-size:1rem;letter-spacing:.05em;">{{ session('new_password') }}</strong></p>
            <p style="margin:.3rem 0 0;color:#92400e;font-size:.78rem;">⚠ Affiché une seule fois — copiez-le maintenant.</p>
        </div>
    </div>
@endif

<div style="display:grid;grid-template-columns:300px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche : identité ──────────────────────────────────── --}}
    <div>
        <div class="card">
            <div class="card-body" style="text-align:center;padding:1.75rem 1.5rem;">
                {{-- Avatar --}}
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}"
                         style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid {{ $rColors[0] }}33;margin:0 auto .75rem;"
                         alt="">
                @else
                    <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:700;color:#fff;margin:0 auto .75rem;">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif

                <div style="font-size:1.05rem;font-weight:700;color:#0f172a;">{{ $user->name }}</div>
                <div style="color:#64748b;font-size:.825rem;margin:.2rem 0 .75rem;">{{ $user->email }}</div>

                {{-- Badge rôle --}}
                @if($roleName)
                    <span class="badge" style="background:{{ $rColors[1] }};color:{{ $rColors[0] }};">
                        {{ $roleLabels[$roleName] ?? $roleName }}
                    </span>
                @endif

                {{-- Statut --}}
                <div style="margin-top:.75rem;">
                    @if($user->trashed())
                        <span class="badge" style="background:#fef2f2;color:#dc2626;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                            Archivé
                        </span>
                    @elseif($user->status === 'active')
                        <span class="badge" style="background:#f0fdf4;color:#16a34a;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                            Actif
                        </span>
                    @else
                        <span class="badge" style="background:#fffbeb;color:#d97706;">
                            <span style="width:6px;height:6px;border-radius:50%;background:#d97706;display:inline-block;"></span>
                            Suspendu
                        </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if(!$user->trashed())
            <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;display:flex;flex-direction:column;gap:.5rem;">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary" style="justify-content:center;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Modifier le compte
                </a>

                {{-- Toggle statut --}}
                @if($user->id !== auth()->id())
                @if($user->status === 'active')
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                      data-confirm="Suspendre le compte de {{ addslashes($user->name) }} ?"
                      data-title="Suspendre l'utilisateur" data-btn-text="Suspendre" data-btn-color="#d97706">
                    @csrf
                    <button type="submit" class="btn btn-orange" style="width:100%;justify-content:center;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M10 15V9M14 15V9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Suspendre
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                      data-confirm="Réactiver le compte de {{ addslashes($user->name) }} ?"
                      data-title="Réactiver l'utilisateur" data-icon="question" data-btn-text="Réactiver" data-btn-color="#10b981">
                    @csrf
                    <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center;color:#16a34a;border-color:#bbf7d0;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Réactiver
                    </button>
                </form>
                @endif

                {{-- Reset MDP --}}
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                      data-confirm="Un nouveau mot de passe sera généré et affiché une seule fois."
                      data-title="Réinitialiser le mot de passe ?" data-icon="question" data-btn-text="Générer" data-btn-color="#d97706">
                    @csrf
                    <button type="submit" class="btn btn-orange" style="width:100%;justify-content:center;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Réinitialiser MDP
                    </button>
                </form>

                {{-- Archiver --}}
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                      data-confirm="Le compte sera archivé mais pourra être restauré."
                      data-title="Archiver ce compte ?" data-btn-text="Archiver">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Archiver
                    </button>
                </form>
                @endif
            </div>
            @else
            <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;">
                <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 12a9 9 0 109-9 9 9 0 00-6.3 2.6L3 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 3v5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Restaurer le compte
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Colonne droite : détails + activité ────────────────────────── --}}
    <div>

        {{-- Informations détaillées --}}
        <div class="card">
            <div class="card-head">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Informations du compte</span>
            </div>
            <div class="card-body">
                <div class="dl">
                    <span class="dt">Email</span>
                    <span class="dd">{{ $user->email }}</span>

                    <span class="dt">Téléphone</span>
                    <span class="dd">{{ $user->phone ?? '—' }}</span>

                    <span class="dt">Service</span>
                    <span class="dd">{{ $user->department ?? '—' }}</span>

                    <span class="dt">Poste</span>
                    <span class="dd">{{ $user->job_title ?? '—' }}</span>

                    <span class="dt">Rôle</span>
                    <span class="dd">
                        @if($roleName)
                            <span class="badge" style="background:{{ $rColors[1] }};color:{{ $rColors[0] }};">{{ $roleLabels[$roleName] ?? $roleName }}</span>
                        @else —
                        @endif
                    </span>

                    <span class="dt">Créé le</span>
                    <span class="dd">{{ $user->created_at->isoFormat('D MMMM YYYY à HH:mm') }}</span>

                    <span class="dt">Dernière connexion</span>
                    <span class="dd">{{ $user->last_login_at ? $user->last_login_at->isoFormat('D MMMM YYYY à HH:mm') : 'Jamais' }}</span>

                    <span class="dt">IP dernière connexion</span>
                    <span class="dd" style="font-family:monospace;font-size:.82rem;">{{ $user->last_login_ip ?? '—' }}</span>

                    <span class="dt">Mot de passe changé</span>
                    <span class="dd">{{ $user->password_changed_at ? $user->password_changed_at->isoFormat('D MMMM YYYY') : 'Jamais changé' }}</span>

                    @if($user->status === 'suspended' && $user->suspension_reason)
                        <span class="dt">Motif suspension</span>
                        <span class="dd" style="color:#d97706;">{{ $user->suspension_reason }}</span>
                    @endif

                    @if($user->trashed())
                        <span class="dt">Archivé le</span>
                        <span class="dd" style="color:#dc2626;">{{ $user->deleted_at->isoFormat('D MMMM YYYY à HH:mm') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Historique d'activité --}}
        <div class="card">
            <div class="card-head">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#64748b" stroke-width="2"/><path d="M12 6v6l4 2" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Activité récente</span>
                <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;">20 dernières actions</span>
            </div>
            <div class="card-body" style="padding:.5rem 1.25rem;">
                @if($activities->isEmpty())
                    <p style="color:#94a3b8;font-size:.85rem;padding:.75rem 0;text-align:center;">Aucune activité enregistrée</p>
                @else
                    @foreach($activities as $activity)
                        <div class="activity-item">
                            <div class="activity-dot" style="background:{{ $activity->event === 'deleted' ? '#dc2626' : ($activity->event === 'created' ? '#10b981' : '#3b82f6') }};"></div>
                            <div style="flex:1;">
                                <div style="font-size:.825rem;color:#374151;">
                                    @if($activity->description)
                                        {{ $activity->description }}
                                    @else
                                        <span style="text-transform:capitalize;">{{ $activity->event ?? 'action' }}</span>
                                        sur <strong>{{ class_basename($activity->subject_type ?? 'élément') }}</strong>
                                    @endif
                                </div>
                                <div style="font-size:.74rem;color:#94a3b8;margin-top:.1rem;">
                                    {{ ucfirst($activity->created_at->diffForHumans()) }}
                                    · Log : {{ $activity->log_name }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
