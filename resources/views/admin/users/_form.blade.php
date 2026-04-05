@php
    $isEdit       = $user !== null;
    $currentRole  = $isEdit ? $user->getRoleNames()->first() : old('role');
    $roleLabels   = ['super_admin'=>'Super Administrateur','admin'=>'Administrateur','fleet_manager'=>'Responsable de Flotte','controller'=>'Contrôleur de Parc','director'=>'Directeur','collaborator'=>'Collaborateur','driver_user'=>'Chauffeur (portail)'];
    $roleColors   = ['super_admin'=>'#B91C1C','admin'=>'#1D4ED8','fleet_manager'=>'#047857','controller'=>'#D97706','director'=>'#7C3AED','collaborator'=>'#0891B2','driver_user'=>'#64748B'];
    $roleDescs    = ['super_admin'=>'Accès technique absolu. Toutes les opérations.','admin'=>'Gestion complète de la flotte et des utilisateurs.','fleet_manager'=>'Affectations, demandes, sinistres, réparations.','controller'=>'Fiches de contrôle, km, infractions terrain.','director'=>'Lecture seule et accès aux rapports.','collaborator'=>'Soumission de demandes de véhicule uniquement.','driver_user'=>'Portail chauffeur : consultation de ses propres données.'];
@endphp

<style>
.form-card { background:#fff; border-radius:.75rem; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:1.25rem; }
.form-card-head { padding:1rem 1.5rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:.6rem; }
.form-card-title { font-size:.95rem; font-weight:700; color:#0f172a; }
.form-card-body { padding:1.5rem; }
.fg { margin-bottom:1.1rem; }
.fl { display:block; font-size:.825rem; font-weight:600; color:#374151; margin-bottom:.4rem; }
.req { color:#ef4444; }
.fi { width:100%; padding:.62rem .9rem; border:1.5px solid #e2e8f0; border-radius:.45rem; font-size:.875rem; color:#111827; outline:none; transition:border-color .15s; }
.fi:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.1); }
.fi::placeholder { color:#9ca3af; }
.fe { color:#dc2626; font-size:.775rem; margin-top:.3rem; }
.grid2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }

/* Sélecteur de rôle visuel */
.role-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.65rem; }
.role-option { position:relative; }
.role-option input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
.role-label {
    display:flex; align-items:flex-start; gap:.75rem; padding:.85rem 1rem;
    border:2px solid #e2e8f0; border-radius:.55rem; cursor:pointer;
    transition:border-color .15s, background .15s;
}
.role-label:hover { border-color:#10b981; background:#f0fdf4; }
.role-option input[type=radio]:checked + .role-label { border-color:var(--rc); background:var(--rb); }
.role-dot { width:10px; height:10px; border-radius:50%; margin-top:.25rem; flex-shrink:0; }
.role-name { font-size:.825rem; font-weight:700; color:#0f172a; }
.role-desc { font-size:.73rem; color:#64748b; margin-top:.15rem; line-height:1.45; }

.btn { padding:.6rem 1.25rem; border-radius:.45rem; font-size:.875rem; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:.4rem; text-decoration:none; transition:opacity .15s; }
.btn-primary { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.btn-primary:hover { opacity:.9; }
.btn-ghost { background:#f8fafc; color:#374151; border:1.5px solid #e2e8f0; }
.btn-ghost:hover { background:#f1f5f9; }
</style>

{{-- Fil d'ariane --}}
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('admin.users.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Utilisateurs</a>
    <span>›</span>
    <span style="color:#374151;">{{ $isEdit ? 'Modifier '.$user->name : 'Nouvel utilisateur' }}</span>
</div>

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start;">

        {{-- Colonne principale --}}
        <div>

            {{-- Informations personnelles --}}
            <div class="form-card">
                <div class="form-card-head">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="form-card-title">Informations personnelles</span>
                </div>
                <div class="form-card-body">
                    <div class="grid2">
                        <div class="fg">
                            <label class="fl">Nom complet <span class="req">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $isEdit ? $user->name : '') }}" class="fi" placeholder="Prénom Nom" required>
                            @error('name') <div class="fe">{{ $message }}</div> @enderror
                        </div>
                        <div class="fg">
                            <label class="fl">Adresse email <span class="req">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $isEdit ? $user->email : '') }}" class="fi" placeholder="prenom.nom@miensafleet.ci" required>
                            @error('email') <div class="fe">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="grid2">
                        <div class="fg">
                            <label class="fl">Téléphone</label>
                            <input type="text" name="phone" value="{{ old('phone', $isEdit ? $user->phone : '') }}" class="fi" placeholder="+225 07 00 00 00 00">
                            @error('phone') <div class="fe">{{ $message }}</div> @enderror
                        </div>
                        <div class="fg">
                            <label class="fl">Service / Département</label>
                            <input type="text" name="department" value="{{ old('department', $isEdit ? $user->department : '') }}" class="fi" placeholder="ex : Logistique">
                            @error('department') <div class="fe">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="fg" style="margin-bottom:0;">
                        <label class="fl">Intitulé du poste</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $isEdit ? $user->job_title : '') }}" class="fi" placeholder="ex : Responsable de Flotte">
                        @error('job_title') <div class="fe">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Rôle --}}
            <div class="form-card">
                <div class="form-card-head">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#7c3aed" stroke-width="2"/></svg>
                    <span class="form-card-title">Rôle et permissions <span class="req">*</span></span>
                </div>
                <div class="form-card-body">
                    @error('role') <div class="fe" style="margin-bottom:.75rem;">{{ $message }}</div> @enderror
                    <div class="role-grid">
                        @foreach($roles as $role)
                            @php
                                $rc  = $roleColors[$role->name] ?? '#64748b';
                                $hex = ltrim($rc, '#');
                                $r   = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b = hexdec(substr($hex,4,2));
                                $bg  = "rgba({$r},{$g},{$b},.08)";
                                // Super admin ne peut être attribué que par un super admin
                                $disabled = ($role->name === 'super_admin' && !auth()->user()->hasRole('super_admin'));
                            @endphp
                            <div class="role-option" style="--rc:{{ $rc }};--rb:{{ $bg }};">
                                <input type="radio" name="role" id="role_{{ $role->name }}" value="{{ $role->name }}"
                                       {{ $currentRole === $role->name || old('role') === $role->name ? 'checked' : '' }}
                                       {{ $disabled ? 'disabled' : '' }}>
                                <label class="role-label" for="role_{{ $role->name }}" style="{{ $disabled ? 'opacity:.4;cursor:not-allowed;' : '' }}">
                                    <div class="role-dot" style="background:{{ $rc }};margin-top:.3rem;"></div>
                                    <div>
                                        <div class="role-name">{{ $roleLabels[$role->name] ?? $role->name }}</div>
                                        <div class="role-desc">{{ $roleDescs[$role->name] ?? '' }}</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── Section chauffeur (affichée uniquement si rôle = driver_user) ── --}}
            <div id="driver-section" style="display:{{ ($currentRole === 'driver_user' || old('role') === 'driver_user') ? 'block' : 'none' }};">
                <div class="form-card" style="border-color:#fde68a;">
                    <div class="form-card-head" style="background:#fffbeb;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                            <rect x="1" y="4" width="22" height="16" rx="2" stroke="#d97706" stroke-width="2"/>
                            <circle cx="8" cy="12" r="2" stroke="#d97706" stroke-width="1.5"/>
                            <path d="M14 9h4M14 12h4M14 15h2" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="form-card-title" style="color:#92400e;">Informations chauffeur</span>
                        <span style="margin-left:.5rem;font-size:.72rem;font-weight:600;padding:.15rem .55rem;background:#fef3c7;border:1px solid #fde68a;border-radius:99px;color:#92400e;">
                            Requis pour ce rôle
                        </span>
                    </div>
                    <div class="form-card-body">
                        <p style="font-size:.8rem;color:#78350f;margin:0 0 1.25rem;padding:.65rem .9rem;background:#fffbeb;border-left:3px solid #fde68a;border-radius:.35rem;">
                            Un profil chauffeur sera créé automatiquement. Renseignez les informations du permis maintenant ou complétez-les plus tard dans la fiche chauffeur.
                        </p>

                        {{-- Date de naissance + Date d'embauche --}}
                        <div class="grid2">
                            <div class="fg">
                                <label class="fl">Date de naissance</label>
                                <input type="date" name="driver_date_of_birth"
                                       value="{{ old('driver_date_of_birth') }}"
                                       max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                       class="fi">
                                @error('driver_date_of_birth')<div class="fe">{{ $message }}</div>@enderror
                            </div>
                            <div class="fg">
                                <label class="fl">Date d'embauche</label>
                                <input type="date" name="driver_hire_date"
                                       value="{{ old('driver_hire_date', now()->format('Y-m-d')) }}"
                                       class="fi">
                                @error('driver_hire_date')<div class="fe">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Permis --}}
                        <div class="grid2">
                            <div class="fg">
                                <label class="fl">
                                    N° de permis <span class="req" id="license-req-star">*</span>
                                </label>
                                <input type="text" name="driver_license_number"
                                       value="{{ old('driver_license_number') }}"
                                       class="fi" placeholder="CI-B-2023-00456"
                                       style="font-family:monospace;text-transform:uppercase;"
                                       oninput="this.value=this.value.toUpperCase()">
                                @error('driver_license_number')<div class="fe">{{ $message }}</div>@enderror
                            </div>
                            <div class="fg">
                                <label class="fl">Date d'expiration du permis <span class="req">*</span></label>
                                <input type="date" name="driver_license_expiry_date"
                                       value="{{ old('driver_license_expiry_date') }}"
                                       min="{{ now()->format('Y-m-d') }}"
                                       class="fi">
                                @error('driver_license_expiry_date')<div class="fe">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Catégories + Autorité --}}
                        <div class="grid2">
                            <div class="fg">
                                <label class="fl">Catégories de permis <span class="req">*</span></label>
                                @php $selCats = old('driver_license_categories', []); @endphp
                                <div style="display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.25rem;">
                                    @foreach(['A','B','C','D','E','BE','CE'] as $cat)
                                    <label style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .6rem;border:1.5px solid #e2e8f0;border-radius:.35rem;cursor:pointer;font-size:.8rem;font-weight:600;color:#374151;transition:all .12s;"
                                           class="cat-pill" id="cat-pill-{{ $cat }}">
                                        <input type="checkbox" name="driver_license_categories[]" value="{{ $cat }}"
                                               @checked(in_array($cat, $selCats))
                                               style="display:none;"
                                               onchange="toggleCatPill(this, '{{ $cat }}')">
                                        {{ $cat }}
                                    </label>
                                    @endforeach
                                </div>
                                @error('driver_license_categories')<div class="fe">{{ $message }}</div>@enderror
                            </div>
                            <div class="fg">
                                <label class="fl">Autorité de délivrance</label>
                                <input type="text" name="driver_license_issuing_authority"
                                       value="{{ old('driver_license_issuing_authority') }}"
                                       class="fi" placeholder="Préfecture, sous-préfecture…">
                            </div>
                        </div>

                        {{-- Type de contrat --}}
                        <div class="fg" style="margin-bottom:0;">
                            <label class="fl">Type de contrat</label>
                            <select name="driver_contract_type" class="fi">
                                @foreach(['permanent'=>'CDI — Contrat à durée indéterminée','fixed_term'=>'CDD — Contrat à durée déterminée','interim'=>'Intérim','contractor'=>'Prestataire externe'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('driver_contract_type', 'permanent') === $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mot de passe (création uniquement) --}}
            @if(!$isEdit)
            <div class="form-card">
                <div class="form-card-head">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#f59e0b" stroke-width="2"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="form-card-title">Mot de passe initial <span class="req">*</span></span>
                </div>
                <div class="form-card-body">
                    <div class="grid2">
                        <div class="fg">
                            <label class="fl">Mot de passe <span class="req">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="pwd-new" class="fi" placeholder="••••••••" style="padding-right:2.75rem;" required>
                                <button type="button" onclick="togglePwd('pwd-new')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                                </button>
                            </div>
                            @error('password') <div class="fe">{{ $message }}</div> @enderror
                        </div>
                        <div class="fg">
                            <label class="fl">Confirmer <span class="req">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password_confirmation" id="pwd-confirm" class="fi" placeholder="••••••••" style="padding-right:2.75rem;" required>
                                <button type="button" onclick="togglePwd('pwd-confirm')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;">
                                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="font-size:.775rem;color:#94a3b8;">8 caractères min. · au moins une majuscule et un chiffre</div>
                </div>
            </div>
            @endif

        </div>

        {{-- Colonne droite : statut + actions --}}
        <div style="display:flex;flex-direction:column;gap:1rem;">

            {{-- Statut --}}
            <div class="form-card">
                <div class="form-card-head">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="form-card-title">Statut du compte</span>
                </div>
                <div class="form-card-body">
                    <div style="display:flex;flex-direction:column;gap:.6rem;">
                        @foreach(['active'=>['Actif','Compte opérationnel, accès complet.','#16a34a','#f0fdf4'], 'suspended'=>['Suspendu','Connexion bloquée, données conservées.','#d97706','#fffbeb']] as $val => [$lbl, $desc, $color, $bg])
                            <label style="display:flex;align-items:flex-start;gap:.65rem;padding:.75rem;border:2px solid {{ old('status', $isEdit ? $user->status : 'active') === $val ? $color : '#e2e8f0' }};border-radius:.45rem;cursor:pointer;background:{{ old('status', $isEdit ? $user->status : 'active') === $val ? $bg : '#fff' }};transition:all .15s;" id="status-label-{{ $val }}">
                                <input type="radio" name="status" value="{{ $val }}" {{ old('status', $isEdit ? $user->status : 'active') === $val ? 'checked' : '' }} style="margin-top:.2rem;accent-color:{{ $color }};" onchange="updateStatusLabels()">
                                <div>
                                    <div style="font-size:.83rem;font-weight:700;color:#0f172a;">{{ $lbl }}</div>
                                    <div style="font-size:.73rem;color:#64748b;">{{ $desc }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Raison de suspension --}}
                    <div id="suspension-reason-wrap" style="{{ old('status', $isEdit ? $user->status : 'active') === 'suspended' ? '' : 'display:none;' }}margin-top:.75rem;">
                        <label class="fl">Motif de suspension</label>
                        <textarea name="suspension_reason" class="fi" rows="3" placeholder="Décrivez la raison…" style="resize:vertical;">{{ old('suspension_reason', $isEdit ? $user->suspension_reason : '') }}</textarea>
                        @error('suspension_reason') <div class="fe">{{ $message }}</div> @enderror
                    </div>

                    @error('status') <div class="fe">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Boutons --}}
            <div class="form-card">
                <div class="form-card-body" style="padding:1rem 1.25rem;">
                    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:.6rem;">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><path d="M17 21v-8H7v8M7 3v5h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le compte' }}
                    </button>
                    <a href="{{ $isEdit ? route('admin.users.show', $user) : route('admin.users.index') }}" class="btn btn-ghost" style="width:100%;justify-content:center;">
                        Annuler
                    </a>
                </div>
            </div>

            @if($isEdit)
            {{-- Actions rapides --}}
            <div class="form-card" style="border-color:#fecaca;">
                <div class="form-card-head" style="border-bottom-color:#fef2f2;">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#dc2626" stroke-width="2"/></svg>
                    <span class="form-card-title" style="color:#dc2626;">Zone sensible</span>
                </div>
                <div class="form-card-body" style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.6rem;">
                    {{-- Reset MDP --}}
                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                          data-confirm="Un nouveau mot de passe sera généré et affiché une seule fois."
                          data-title="Réinitialiser le mot de passe ?" data-icon="question" data-btn-text="Générer" data-btn-color="#d97706">
                        @csrf
                        <button type="submit" class="btn" style="width:100%;justify-content:center;background:#fffbeb;color:#92400e;border:1px solid #fde68a;">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            Réinitialiser le mot de passe
                        </button>
                    </form>
                    {{-- Archiver --}}
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              data-confirm="Le compte sera archivé. Cette action peut être annulée."
                              data-title="Archiver {{ addslashes($user->name) }} ?" data-btn-text="Archiver">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center;border:1.5px solid #fecaca;">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="1.8"/><path d="M19 6l-1 14H6L5 6M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                Archiver ce compte
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</form>

<script>
function togglePwd(id) {
    const i = document.getElementById(id);
    i.type = i.type === 'password' ? 'text' : 'password';
}

function updateStatusLabels() {
    const radios = document.querySelectorAll('input[name="status"]');
    const colors = { active: '#16a34a', suspended: '#d97706' };
    const bgs    = { active: '#f0fdf4', suspended: '#fffbeb' };
    radios.forEach(r => {
        const lbl = document.getElementById('status-label-' + r.value);
        if (lbl) {
            lbl.style.borderColor = r.checked ? colors[r.value] : '#e2e8f0';
            lbl.style.background  = r.checked ? bgs[r.value]   : '#fff';
        }
    });
    const wrap = document.getElementById('suspension-reason-wrap');
    if (wrap) wrap.style.display = document.querySelector('input[name="status"][value="suspended"]').checked ? '' : 'none';
}

// ── Affichage conditionnel de la section chauffeur ─────────────────────────

document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const section = document.getElementById('driver-section');
        if (section) {
            section.style.display = radio.value === 'driver_user' ? 'block' : 'none';
        }
    });
});

// ── Pilules de catégories de permis ───────────────────────────────────────

function toggleCatPill(checkbox, cat) {
    const pill = document.getElementById('cat-pill-' + cat);
    if (!pill) return;
    if (checkbox.checked) {
        pill.style.borderColor  = '#d97706';
        pill.style.background   = '#fffbeb';
        pill.style.color        = '#92400e';
    } else {
        pill.style.borderColor  = '#e2e8f0';
        pill.style.background   = '';
        pill.style.color        = '#374151';
    }
}

// Appliquer le style aux catégories déjà cochées au chargement
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[name="driver_license_categories[]"]').forEach(cb => {
        if (cb.checked) toggleCatPill(cb, cb.value);
    });
});
</script>
