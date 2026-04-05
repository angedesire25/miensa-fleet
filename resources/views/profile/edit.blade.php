@extends('layouts.dashboard')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<style>
.profile-card {
    background: #fff; border-radius: .75rem; border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.card-header {
    padding: 1.1rem 1.5rem; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .6rem;
}
.card-title { font-size: .95rem; font-weight: 700; color: #0f172a; }
.card-body { padding: 1.5rem; }

.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: .825rem; font-weight: 600; color: #374151; margin-bottom: .4rem; }
.form-input {
    width: 100%; padding: .65rem .9rem; border: 1.5px solid #e2e8f0;
    border-radius: .45rem; font-size: .875rem; color: #111827; background: #fff;
    outline: none; transition: border-color .15s, box-shadow .15s;
}
.form-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
.form-input::placeholder { color: #9ca3af; }
.form-input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
.form-error { color: #dc2626; font-size: .78rem; margin-top: .3rem; }
.form-hint { color: #94a3b8; font-size: .75rem; margin-top: .3rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.btn { padding: .6rem 1.25rem; border-radius: .45rem; font-size: .875rem; font-weight: 600; border: none; cursor: pointer; transition: opacity .15s; }
.btn-primary { background: linear-gradient(135deg,#10b981,#059669); color: #fff; }
.btn-primary:hover { opacity: .9; }
.btn-danger  { background: #fff; color: #dc2626; border: 1.5px solid #fecaca; }
.btn-danger:hover  { background: #fef2f2; }

.alert { padding: .75rem 1rem; border-radius: .45rem; font-size: .875rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: .6rem; }
.alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
.alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

.role-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .75rem; border-radius: 99px; font-size: .78rem; font-weight: 600;
}
</style>

{{-- En-tête --}}
<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.35rem;font-weight:700;color:#0f172a;margin:0;">Mon profil</h1>
    <p style="color:#64748b;font-size:.85rem;margin:.15rem 0 0;">Gérez vos informations personnelles et votre mot de passe</p>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:1.25rem;align-items:start;">

    {{-- ── Colonne gauche : avatar + infos rôle ──────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Card avatar --}}
        <div class="profile-card">
            <div class="card-body" style="text-align:center;padding:1.75rem 1.5rem;">

                {{-- Photo actuelle --}}
                <div style="position:relative;display:inline-block;margin-bottom:1rem;">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}"
                             alt="Avatar"
                             id="avatar-preview"
                             style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #10b981;">
                    @else
                        <div id="avatar-initials"
                             style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;margin:0 auto;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <img id="avatar-preview"
                             src=""
                             alt="Aperçu"
                             style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #10b981;display:none;margin:0 auto;">
                    @endif
                    {{-- Bouton caméra overlay --}}
                    <label for="avatar-input"
                           style="position:absolute;bottom:2px;right:2px;width:28px;height:28px;background:#10b981;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid #fff;"
                           title="Changer la photo">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke="white" stroke-width="2"/><circle cx="12" cy="13" r="4" stroke="white" stroke-width="2"/></svg>
                    </label>
                </div>

                <div style="font-size:1rem;font-weight:700;color:#0f172a;">{{ $user->name }}</div>
                <div style="color:#64748b;font-size:.825rem;margin:.2rem 0 .75rem;">{{ $user->email }}</div>

                @php
                    $roleColors = ['super_admin'=>'#B91C1C','admin'=>'#1D4ED8','fleet_manager'=>'#047857','controller'=>'#D97706','director'=>'#7C3AED','collaborator'=>'#0891B2','driver_user'=>'#64748B'];
                    $roleLabels = ['super_admin'=>'Super Administrateur','admin'=>'Administrateur','fleet_manager'=>'Responsable Flotte','controller'=>'Contrôleur','director'=>'Directeur','collaborator'=>'Collaborateur','driver_user'=>'Chauffeur'];
                    $roleName   = $user->getRoleNames()->first() ?? '';
                    $roleColor  = $roleColors[$roleName] ?? '#64748b';
                    $roleLabel  = $roleLabels[$roleName] ?? $roleName;
                @endphp
                <span class="role-badge" style="background:{{ $roleColor }}18;color:{{ $roleColor }};border:1px solid {{ $roleColor }}33;">
                    {{ $roleLabel }}
                </span>

                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f1f5f9;text-align:left;">
                    <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.4rem;">INFORMATIONS</div>
                    @if($user->department)
                        <div style="font-size:.82rem;color:#374151;margin-bottom:.3rem;">
                            <span style="color:#9ca3af;">Service :</span> {{ $user->department }}
                        </div>
                    @endif
                    @if($user->job_title)
                        <div style="font-size:.82rem;color:#374151;margin-bottom:.3rem;">
                            <span style="color:#9ca3af;">Poste :</span> {{ $user->job_title }}
                        </div>
                    @endif
                    @if($user->phone)
                        <div style="font-size:.82rem;color:#374151;">
                            <span style="color:#9ca3af;">Tél :</span> {{ $user->phone }}
                        </div>
                    @endif
                    <div style="font-size:.78rem;color:#cbd5e1;margin-top:.5rem;">
                        Compte créé le {{ $user->created_at->isoFormat('D MMMM YYYY') }}
                    </div>
                    @if($user->password_changed_at)
                        <div style="font-size:.78rem;color:#cbd5e1;">
                            MDP changé le {{ $user->password_changed_at->isoFormat('D MMMM YYYY') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Colonne droite : formulaires ───────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- ── Formulaire : informations personnelles ────────────────────── --}}
        <div class="profile-card">
            <div class="card-header">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Informations personnelles</span>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    {{-- Input avatar caché --}}
                    <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/jpg,image/png,image/webp" style="display:none;" onchange="previewAvatar(this)">

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nom complet <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" placeholder="Prénom Nom" required>
                            @error('name') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adresse email <span style="color:#ef4444;">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" placeholder="prenom.nom@entreprise.ci" required>
                            @error('email') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="+225 07 00 00 00 00">
                            @error('phone') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Service / Département</label>
                            <input type="text" name="department" value="{{ old('department', $user->department) }}" class="form-input" placeholder="ex : Logistique">
                            @error('department') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Intitulé du poste</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="form-input" placeholder="ex : Responsable de Flotte">
                        @error('job_title') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Photo de profil</label>
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <label for="avatar-input"
                                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1rem;border:1.5px dashed #d1d5db;border-radius:.45rem;cursor:pointer;font-size:.825rem;color:#64748b;transition:border-color .15s;"
                                   onmouseover="this.style.borderColor='#10b981';this.style.color='#10b981'"
                                   onmouseout="this.style.borderColor='#d1d5db';this.style.color='#64748b'">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Choisir une image
                            </label>
                            <span id="avatar-filename" style="font-size:.78rem;color:#9ca3af;">JPG, PNG, WEBP — max 2 Mo</span>
                        </div>
                        @error('avatar') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Formulaire : changement de mot de passe ───────────────────── --}}
        <div class="profile-card" id="password">
            <div class="card-header">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#f59e0b" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Changer le mot de passe</span>
            </div>
            <div class="card-body">

                @if(session('success_password'))
                    <div class="alert alert-success">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#16a34a"/><path d="M9 12l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                        {{ session('success_password') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Mot de passe actuel <span style="color:#ef4444;">*</span></label>
                        <div style="position:relative;">
                            <input type="password" name="current_password" id="pwd-current" class="form-input" placeholder="••••••••" style="padding-right:2.75rem;" required>
                            <button type="button" onclick="togglePwd('pwd-current')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                                <svg width="17" height="17" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                            </button>
                        </div>
                        @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nouveau mot de passe <span style="color:#ef4444;">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="pwd-new" class="form-input" placeholder="••••••••" style="padding-right:2.75rem;" required>
                                <button type="button" onclick="togglePwd('pwd-new')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                                </button>
                            </div>
                            <div class="form-hint">8 caractères min., majuscule + chiffre requis</div>
                            @error('password') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmer le nouveau mot de passe <span style="color:#ef4444;">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password_confirmation" id="pwd-confirm" class="form-input" placeholder="••••••••" style="padding-right:2.75rem;" required>
                                <button type="button" onclick="togglePwd('pwd-confirm')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                                </button>
                            </div>
                            @error('password_confirmation') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Indicateur de force --}}
                    <div style="margin-bottom:1.25rem;">
                        <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.4rem;">Force du mot de passe</div>
                        <div style="height:5px;background:#e2e8f0;border-radius:99px;overflow:hidden;">
                            <div id="pwd-strength-bar" style="height:100%;width:0%;border-radius:99px;transition:width .3s,background .3s;background:#ef4444;"></div>
                        </div>
                        <div id="pwd-strength-label" style="font-size:.72rem;color:#94a3b8;margin-top:.3rem;"></div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">
                            Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
// Aperçu de l'avatar avant upload
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    document.getElementById('avatar-filename').textContent = file.name;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('avatar-preview');
        const initials = document.getElementById('avatar-initials');
        preview.src = e.target.result;
        preview.style.display = 'block';
        preview.style.margin = '0 auto';
        if (initials) initials.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// Toggle affichage mot de passe
function togglePwd(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Indicateur de force du mot de passe
document.getElementById('pwd-new').addEventListener('input', function () {
    const val = this.value;
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const bar    = document.getElementById('pwd-strength-bar');
    const label  = document.getElementById('pwd-strength-label');
    const levels = [
        { pct: '0%',   color: '#e2e8f0', txt: '' },
        { pct: '25%',  color: '#ef4444', txt: 'Très faible' },
        { pct: '50%',  color: '#f59e0b', txt: 'Faible' },
        { pct: '75%',  color: '#3b82f6', txt: 'Bon' },
        { pct: '100%', color: '#10b981', txt: 'Excellent' },
    ];
    bar.style.width      = levels[score].pct;
    bar.style.background = levels[score].color;
    label.textContent    = levels[score].txt;
    label.style.color    = levels[score].color;
});
</script>

@endsection
