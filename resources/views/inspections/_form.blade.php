@php $isEdit = isset($inspection); @endphp

<style>
.form-section{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.1rem;}
.form-section-head{padding:.8rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.section-icon{width:28px;height:28px;border-radius:.4rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.form-section-title{font-size:.855rem;font-weight:700;color:#0f172a;}
.form-section-body{padding:1.1rem 1.25rem;}
.form-group{margin-bottom:.85rem;}
.form-label{font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.3rem;display:block;}
.form-input{width:100%;padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.83rem;background:#fff;color:#0f172a;outline:none;transition:border-color .15s;}
.form-input:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.08);}
.form-input.is-invalid{border-color:#ef4444;}
.invalid-msg{font-size:.73rem;color:#ef4444;margin-top:.2rem;}

/* ── Point de contrôle (3 boutons : OK / Attention / Critique) ── */
.ctrl-point{display:grid;grid-template-columns:1fr auto;gap:.6rem .85rem;align-items:center;padding:.6rem 0;border-bottom:1px solid #f8fafc;}
.ctrl-point:last-child{border-bottom:none;}
.ctrl-label{font-size:.825rem;font-weight:500;color:#374151;}
.ctrl-label small{display:block;font-size:.72rem;color:#94a3b8;font-weight:400;margin-top:.05rem;}
.ctrl-btns{display:flex;gap:.35rem;flex-shrink:0;}
.ctrl-btn{padding:.28rem .6rem;border-radius:.4rem;font-size:.72rem;font-weight:600;border:2px solid transparent;cursor:pointer;background:#f8fafc;color:#94a3b8;transition:all .15s;white-space:nowrap;}
.ctrl-btn:hover{border-color:#cbd5e1;}
.ctrl-btn.ok.active    {background:#f0fdf4;color:#059669;border-color:#86efac;}
.ctrl-btn.warn.active  {background:#fffbeb;color:#92400e;border-color:#fcd34d;}
.ctrl-btn.crit.active  {background:#fef2f2;color:#dc2626;border-color:#fca5a5;}
.ctrl-btn.na.active    {background:#f1f5f9;color:#64748b;border-color:#cbd5e1;}
.ctrl-note{grid-column:1/-1;padding-top:.15rem;display:none;}
.ctrl-note.visible{display:block;}

/* ── Niveau de carburant ── */
.fuel-track{width:100%;height:14px;background:#f1f5f9;border-radius:99px;position:relative;cursor:pointer;margin:.5rem 0;}
.fuel-fill-bar{height:100%;border-radius:99px;transition:width .2s,background .2s;}
.fuel-labels{display:flex;justify-content:space-between;font-size:.68rem;color:#94a3b8;}

/* ── Zone photos carrosserie ── */
.photo-thumb{width:90px;height:90px;border-radius:.55rem;object-fit:cover;border:1.5px solid #e2e8f0;}
.photo-delete-mark{opacity:.5;filter:grayscale(1);outline:2px solid #ef4444;}

/* ── Boutons de soumission ── */
.btn-submit{padding:.6rem 1.5rem;border-radius:.5rem;font-size:.875rem;font-weight:700;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;}
.btn-draft {padding:.55rem 1.2rem;border-radius:.45rem;font-size:.83rem;font-weight:600;background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;}
.btn-ghost {padding:.55rem 1.1rem;border-radius:.45rem;font-size:.83rem;font-weight:600;background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;}
</style>

@if($errors->any())
<div style="padding:.75rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.6rem;margin-bottom:1.1rem;font-size:.83rem;color:#dc2626;">
    <strong>Corrections nécessaires :</strong>
    <ul style="margin:.3rem 0 0 1rem;padding:0;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- ══ SECTION 1 : Contexte ══════════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#eff6ff;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5"/></svg>
        </div>
        <span class="form-section-title">Contexte du contrôle</span>
    </div>
    <div class="form-section-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Véhicule <span style="color:#ef4444;">*</span></label>
                <select name="vehicle_id" class="form-input {{ $errors->has('vehicle_id') ? 'is-invalid' : '' }}" required @if($isEdit) disabled @endif>
                    <option value="">— Sélectionner un véhicule —</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id', $isEdit ? $inspection->vehicle_id : $preVehicle?->id) == $v->id)>
                            {{ $v->plate }} — {{ $v->brand }} {{ $v->model }}
                        </option>
                    @endforeach
                </select>
                @if($isEdit)<input type="hidden" name="vehicle_id" value="{{ $inspection->vehicle_id }}">@endif
                @error('vehicle_id')<div class="invalid-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Chauffeur concerné</label>
                <select name="driver_id" class="form-input">
                    <option value="">— Aucun / Non applicable —</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" @selected(old('driver_id', $isEdit ? $inspection->driver_id : $preDriver?->id) == $d->id)>
                            {{ $d->full_name }} ({{ $d->matricule }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Type de contrôle <span style="color:#ef4444;">*</span></label>
                <select name="inspection_type" class="form-input {{ $errors->has('inspection_type') ? 'is-invalid' : '' }}" required>
                    <option value="departure" @selected(old('inspection_type', $isEdit ? $inspection->inspection_type : 'departure') === 'departure')>🚗 Départ</option>
                    <option value="return"    @selected(old('inspection_type', $isEdit ? $inspection->inspection_type : '') === 'return')>🏁 Retour</option>
                    <option value="routine"   @selected(old('inspection_type', $isEdit ? $inspection->inspection_type : '') === 'routine')>🔄 Routine</option>
                </select>
                @error('inspection_type')<div class="invalid-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Date & heure <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="inspected_at"
                       class="form-input {{ $errors->has('inspected_at') ? 'is-invalid' : '' }}"
                       value="{{ old('inspected_at', $isEdit ? $inspection->inspected_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
                @error('inspected_at')<div class="invalid-msg">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Lieu du contrôle</label>
                <input type="text" name="location" class="form-input" placeholder="ex: Parking siège, Garage central…"
                       value="{{ old('location', $isEdit ? $inspection->location : '') }}">
            </div>
        </div>
        <div class="form-group" style="max-width:200px;margin-bottom:0;">
            <label class="form-label">Kilométrage relevé</label>
            <div style="position:relative;">
                <input type="number" name="km" class="form-input" placeholder="ex: 45 800" min="0"
                       value="{{ old('km', $isEdit ? $inspection->km : '') }}"
                       style="padding-right:2.8rem;">
                <span style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);font-size:.75rem;color:#94a3b8;pointer-events:none;">km</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ SECTION 2 : Niveau de carburant ══════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#fff7ed;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M3 22V10l4-8h10l4 8v12" stroke="#f97316" stroke-width="2" stroke-linecap="round"/><path d="M3 10h18" stroke="#f97316" stroke-width="2" stroke-linecap="round"/><rect x="8" y="14" width="8" height="8" rx="1" stroke="#f97316" stroke-width="2"/></svg>
        </div>
        <span class="form-section-title">Niveau de carburant</span>
        <span id="fuel-display" style="margin-left:auto;font-size:.85rem;font-weight:700;color:#10b981;">
            {{ old('fuel_level_pct', $isEdit ? $inspection->fuel_level_pct : 50) ?? 50 }}%
        </span>
    </div>
    <div class="form-section-body" style="padding:.85rem 1.5rem;">
        <input type="hidden" name="fuel_level_pct" id="fuel-input"
               value="{{ old('fuel_level_pct', $isEdit ? $inspection->fuel_level_pct : 50) ?? 50 }}">
        <div class="fuel-track" id="fuel-track" onclick="setFuel(event)">
            <div class="fuel-fill-bar" id="fuel-bar"
                 style="width:{{ old('fuel_level_pct', $isEdit ? $inspection->fuel_level_pct : 50) ?? 50 }}%;background:#10b981;"></div>
        </div>
        <div class="fuel-labels">
            <span>Vide (0%)</span><span>1/4</span><span>1/2</span><span>3/4</span><span>Plein (100%)</span>
        </div>
        <div style="display:flex;gap:.5rem;margin-top:.65rem;flex-wrap:wrap;">
            @foreach([0=>'Vide',25=>'1/4',50=>'Moitié',75=>'3/4',100=>'Plein'] as $pct=>$lbl)
            <button type="button" onclick="setFuelPct({{ $pct }})"
                    style="padding:.28rem .65rem;border-radius:.4rem;font-size:.75rem;font-weight:600;border:1.5px solid #e2e8f0;background:#f8fafc;color:#374151;cursor:pointer;transition:all .15s;">
                {{ $lbl }}
            </button>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ SECTION 3 : Fluides moteur ════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#f0fdf4;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" stroke="#10b981" stroke-width="2"/><path d="M12 8v4l3 3" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <span class="form-section-title">Fluides moteur</span>
    </div>
    <div class="form-section-body">
        @php
        $fluids = [
            ['oil_level',         'Huile moteur',              'low=faible / medium=normal / high=plein', ['low'=>['Faible','crit'],'medium'=>['Normal','ok'],'high'=>['Plein','ok']]],
            ['coolant_level',     'Liquide de refroidissement','',                                        ['low'=>['Faible','crit'],'medium'=>['Normal','ok'],'high'=>['Plein','ok']]],
            ['brake_fluid_level', 'Liquide de frein',          '',                                        ['low'=>['Faible','crit'],'medium'=>['Normal','ok'],'high'=>['Plein','ok']]],
        ];
        @endphp
        @foreach($fluids as [$field, $label, $hint, $opts])
        @php $val = old($field, $isEdit ? $inspection->$field : null); @endphp
        <div class="ctrl-point">
            <div class="ctrl-label">{{ $label }}<small>{{ $hint }}</small></div>
            <div class="ctrl-btns">
                @foreach($opts as $optVal=>[$optLbl,$optClass])
                <button type="button"
                        class="ctrl-btn {{ $optClass }} {{ $val === $optVal ? 'active' : '' }}"
                        onclick="setCtrl('{{ $field }}','{{ $optVal }}',this)">
                    {{ $optLbl }}
                </button>
                @endforeach
                <input type="hidden" name="{{ $field }}" id="ctrl-{{ $field }}" value="{{ $val ?? '' }}">
            </div>
        </div>
        @endforeach

        {{-- Note huile + vidange --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:.75rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Note huile</label>
                <input type="text" name="oil_notes" class="form-input" placeholder="Observations sur l'huile…"
                       value="{{ old('oil_notes', $isEdit ? $inspection->oil_notes : '') }}">
            </div>
            <div>
                @php $oilChg = old('oil_change_status', $isEdit ? $inspection->oil_change_status : null); @endphp
                <div class="ctrl-point" style="border:none;padding:0;">
                    <div class="ctrl-label" style="margin-bottom:.35rem;">État vidange</div>
                    <div class="ctrl-btns">
                        <button type="button" class="ctrl-btn ok {{ $oilChg==='ok' ? 'active' : '' }}"         onclick="setCtrl('oil_change_status','ok',this)">OK</button>
                        <button type="button" class="ctrl-btn warn {{ $oilChg==='due_soon' ? 'active' : '' }}"  onclick="setCtrl('oil_change_status','due_soon',this)">Bientôt</button>
                        <button type="button" class="ctrl-btn crit {{ $oilChg==='overdue' ? 'active' : '' }}"   onclick="setCtrl('oil_change_status','overdue',this)">Dépassée</button>
                        <input type="hidden" name="oil_change_status" id="ctrl-oil_change_status" value="{{ $oilChg ?? '' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ SECTION 4 : Pneus ═════════════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#f8fafc;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#374151" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="#374151" stroke-width="2"/></svg>
        </div>
        <span class="form-section-title">Pneus</span>
    </div>
    <div class="form-section-body">
        @php $tire = old('tire_pressure', $isEdit ? $inspection->tire_pressure : null); @endphp
        <div class="ctrl-point" style="border:none;">
            <div class="ctrl-label">Pression des pneus</div>
            <div class="ctrl-btns">
                <button type="button" class="ctrl-btn crit {{ $tire==='low' ? 'active' : '' }}"   onclick="setCtrl('tire_pressure','low',this)">Basse</button>
                <button type="button" class="ctrl-btn warn {{ $tire==='medium' ? 'active' : '' }}" onclick="setCtrl('tire_pressure','medium',this)">Moyenne</button>
                <button type="button" class="ctrl-btn ok {{ $tire==='ok' ? 'active' : '' }}"      onclick="setCtrl('tire_pressure','ok',this)">Correcte</button>
                <input type="hidden" name="tire_pressure" id="ctrl-tire_pressure" value="{{ $tire ?? '' }}">
            </div>
        </div>
        <div class="form-group" style="margin-top:.6rem;margin-bottom:0;">
            <label class="form-label">Observations pneus</label>
            <input type="text" name="tire_notes" class="form-input" placeholder="Usure, crevaison, gonflage…"
                   value="{{ old('tire_notes', $isEdit ? $inspection->tire_notes : '') }}">
        </div>
    </div>
</div>

{{-- ══ SECTION 5 : Éclairage & Freinage ═════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#fefce8;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="2"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="#ca8a04" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <span class="form-section-title">Éclairage & Freinage</span>
    </div>
    <div class="form-section-body">
        @php
        $pairs = [
            ['lights_status', 'Éclairage (feux avant/arrière, clignotants, stop)', 'lights_notes', 'Détail des feux défaillants…'],
            ['brakes_status', 'Freinage (pédale, frein à main)', 'brakes_notes', 'Observations sur les freins…'],
        ];
        @endphp
        @foreach($pairs as [$field, $lbl, $noteField, $notePlaceholder])
        @php $val = old($field, $isEdit ? $inspection->$field : null); @endphp
        <div class="ctrl-point">
            <div class="ctrl-label">{{ $lbl }}</div>
            <div class="ctrl-btns">
                <button type="button" class="ctrl-btn ok {{ $val==='ok' ? 'active' : '' }}"           onclick="setCtrl('{{ $field }}','ok',this)">OK</button>
                <button type="button" class="ctrl-btn warn {{ $val==='minor_issue' ? 'active' : '' }}" onclick="setCtrl('{{ $field }}','minor_issue',this)">Anomalie</button>
                <button type="button" class="ctrl-btn crit {{ $val==='critical' ? 'active' : '' }}"    onclick="setCtrl('{{ $field }}','critical',this)">Critique</button>
                <input type="hidden" name="{{ $field }}" id="ctrl-{{ $field }}" value="{{ $val ?? '' }}">
            </div>
        </div>
        <div class="form-group" style="margin-top:.25rem;margin-bottom:.85rem;">
            <input type="text" name="{{ $noteField }}" class="form-input" placeholder="{{ $notePlaceholder }}"
                   value="{{ old($noteField, $isEdit ? $inspection->$noteField : '') }}">
        </div>
        @endforeach
    </div>
</div>

{{-- ══ SECTION 6 : Documents & Conformité légale ═════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#f0f0ff;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="#6366f1" stroke-width="2"/><polyline points="14,2 14,8 20,8" stroke="#6366f1" stroke-width="2"/></svg>
        </div>
        <span class="form-section-title">Documents & Conformité légale</span>
    </div>
    <div class="form-section-body">
        {{-- Carte grise --}}
        @php $reg = old('registration_present', $isEdit ? ($inspection->registration_present ? '1' : '0') : null); @endphp
        <div class="ctrl-point">
            <div class="ctrl-label">Carte grise</div>
            <div class="ctrl-btns">
                <button type="button" class="ctrl-btn ok {{ $reg==='1' ? 'active' : '' }}"   onclick="setCtrlBool('registration_present','1',this)">Présente</button>
                <button type="button" class="ctrl-btn crit {{ $reg==='0' ? 'active' : '' }}" onclick="setCtrlBool('registration_present','0',this)">Absente</button>
                <input type="hidden" name="registration_present" id="ctrl-registration_present" value="{{ $reg ?? '' }}">
            </div>
        </div>

        {{-- Assurance --}}
        @php $ins = old('insurance_status', $isEdit ? $inspection->insurance_status : null); @endphp
        <div class="ctrl-point">
            <div class="ctrl-label">Assurance</div>
            <div class="ctrl-btns">
                <button type="button" class="ctrl-btn ok {{ $ins==='present' ? 'active' : '' }}"  onclick="setCtrl('insurance_status','present',this);toggleDate('ins-date','present')">À jour</button>
                <button type="button" class="ctrl-btn crit {{ $ins==='expired' ? 'active' : '' }}" onclick="setCtrl('insurance_status','expired',this);toggleDate('ins-date','expired')">Expirée</button>
                <button type="button" class="ctrl-btn crit {{ $ins==='absent' ? 'active' : '' }}"  onclick="setCtrl('insurance_status','absent',this);toggleDate('ins-date','absent')">Absente</button>
                <input type="hidden" name="insurance_status" id="ctrl-insurance_status" value="{{ $ins ?? '' }}">
            </div>
        </div>
        <div id="ins-date" style="padding:.4rem 0 .65rem;display:{{ $ins ? 'block' : 'none' }};">
            <label class="form-label" style="font-size:.74rem;">Date d'expiration assurance</label>
            <input type="date" name="insurance_expiry" class="form-input" style="max-width:180px;"
                   value="{{ old('insurance_expiry', $isEdit ? $inspection->insurance_expiry?->format('Y-m-d') : '') }}">
        </div>

        {{-- Visite technique --}}
        @php $ct = old('technical_control_status', $isEdit ? $inspection->technical_control_status : null); @endphp
        <div class="ctrl-point">
            <div class="ctrl-label">Visite technique</div>
            <div class="ctrl-btns">
                <button type="button" class="ctrl-btn ok {{ $ct==='present' ? 'active' : '' }}"  onclick="setCtrl('technical_control_status','present',this);toggleDate('ct-date','present')">À jour</button>
                <button type="button" class="ctrl-btn crit {{ $ct==='expired' ? 'active' : '' }}" onclick="setCtrl('technical_control_status','expired',this);toggleDate('ct-date','expired')">Expirée</button>
                <button type="button" class="ctrl-btn crit {{ $ct==='absent' ? 'active' : '' }}"  onclick="setCtrl('technical_control_status','absent',this);toggleDate('ct-date','absent')">Absente</button>
                <input type="hidden" name="technical_control_status" id="ctrl-technical_control_status" value="{{ $ct ?? '' }}">
            </div>
        </div>
        <div id="ct-date" style="padding:.4rem 0 0;display:{{ $ct ? 'block' : 'none' }};">
            <label class="form-label" style="font-size:.74rem;">Date d'expiration visite technique</label>
            <input type="date" name="technical_control_expiry" class="form-input" style="max-width:180px;"
                   value="{{ old('technical_control_expiry', $isEdit ? $inspection->technical_control_expiry?->format('Y-m-d') : '') }}">
        </div>
    </div>
</div>

{{-- ══ SECTION 7 : Carrosserie & Observations ═══════════════════════════ --}}
<div class="form-section">
    <div class="form-section-head">
        <div class="section-icon" style="background:#fef2f2;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#ef4444" stroke-width="2"/></svg>
        </div>
        <span class="form-section-title">Carrosserie & Observations générales</span>
    </div>
    <div class="form-section-body">
        <div class="form-group">
            <label class="form-label">Dommages carrosserie / vitres</label>
            <textarea name="body_notes" class="form-input" rows="2"
                      placeholder="Rayures, bosses, impacts sur vitres, déformations…">{{ old('body_notes', $isEdit ? $inspection->body_notes : '') }}</textarea>
        </div>
        {{-- ── Zone d'upload photos carrosserie ── --}}
        <div class="form-group">
            <label class="form-label">Photos carrosserie / dégâts</label>

            {{-- Photos existantes (mode édition) --}}
            @if($isEdit && !empty($inspection->body_photos))
            <div id="existing-photos" style="display:flex;flex-wrap:wrap;gap:.65rem;margin-bottom:.85rem;">
                @foreach($inspection->body_photos as $photo)
                <div style="position:relative;width:90px;height:90px;border-radius:.55rem;overflow:hidden;border:1.5px solid #e2e8f0;" id="photo-wrap-{{ $loop->index }}">
                    <img src="{{ Storage::url($photo) }}" alt="Photo carrosserie"
                         style="width:100%;height:100%;object-fit:cover;">
                    {{-- Case à cocher pour supprimer la photo --}}
                    <label title="Supprimer cette photo"
                           style="position:absolute;top:4px;right:4px;width:22px;height:22px;background:rgba(0,0,0,.55);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                        <input type="checkbox" name="delete_photos[]" value="{{ $photo }}"
                               style="display:none;" onchange="markDeletePhoto(this, {{ $loop->index }})">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24">
                            <path d="M18 6L6 18M6 6l12 12" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </label>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Zone de drop / sélection fichiers --}}
            <div id="photo-drop-zone"
                 style="border:2px dashed #e2e8f0;border-radius:.65rem;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;"
                 onclick="document.getElementById('photo-input').click()"
                 ondragover="event.preventDefault();this.style.borderColor='#10b981';this.style.background='#f0fdf4';"
                 ondragleave="this.style.borderColor='#e2e8f0';this.style.background='';"
                 ondrop="handlePhotoDrop(event)">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" style="margin:0 auto .5rem;display:block;color:#94a3b8;">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <polyline points="17,8 12,3 7,8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <p style="font-size:.8rem;color:#64748b;margin:0;">
                    <strong style="color:#374151;">Cliquer pour ajouter des photos</strong> ou glisser-déposer<br>
                    <span style="font-size:.73rem;color:#94a3b8;">JPEG, PNG, WebP — max 5 Mo par photo — 10 photos max</span>
                </p>
                <input type="file" id="photo-input" name="body_photos_upload[]"
                       accept="image/jpeg,image/png,image/webp"
                       multiple style="display:none;"
                       onchange="previewPhotos(this.files)">
            </div>

            {{-- Prévisualisation des nouveaux fichiers sélectionnés --}}
            <div id="photo-preview" style="display:flex;flex-wrap:wrap;gap:.65rem;margin-top:.65rem;"></div>
            @error('body_photos_upload')<div class="invalid-msg">{{ $message }}</div>@enderror
            @error('body_photos_upload.*')<div class="invalid-msg">{{ $message }}</div>@enderror
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Observations générales</label>
            <textarea name="general_observations" class="form-input" rows="3"
                      placeholder="Remarques complémentaires, comportement du véhicule, bruits anormaux…">{{ old('general_observations', $isEdit ? $inspection->general_observations : '') }}</textarea>
        </div>
    </div>
</div>

{{-- ══ Score de complétion (live) ════════════════════════════════════════ --}}
<div id="completion-bar" style="background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;padding:.85rem 1.25rem;margin-bottom:1.1rem;display:flex;align-items:center;gap:1rem;">
    <div style="flex:1;">
        <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#374151;font-weight:600;margin-bottom:.3rem;">
            <span>Complétion de la fiche</span>
            <span id="completion-pct">0%</span>
        </div>
        <div style="height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
            <div id="completion-fill" style="height:100%;border-radius:99px;background:#10b981;width:0%;transition:width .3s;"></div>
        </div>
    </div>
    <div id="completion-warning" style="font-size:.78rem;color:#d97706;display:none;">
        ⚠ Certains points clés ne sont pas renseignés
    </div>
</div>

{{-- Boutons --}}
<div style="display:flex;gap:.75rem;align-items:center;">
    <a href="{{ $isEdit ? route('inspections.show', $inspection) : route('inspections.index') }}" class="btn-ghost">
        Annuler
    </a>
    <button type="submit" name="action" value="draft" class="btn-draft">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2"/></svg>
        Brouillon
    </button>
    <button type="submit" name="action" value="submit" class="btn-submit" style="margin-left:auto;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
        {{ $isEdit ? 'Mettre à jour' : 'Soumettre la fiche' }}
    </button>
</div>

<script>
// ── Boutons de contrôle ────────────────────────────────────────────────────
function setCtrl(field, val, btn) {
    const group = btn.closest('.ctrl-btns');
    group.querySelectorAll('.ctrl-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('ctrl-' + field).value = val;
    updateCompletion();
}

function setCtrlBool(field, val, btn) {
    const group = btn.closest('.ctrl-btns');
    group.querySelectorAll('.ctrl-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('ctrl-' + field).value = val;
    updateCompletion();
}

// ── Dates optionnelles ─────────────────────────────────────────────────────
function toggleDate(id, val) {
    document.getElementById(id).style.display = val ? 'block' : 'none';
}

// ── Jauge carburant ────────────────────────────────────────────────────────
function setFuel(e) {
    const track = document.getElementById('fuel-track');
    const rect  = track.getBoundingClientRect();
    const pct   = Math.round(Math.min(100, Math.max(0, (e.clientX - rect.left) / rect.width * 100)));
    applyFuel(pct);
}

function setFuelPct(pct) { applyFuel(pct); }

function applyFuel(pct) {
    document.getElementById('fuel-input').value = pct;
    const bar   = document.getElementById('fuel-bar');
    const color = pct >= 50 ? '#10b981' : pct >= 25 ? '#d97706' : '#ef4444';
    bar.style.width      = pct + '%';
    bar.style.background = color;
    document.getElementById('fuel-display').textContent = pct + '%';
    document.getElementById('fuel-display').style.color = color;
}

// ── Score de complétion ────────────────────────────────────────────────────
const KEY_FIELDS = ['oil_level','coolant_level','brake_fluid_level','tire_pressure',
                    'insurance_status','technical_control_status','registration_present',
                    'oil_change_status','lights_status','brakes_status'];

function updateCompletion() {
    const filled = KEY_FIELDS.filter(f => {
        const el = document.getElementById('ctrl-' + f);
        return el && el.value !== '';
    }).length;
    const pct = Math.round(filled / KEY_FIELDS.length * 100);
    document.getElementById('completion-pct').textContent = pct + '%';
    document.getElementById('completion-fill').style.width = pct + '%';
    const fill = document.getElementById('completion-fill');
    fill.style.background = pct >= 80 ? '#10b981' : pct >= 40 ? '#d97706' : '#ef4444';
    document.getElementById('completion-warning').style.display = pct < 80 ? 'flex' : 'none';
}

document.addEventListener('DOMContentLoaded', updateCompletion);

// ── Gestion des photos carrosserie ─────────────────────────────────────────

/** Prévisualisation des nouveaux fichiers sélectionnés */
function previewPhotos(files) {
    const container = document.getElementById('photo-preview');
    container.innerHTML = '';
    if (!files || files.length === 0) return;

    Array.from(files).forEach((file, i) => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.style.cssText = 'position:relative;width:90px;height:90px;border-radius:.55rem;overflow:hidden;border:1.5px solid #10b981;';
            wrap.innerHTML = `
                <img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,.45);font-size:.63rem;color:#fff;text-align:center;padding:.15rem 0;">Nouveau</div>
            `;
            container.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}

/** Drag & drop vers la zone d'upload */
function handlePhotoDrop(e) {
    e.preventDefault();
    const drop = e.currentTarget;
    drop.style.borderColor = '#e2e8f0';
    drop.style.background  = '';
    const input = document.getElementById('photo-input');
    // Transférer les fichiers vers l'input
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    input.files = dt.files;
    previewPhotos(input.files);
}

/** Marque une photo existante pour suppression (style visuel) */
function markDeletePhoto(checkbox, index) {
    const wrap = document.getElementById('photo-wrap-' + index);
    if (!wrap) return;
    if (checkbox.checked) {
        wrap.style.opacity = '.4';
        wrap.style.outline = '2px solid #ef4444';
        wrap.style.borderRadius = '.55rem';
    } else {
        wrap.style.opacity = '1';
        wrap.style.outline = '';
    }
}
</script>
