{{--
  Formulaire partagé véhicules — utilisé par create.blade.php et edit.blade.php
  Variables attendues : $vehicle (peut être null pour create), $drivers (collection)
--}}
@php $isEdit = isset($vehicle) && $vehicle->exists; @endphp

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
.form-textarea{resize:vertical;min-height:80px;}
.form-error{font-size:.75rem;color:#dc2626;margin-top:.2rem;}
.btn{padding:.55rem 1.1rem;border-radius:.45rem;font-size:.85rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.radio-group{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.5rem;}
.radio-card{position:relative;}
.radio-card input{position:absolute;opacity:0;width:0;height:0;}
.radio-card label{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.25rem;padding:.6rem .5rem;border:1.5px solid #e2e8f0;border-radius:.5rem;cursor:pointer;font-size:.78rem;font-weight:600;color:#64748b;transition:.15s;text-align:center;}
.radio-card input:checked + label{border-color:#10b981;background:#f0fdf4;color:#047857;}
.radio-card label:hover{border-color:#94a3b8;}
</style>

{{-- Section 1 : Identité ──────────────────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#10b981" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#10b981" stroke-width="1.5"/></svg>
        <span class="form-head-title">Identité du véhicule</span>
    </div>
    <div class="form-body">
        <div class="form-grid form-grid-3">
            <div class="form-group">
                <label class="form-label">Marque <span class="req">*</span></label>
                <input type="text" name="brand" value="{{ old('brand', $vehicle->brand ?? '') }}" class="form-input @error('brand') border-red-400 @enderror" placeholder="Toyota, Peugeot…">
                @error('brand')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Modèle <span class="req">*</span></label>
                <input type="text" name="model" value="{{ old('model', $vehicle->model ?? '') }}" class="form-input" placeholder="Hilux, 308…">
                @error('model')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Immatriculation <span class="req">*</span></label>
                <input type="text" name="plate" value="{{ old('plate', $vehicle->plate ?? '') }}" class="form-input" placeholder="AB 1234 CI" style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
                @error('plate')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Année <span class="req">*</span></label>
                <input type="number" name="year" value="{{ old('year', $vehicle->year ?? date('Y')) }}" class="form-input" min="1990" max="{{ date('Y') + 1 }}">
                @error('year')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Couleur</label>
                <input type="text" name="color" value="{{ old('color', $vehicle->color ?? '') }}" class="form-input" placeholder="Blanc, Noir…">
                @error('color')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">N° châssis (VIN)</label>
                <input type="text" name="vin" value="{{ old('vin', $vehicle->vin ?? '') }}" class="form-input" placeholder="17 caractères" maxlength="17" style="font-family:monospace;" oninput="this.value=this.value.toUpperCase()">
                @error('vin')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 2 : Caractéristiques techniques ──────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke="#6366f1" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="#6366f1" stroke-width="2"/></svg>
        <span class="form-head-title">Caractéristiques techniques</span>
    </div>
    <div class="form-body">
        {{-- Type véhicule --}}
        <div class="form-group" style="margin-bottom:1rem;">
            <label class="form-label">Type de véhicule <span class="req">*</span></label>
            <div class="radio-group">
                @foreach(['sedan'=>['Berline','🚗'],'suv'=>['SUV','🚙'],'van'=>['Van','🚐'],'pickup'=>['Pick-up','🛻'],'truck'=>['Camion','🚛'],'city'=>['Citadine','🚘'],'motorcycle'=>['Moto','🏍️']] as $val => [$lbl,$icon])
                <div class="radio-card">
                    <input type="radio" name="vehicle_type" id="type_{{ $val }}" value="{{ $val }}" @checked(old('vehicle_type', $vehicle->vehicle_type ?? 'sedan') === $val)>
                    <label for="type_{{ $val }}">{{ $icon }}<span>{{ $lbl }}</span></label>
                </div>
                @endforeach
            </div>
            @error('vehicle_type')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-grid form-grid-3">
            <div class="form-group">
                <label class="form-label">Carburant <span class="req">*</span></label>
                <select name="fuel_type" class="form-select">
                    @foreach(['diesel'=>'Diesel','gasoline'=>'Essence','hybrid'=>'Hybride','electric'=>'Électrique','lpg'=>'GPL'] as $val=>$lbl)
                    <option value="{{ $val }}" @selected(old('fuel_type', $vehicle->fuel_type ?? 'diesel') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('fuel_type')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Catégorie permis requis <span class="req">*</span></label>
                <select name="license_category" class="form-select">
                    @foreach(['A','B','C','D','E','BE','CE'] as $cat)
                    <option value="{{ $cat }}" @selected(old('license_category', $vehicle->license_category ?? 'B') === $cat)>Permis {{ $cat }}</option>
                    @endforeach
                </select>
                @error('license_category')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nombre de places <span class="req">*</span></label>
                <input type="number" name="seats" value="{{ old('seats', $vehicle->seats ?? 5) }}" class="form-input" min="1" max="60">
                @error('seats')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Charge utile (kg)</label>
                <input type="number" name="payload_kg" value="{{ old('payload_kg', $vehicle->payload_kg ?? '') }}" class="form-input" min="0" placeholder="Pour utilitaires">
                @error('payload_kg')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Kilométrage actuel <span class="req">*</span></label>
                <div style="position:relative;">
                    <input type="number" name="km_current" value="{{ old('km_current', $vehicle->km_current ?? 0) }}" class="form-input" min="0" style="padding-right:2.5rem;">
                    <span style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);font-size:.75rem;color:#94a3b8;">km</span>
                </div>
                @error('km_current')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Prochain entretien (km)</label>
                <div style="position:relative;">
                    <input type="number" name="km_next_service" value="{{ old('km_next_service', $vehicle->km_next_service ?? '') }}" class="form-input" min="0" style="padding-right:2.5rem;">
                    <span style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);font-size:.75rem;color:#94a3b8;">km</span>
                </div>
                @error('km_next_service')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 3 : Informations financières & assurance ─────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2" stroke="#d97706" stroke-width="2"/><path d="M2 10h20" stroke="#d97706" stroke-width="2"/></svg>
        <span class="form-head-title">Financier &amp; Assurance</span>
    </div>
    <div class="form-body">
        <div class="form-grid form-grid-2">
            <div class="form-group">
                <label class="form-label">Prix d'acquisition (FCFA)</label>
                <input type="number" name="purchase_price" value="{{ old('purchase_price', $vehicle->purchase_price ?? '') }}" class="form-input" min="0" step="1000" placeholder="0">
                @error('purchase_price')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Date d'acquisition</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', isset($vehicle) && $vehicle->purchase_date ? $vehicle->purchase_date->format('Y-m-d') : '') }}" class="form-input">
                @error('purchase_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Compagnie d'assurance</label>
                <input type="text" name="insurance_company" value="{{ old('insurance_company', $vehicle->insurance_company ?? '') }}" class="form-input" placeholder="NSIA, SANLAM…">
                @error('insurance_company')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">N° police d'assurance</label>
                <input type="text" name="insurance_policy_number" value="{{ old('insurance_policy_number', $vehicle->insurance_policy_number ?? '') }}" class="form-input" style="font-family:monospace;">
                @error('insurance_policy_number')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</div>

{{-- Section 4 : Photo de profil ──────────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#ec4899" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="#ec4899"/><path d="M21 15l-5-5L5 21" stroke="#ec4899" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="form-head-title">Photo du véhicule</span>
    </div>
    <div class="form-body">
        <div style="display:flex;align-items:center;gap:1.25rem;">
            {{-- Aperçu --}}
            <div id="photo-preview" style="width:120px;height:90px;border-radius:.5rem;overflow:hidden;background:#f1f5f9;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:2px dashed #e2e8f0;">
                @if($isEdit && $vehicle->profilePhoto)
                    <img id="preview-img" src="{{ Storage::url($vehicle->profilePhoto->file_path) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                @else
                    <div id="preview-placeholder" style="text-align:center;">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" style="margin:0 auto;color:#cbd5e1;"><path d="M3 17l6-6 4 4 3-3 5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="9" r="2" stroke="currentColor" stroke-width="1.5"/><rect x="2" y="3" width="20" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/></svg>
                        <p style="font-size:.68rem;color:#94a3b8;margin:.3rem 0 0;">Aucune photo</p>
                    </div>
                    <img id="preview-img" src="" style="width:100%;height:100%;object-fit:cover;display:none;" alt="">
                @endif
            </div>
            <div style="flex:1;">
                <label style="display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1rem;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:.45rem;cursor:pointer;font-size:.82rem;font-weight:600;color:#374151;transition:.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Choisir une photo
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewVehiclePhoto(this)">
                </label>
                <p style="margin:.5rem 0 0;font-size:.75rem;color:#94a3b8;">JPEG, PNG, WebP · max 5 Mo</p>
                @if($isEdit && $vehicle->profilePhoto)
                <p style="margin:.3rem 0 0;font-size:.75rem;color:#d97706;">Une nouvelle photo remplacera l'ancienne.</p>
                @endif
            </div>
        </div>
        @error('photo')<p class="form-error" style="margin-top:.5rem;">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Section 5 : Notes ────────────────────────────────────────────────── --}}
<div class="form-card">
    <div class="form-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#64748b" stroke-width="2"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="form-head-title">Notes</span>
    </div>
    <div class="form-body">
        <textarea name="notes" class="form-textarea" placeholder="Observations, remarques…" style="min-height:90px;">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
        @error('notes')<p class="form-error">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Boutons ──────────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:.75rem;justify-content:flex-end;padding:.25rem 0 1rem;">
    <a href="{{ $isEdit ? route('vehicles.show', $vehicle) : route('vehicles.index') }}" class="btn btn-ghost">Annuler</a>
    <button type="submit" class="btn btn-primary">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2"/></svg>
        {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le véhicule' }}
    </button>
</div>

<script>
function previewVehiclePhoto(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        const img = document.getElementById('preview-img');
        const ph  = document.getElementById('preview-placeholder');
        img.src = e.target.result;
        img.style.display = 'block';
        if (ph) ph.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
