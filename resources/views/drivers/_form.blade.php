{{--
  Formulaire partagé chauffeurs — utilisé par create.blade.php et edit.blade.php
  Variables attendues : $driver (peut être null pour create), $vehicles (collection)
--}}
@php $isEdit = isset($driver) && $driver->exists; @endphp

<style>
.form-card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.form-head{padding:.8rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.55rem;background:#fafafa;}
.form-head-title{font-size:.875rem;font-weight:700;color:#0f172a;}
.form-body{padding:1.25rem 1.5rem;}
.form-grid{display:grid;gap:1rem;}
.form-grid-2{grid-template-columns:1fr 1fr;}
.form-grid-3{grid-template-columns:1fr 1fr 1fr;}
.form-group{display:flex;flex-direction:column;gap:.35rem;}
.form-label{font-size:.78rem;font-weight:600;color:#374151;}
.form-label .req{color:#ef4444;margin-left:.15rem;}
.form-input,.form-select,.form-textarea{padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.855rem;color:#0f172a;background:#fff;outline:none;transition:border-color .15s;width:100%;}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.08);}
.is-incomplete{border-color:#fde68a;background:#fffbeb;}
.form-textarea{resize:vertical;min-height:80px;}
.form-error{font-size:.75rem;color:#dc2626;margin-top:.2rem;}
.btn{padding:.55rem 1.1rem;border-radius:.45rem;font-size:.85rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.checkbox-group{display:flex;flex-wrap:wrap;gap:.5rem;}
.checkbox-pill{display:flex;align-items:center;gap:.35rem;padding:.35rem .75rem;border:1.5px solid #e2e8f0;border-radius:99px;cursor:pointer;font-size:.8rem;font-weight:600;color:#64748b;transition:.15s;}
.checkbox-pill:has(input:checked){border-color:#10b981;background:#f0fdf4;color:#047857;}
.checkbox-pill input{display:none;}
</style>

{{-- Section 1 : Identité ──────────────────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="form-head-title">Identité</span>
    </div>
    <div class="form-body">
        <div class="form-grid form-grid-3">
            <div class="form-group">
                <label class="form-label">Matricule <span class="req">*</span></label>
                <input type="text" name="matricule" value="{{ old('matricule', $driver->matricule ?? '') }}" class="form-input" placeholder="CHF-2024-001" style="font-family:monospace;" oninput="this.value=this.value.toUpperCase()">
                @error('matricule')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label">Nom complet <span class="req">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name', $driver->full_name ?? '') }}" class="form-input" placeholder="Prénom Nom">
                @error('full_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Date de naissance</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($driver) && $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : '') }}" class="form-input">
                @error('date_of_birth')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Téléphone <span class="req">*</span></label>
                <input type="tel" name="phone" value="{{ old('phone', $driver->phone ?? '') }}" class="form-input" placeholder="+225 07 XX XX XX XX">
                @error('phone')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $driver->email ?? '') }}" class="form-input" placeholder="chauffeur@example.com">
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group" style="grid-column:span 3;">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" value="{{ old('address', $driver->address ?? '') }}" class="form-input" placeholder="Quartier, commune, ville…">
                @error('address')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 2 : Contrat ───────────────────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#6366f1" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="form-head-title">Contrat de travail</span>
    </div>
    <div class="form-body">
        <div class="form-grid form-grid-3">
            <div class="form-group">
                <label class="form-label">Date d'embauche <span class="req">*</span></label>
                <input type="date" name="hire_date" value="{{ old('hire_date', isset($driver) && $driver->hire_date ? $driver->hire_date->format('Y-m-d') : '') }}" class="form-input">
                @error('hire_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Type de contrat <span class="req">*</span></label>
                <select name="contract_type" class="form-select">
                    @foreach(['permanent'=>'CDI','fixed_term'=>'CDD','interim'=>'Intérim','contractor'=>'Prestataire'] as $val=>$lbl)
                    <option value="{{ $val }}" @selected(old('contract_type', $driver->contract_type ?? 'permanent') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('contract_type')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Fin de contrat <small style="color:#94a3b8;">(CDD)</small></label>
                <input type="date" name="contract_end_date" value="{{ old('contract_end_date', isset($driver) && $driver->contract_end_date ? $driver->contract_end_date->format('Y-m-d') : '') }}" class="form-input">
                @error('contract_end_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 3 : Permis de conduire ───────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" stroke="#d97706" stroke-width="2"/><circle cx="8" cy="12" r="2" stroke="#d97706" stroke-width="1.5"/><path d="M14 9h4M14 12h4M14 15h2" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/></svg>
        <span class="form-head-title">Permis de conduire</span>
        {{-- Indicateur profil incomplet --}}
        @if($isEdit && !$driver->license_number)
            <span style="margin-left:auto;display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .65rem;background:#fffbeb;border:1px solid #fde68a;border-radius:99px;font-size:.72rem;font-weight:600;color:#92400e;">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2"/></svg>
                À compléter
            </span>
        @endif
    </div>
    <div class="form-body">
        {{-- Bannière profil auto-créé --}}
        @if($isEdit && !$driver->license_number)
        <div style="padding:.75rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.55rem;margin-bottom:1rem;font-size:.82rem;color:#92400e;display:flex;gap:.6rem;align-items:flex-start;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.05rem;"><path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2"/></svg>
            <div>
                <strong>Profil créé automatiquement depuis un compte utilisateur.</strong><br>
                Veuillez compléter les informations du permis de conduire ci-dessous.
            </div>
        </div>
        @endif

        <div class="form-grid form-grid-3" style="margin-bottom:1rem;">
            <div class="form-group">
                <label class="form-label">N° de permis</label>
                <input type="text" name="license_number" value="{{ old('license_number', $driver->license_number ?? '') }}"
                       class="form-input {{ !($driver->license_number ?? true) ? 'is-incomplete' : '' }}"
                       style="font-family:monospace;" placeholder="Ex : CI-B-2023-00456"
                       oninput="this.value=this.value.toUpperCase()">
                @error('license_number')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Date d'expiration</label>
                <input type="date" name="license_expiry_date"
                       value="{{ old('license_expiry_date', isset($driver) && $driver->license_expiry_date ? $driver->license_expiry_date->format('Y-m-d') : '') }}"
                       class="form-input">
                @error('license_expiry_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Autorité de délivrance</label>
                <input type="text" name="license_issuing_authority"
                       value="{{ old('license_issuing_authority', $driver->license_issuing_authority ?? '') }}"
                       class="form-input" placeholder="Préfecture, sous-préfecture…">
                @error('license_issuing_authority')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Catégories de permis</label>
            @php $selectedCats = old('license_categories', isset($driver) ? $driver->license_categories ?? [] : []); @endphp
            <div class="checkbox-group">
                @foreach(['A','B','C','D','E','BE','CE'] as $cat)
                <label class="checkbox-pill">
                    <input type="checkbox" name="license_categories[]" value="{{ $cat }}" @checked(in_array($cat, $selectedCats))>
                    {{ $cat }}
                </label>
                @endforeach
            </div>
            @error('license_categories')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Section 4 : Documents d'identité ────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#6366f1" stroke-width="2"/><path d="M14 2v6h6" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/><path d="M16 13H8M16 17H8M10 9H8" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round"/></svg>
        <span class="form-head-title">Documents d'identité</span>
        <span style="margin-left:auto;font-size:.73rem;color:#94a3b8;">PDF, JPEG, PNG · max 5 Mo par fichier</span>
    </div>
    <div class="form-body" style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Permis de conduire (scan) --}}
        <div>
            <div style="font-size:.8rem;font-weight:700;color:#374151;margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem;">
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#d97706;"></span>
                Permis de conduire
                @if($isEdit)
                    @php $existingLic = $driver->documents->firstWhere('type','license'); @endphp
                    @if($existingLic?->file_path)
                        <a href="{{ Storage::url($existingLic->file_path) }}" target="_blank"
                           style="margin-left:.5rem;font-size:.72rem;font-weight:600;color:#10b981;text-decoration:none;border:1px solid #bbf7d0;background:#f0fdf4;padding:.1rem .5rem;border-radius:99px;">
                            Voir fichier actuel ↗
                        </a>
                    @endif
                @endif
            </div>
            <div class="form-grid form-grid-2">
                <div class="form-group">
                    <label class="form-label">Date de délivrance</label>
                    <input type="date" name="license_issue_date"
                           value="{{ old('license_issue_date', isset($existingLic) ? $existingLic->issue_date?->format('Y-m-d') : '') }}"
                           class="form-input">
                    @error('license_issue_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Scan du permis <small style="color:#94a3b8;">(PDF ou image)</small></label>
                    <label style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .9rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.45rem;cursor:pointer;font-size:.82rem;font-weight:600;color:#374151;width:fit-content;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <span id="license-file-label">Choisir un fichier</span>
                        <input type="file" name="license_file" accept=".pdf,image/jpeg,image/png"
                               style="display:none;" onchange="updateFileLabel(this,'license-file-label')">
                    </label>
                    @error('license_file')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <hr style="border:none;border-top:1px solid #f1f5f9;margin:0;">

        {{-- CNI --}}
        <div>
            <div style="font-size:.8rem;font-weight:700;color:#374151;margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem;">
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#6366f1;"></span>
                Carte Nationale d'Identité (CNI)
                @if($isEdit)
                    @php $existingCni = $driver->documents->firstWhere('type','national_id'); @endphp
                    @if($existingCni?->file_path)
                        <a href="{{ Storage::url($existingCni->file_path) }}" target="_blank"
                           style="margin-left:.5rem;font-size:.72rem;font-weight:600;color:#10b981;text-decoration:none;border:1px solid #bbf7d0;background:#f0fdf4;padding:.1rem .5rem;border-radius:99px;">
                            Voir fichier actuel ↗
                        </a>
                    @endif
                @endif
            </div>
            <div class="form-grid form-grid-3">
                <div class="form-group">
                    <label class="form-label">Numéro CNI</label>
                    <input type="text" name="national_id_number"
                           value="{{ old('national_id_number', isset($existingCni) ? $existingCni->document_number : '') }}"
                           class="form-input" style="font-family:monospace;" placeholder="Ex : CI-0000-0000000">
                    @error('national_id_number')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Date de délivrance</label>
                    <input type="date" name="national_id_issue_date"
                           value="{{ old('national_id_issue_date', isset($existingCni) ? $existingCni->issue_date?->format('Y-m-d') : '') }}"
                           class="form-input">
                    @error('national_id_issue_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Date d'expiration</label>
                    <input type="date" name="national_id_expiry_date"
                           value="{{ old('national_id_expiry_date', isset($existingCni) ? $existingCni->expiry_date?->format('Y-m-d') : '') }}"
                           class="form-input">
                    @error('national_id_expiry_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-group" style="margin-top:.75rem;">
                <label class="form-label">Scan CNI <small style="color:#94a3b8;">(recto/verso en un fichier PDF ou image)</small></label>
                <label style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .9rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.45rem;cursor:pointer;font-size:.82rem;font-weight:600;color:#374151;width:fit-content;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span id="cni-file-label">Choisir un fichier</span>
                    <input type="file" name="national_id_file" accept=".pdf,image/jpeg,image/png"
                           style="display:none;" onchange="updateFileLabel(this,'cni-file-label')">
                </label>
                @error('national_id_file')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 6 : Photo + véhicule préférentiel ────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#ec4899" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="#ec4899"/><path d="M21 15l-5-5L5 21" stroke="#ec4899" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="form-head-title">Photo & Affectation</span>
    </div>
    <div class="form-body">
        <div class="form-grid form-grid-2">
            {{-- Photo --}}
            <div class="form-group">
                <label class="form-label">Photo du chauffeur</label>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div id="avatar-preview" style="width:64px;height:64px;border-radius:50%;overflow:hidden;background:#f1f5f9;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:2px solid #e2e8f0;">
                        @if($isEdit && $driver->avatar)
                            <img id="prev-img" src="{{ Storage::url($driver->avatar) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                        @else
                            <span id="prev-init" style="font-size:1.1rem;font-weight:700;color:#94a3b8;">?</span>
                            <img id="prev-img" src="" style="width:100%;height:100%;object-fit:cover;display:none;" alt="">
                        @endif
                    </div>
                    <label style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .9rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.45rem;cursor:pointer;font-size:.82rem;font-weight:600;color:#374151;transition:.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Choisir
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewDriverAvatar(this)">
                    </label>
                </div>
                <p style="font-size:.73rem;color:#94a3b8;margin:.3rem 0 0;">JPEG, PNG · max 3 Mo</p>
                @error('avatar')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Véhicule préférentiel --}}
            <div class="form-group">
                <label class="form-label">Véhicule préférentiel</label>
                <select name="preferred_vehicle_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(old('preferred_vehicle_id', $driver->preferred_vehicle_id ?? '') == $v->id)>
                        {{ $v->plate }} — {{ $v->brand }} {{ $v->model }}
                    </option>
                    @endforeach
                </select>
                @error('preferred_vehicle_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 7 : Statut (uniquement en modification) ──────────────────── --}}
@if($isEdit)
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#ef4444" stroke-width="2"/></svg>
        <span class="form-head-title">Statut du chauffeur</span>
    </div>
    <div class="form-body">
        <div class="form-grid form-grid-2">
            <div class="form-group">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select" id="driver-status-select" onchange="toggleSuspensionReason()">
                    @foreach(['active'=>'Actif','suspended'=>'Suspendu','on_leave'=>'En congé','terminated'=>'Licencié'] as $val=>$lbl)
                    <option value="{{ $val }}" @selected(old('status', $driver->status ?? 'active') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('status')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group" id="suspension-reason-group" style="{{ (old('status', $driver->status) === 'active') ? 'display:none;' : '' }}">
                <label class="form-label">Motif</label>
                <input type="text" name="suspension_reason" value="{{ old('suspension_reason', $driver->suspension_reason ?? '') }}" class="form-input" placeholder="Raison de la suspension / absence…">
                @error('suspension_reason')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>
<script>
function toggleSuspensionReason() {
    const sel = document.getElementById('driver-status-select');
    const grp = document.getElementById('suspension-reason-group');
    if (grp) grp.style.display = (sel.value === 'active') ? 'none' : '';
}
</script>
@endif

{{-- Notes ────────────────────────────────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#64748b" stroke-width="2"/></svg>
        <span class="form-head-title">Notes</span>
    </div>
    <div class="form-body">
        <textarea name="notes" class="form-textarea" placeholder="Observations, compétences particulières…">{{ old('notes', $driver->notes ?? '') }}</textarea>
        @error('notes')<p class="form-error">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Boutons ──────────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:.75rem;justify-content:flex-end;padding:.25rem 0 1rem;">
    <a href="{{ $isEdit ? route('drivers.show', $driver) : route('drivers.index') }}" class="btn btn-ghost">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2"/></svg>
        {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le chauffeur' }}
    </button>
</div>

<script>
function updateFileLabel(input, labelId) {
    const label = document.getElementById(labelId);
    if (label && input.files && input.files[0]) {
        label.textContent = input.files[0].name;
    }
}
function previewDriverAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        const img  = document.getElementById('prev-img');
        const init = document.getElementById('prev-init');
        img.src = e.target.result;
        img.style.display = 'block';
        if (init) init.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
