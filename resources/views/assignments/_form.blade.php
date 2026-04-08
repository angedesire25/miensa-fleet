{{--
  Formulaire partagé : create & edit affectations
  Variables attendues :
    $vehicles   — Collection Vehicle::active()
    $drivers    — Collection Driver::active()
    $preVehicle — Vehicle|null (pré-sélection depuis fiche véhicule)
    $preDriver  — Driver|null  (pré-sélection depuis fiche chauffeur)
    $assignment — Assignment|null (mode édition)
--}}
@php
    $isEdit = isset($assignment);
    $typeMap = [
        'mission' => 'Mission',
        'daily' => 'Journée',
        'permanent' => 'Permanente',
        'replacement' => 'Remplacement',
        'trial' => 'Essai',
    ];
    $typeIcons = ['mission' => '🗺️', 'daily' => '📅', 'permanent' => '🔄', 'replacement' => '🔁', 'trial' => '🧪'];
    $typeColors = [
        'mission' => '#6366f1',
        'daily' => '#3b82f6',
        'permanent' => '#10b981',
        'replacement' => '#d97706',
        'trial' => '#ec4899',
    ];
    $typeDesc = [
        'mission' => 'Déplacement ponctuel avec destination définie.',
        'daily' => 'Mise à disposition pour la journée.',
        'permanent' => 'Affectation continue à long terme.',
        'replacement' => 'Remplacement temporaire d\'un véhicule.',
        'trial' => 'Essai ou évaluation du véhicule.',
    ];
    $vTypeIcons = ['city' => '🏙️', 'sedan' => '🚗', 'suv' => '🚙', 'pickup' => '🛻', 'van' => '🚐', 'truck' => '🚚'];
@endphp

<style>
    .form-section {
        background: #fff;
        border-radius: .75rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    .form-section-head {
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .form-section-title {
        font-size: .875rem;
        font-weight: 700;
        color: #0f172a;
    }

    .form-section-body {
        padding: 1.25rem 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        font-size: .795rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .3rem;
        display: block;
    }

    .form-hint {
        font-size: .74rem;
        color: #94a3b8;
        margin-top: .2rem;
    }

    .form-input {
        width: 100%;
        padding: .52rem .75rem;
        border: 1.5px solid #e2e8f0;
        border-radius: .45rem;
        font-size: .84rem;
        background: #fff;
        color: #0f172a;
        outline: none;
        transition: border-color .15s;
    }

    .form-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, .08);
    }

    .form-input.is-invalid {
        border-color: #ef4444;
    }

    .invalid-msg {
        font-size: .74rem;
        color: #ef4444;
        margin-top: .2rem;
    }

    .type-card {
        border: 2px solid #e2e8f0;
        border-radius: .6rem;
        padding: .75rem 1rem;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        user-select: none;
    }

    .type-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .type-card.selected {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .type-radio {
        display: none;
    }

    .driver-card,
    .vehicle-card {
        border: 2px solid #e2e8f0;
        border-radius: .55rem;
        padding: .6rem .85rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: .65rem;
        transition: border-color .15s;
        margin-bottom: .4rem;
    }

    .driver-card:hover,
    .vehicle-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .driver-card.selected,
    .vehicle-card.selected {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .sel-input {
        display: none;
    }

    .btn-submit {
        padding: .6rem 1.5rem;
        border-radius: .5rem;
        font-size: .875rem;
        font-weight: 700;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: opacity .15s;
    }

    .btn-submit:hover {
        opacity: .9;
    }

    .btn-ghost {
        padding: .55rem 1.1rem;
        border-radius: .45rem;
        font-size: .83rem;
        font-weight: 600;
        background: #f8fafc;
        color: #374151;
        border: 1.5px solid #e2e8f0;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .btn-ghost:hover {
        background: #f1f5f9;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: .45rem .75rem .45rem 2rem;
        border: 1.5px solid #e2e8f0;
        border-radius: .4rem;
        font-size: .82rem;
        outline: none;
    }

    .search-box input:focus {
        border-color: #10b981;
    }

    .search-box svg {
        position: absolute;
        left: .55rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #94a3b8;
    }
</style>

{{-- ── Erreurs générales ──────────────────────────────────────────────────── --}}
@if ($errors->any())
    <div
        style="padding:.75rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:.6rem;margin-bottom:1.25rem;font-size:.83rem;color:#dc2626;">
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul style="margin:.35rem 0 0 1rem;padding:0;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Section 1 : Type d'affectation ──────────────────────────────────────── --}}
@if (!$isEdit)
    <div class="form-section">
        <div class="form-section-head">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="16" rx="2" stroke="#6366f1" stroke-width="2" />
                <path d="M8 2v4M16 2v4M3 10h18" stroke="#6366f1" stroke-width="2" stroke-linecap="round" />
            </svg>
            <span class="form-section-title">Type d'affectation</span>
        </div>
        <div class="form-section-body">
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.65rem;">
                @foreach ($typeMap as $val => $label)
                    <label
                        class="type-card {{ old('type', $preVehicle || $preDriver ? 'mission' : '') === $val ? 'selected' : '' }}"
                        id="type-label-{{ $val }}">
                        <input type="radio" name="type" value="{{ $val }}" class="type-radio"
                            @checked(old('type', 'mission') === $val) onchange="selectType('{{ $val }}')">
                        <div>
                            <div style="font-size:1.1rem;margin-bottom:.25rem;">{{ $typeIcons[$val] }}</div>
                            <div style="font-size:.8rem;font-weight:700;color:#0f172a;">{{ $label }}</div>
                            <div style="font-size:.72rem;color:#64748b;margin-top:.15rem;">{{ $typeDesc[$val] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('type')
                <div class="invalid-msg" style="margin-top:.5rem;">{{ $message }}</div>
            @enderror
        </div>
    </div>
@else
    <input type="hidden" name="type" value="{{ old('type', $assignment->type) }}">
@endif

{{-- ── Section 2 : Chauffeur ────────────────────────────────────────────────── --}}
@if (!$isEdit)
    <div class="form-section">
        <div class="form-section-head">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="8" r="4" stroke="#10b981" stroke-width="2" />
                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="#10b981" stroke-width="2" stroke-linecap="round" />
            </svg>
            <span class="form-section-title">Chauffeur <span style="color:#ef4444;">*</span></span>
        </div>
        <div class="form-section-body">
            <div class="form-group search-box">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" />
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" />
                </svg>
                <input type="text" id="driver-search" placeholder="Rechercher un chauffeur…"
                    oninput="filterDrivers(this.value)">
            </div>
            <div id="driver-list" style="max-height:240px;overflow-y:auto;">
                @foreach ($drivers as $d)
                    <label class="driver-card {{ old('driver_id', $preDriver?->id) == $d->id ? 'selected' : '' }}"
                        id="driver-card-{{ $d->id }}">
                        <input type="radio" name="driver_id" value="{{ $d->id }}" class="sel-input"
                            @checked(old('driver_id', $preDriver?->id) == $d->id) onchange="selectDriver({{ $d->id }})">
                        @if ($d->avatar)
                            <img src="{{ Storage::url($d->avatar) }}"
                                style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                                alt="">
                        @else
                            <div
                                style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($d->full_name, 0, 2)) }}
                            </div>
                        @endif
                        <div style="flex:1;">
                            <div style="font-weight:600;font-size:.84rem;color:#0f172a;">{{ $d->full_name }}</div>
                            <div style="font-size:.74rem;color:#94a3b8;">{{ $d->matricule }} ·
                                {{ implode(', ', (array) $d->license_categories) }}</div>
                        </div>
                        @if ($d->activeAssignment)
                            <span
                                style="font-size:.7rem;background:#fffbeb;color:#92400e;padding:.15rem .45rem;border-radius:99px;font-weight:600;flex-shrink:0;">En
                                mission</span>
                        @endif
                    </label>
                @endforeach
            </div>
            @error('driver_id')
                <div class="invalid-msg" style="margin-top:.4rem;">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endif

{{-- ── Section 3 : Véhicule ─────────────────────────────────────────────────── --}}
@if (!$isEdit)
    <div class="form-section">
        <div class="form-section-head">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" />
                <circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5" />
                <circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5" />
            </svg>
            <span class="form-section-title">Véhicule <span style="color:#ef4444;">*</span></span>
        </div>
        <div class="form-section-body">
            <div class="form-group search-box">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" />
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" />
                </svg>
                <input type="text" id="vehicle-search" placeholder="Rechercher par plaque, marque…"
                    oninput="filterVehicles(this.value)">
            </div>
            <div id="vehicle-list" style="max-height:240px;overflow-y:auto;">
                @foreach ($vehicles as $v)
                    <label class="vehicle-card {{ old('vehicle_id', $preVehicle?->id) == $v->id ? 'selected' : '' }}"
                        id="vehicle-card-{{ $v->id }}">
                        <input type="radio" name="vehicle_id" value="{{ $v->id }}" class="sel-input"
                            @checked(old('vehicle_id', $preVehicle?->id) == $v->id) onchange="selectVehicle({{ $v->id }})">
                        @if ($v->profilePhoto)
                            <img src="{{ Storage::url($v->profilePhoto->path) }}"
                                style="width:44px;height:34px;border-radius:.3rem;object-fit:cover;flex-shrink:0;"
                                alt="">
                        @else
                            <div
                                style="width:44px;height:34px;border-radius:.3rem;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                                    <path d="M3 17h2l1-3h12l1 3h2" stroke="#cbd5e1" stroke-width="2" />
                                    <circle cx="7.5" cy="18.5" r="1.5" stroke="#cbd5e1"
                                        stroke-width="1.5" />
                                    <circle cx="16.5" cy="18.5" r="1.5" stroke="#cbd5e1"
                                        stroke-width="1.5" />
                                </svg>
                            </div>
                        @endif
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:.5rem;">
                                <span
                                    style="font-family:monospace;font-size:.82rem;font-weight:700;background:#f1f5f9;padding:.1rem .4rem;border-radius:.25rem;">{{ $v->plate }}</span>
                                <span style="font-size:.82rem;color:#374151;">{{ $v->brand }}
                                    {{ $v->model }}</span>
                            </div>
                            <div style="font-size:.74rem;color:#94a3b8;margin-top:.1rem;">{{ $v->year }} ·
                                {{ number_format($v->km_current ?? 0) }} km</div>
                        </div>
                        @if ($v->status === 'on_mission')
                            <span
                                style="font-size:.7rem;background:#eff6ff;color:#1d4ed8;padding:.15rem .45rem;border-radius:99px;font-weight:600;flex-shrink:0;">En
                                mission</span>
                        @elseif($v->status !== 'available')
                            <span
                                style="font-size:.7rem;background:#f8fafc;color:#64748b;padding:.15rem .45rem;border-radius:99px;font-weight:600;flex-shrink:0;">Indisponible</span>
                        @endif
                    </label>
                @endforeach
            </div>
            @error('vehicle_id')
                <div class="invalid-msg" style="margin-top:.4rem;">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endif

{{-- ── Section 4 : Dates & Destination ─────────────────────────────────────── --}}
<div class="form-section">
    <div class="form-section-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="16" rx="2" stroke="#d97706" stroke-width="2" />
            <path d="M8 2v4M16 2v4M3 10h18" stroke="#d97706" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span class="form-section-title">Dates & destination</span>
    </div>
    <div class="form-section-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Date et heure de départ <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="datetime_start"
                    class="form-input {{ $errors->has('datetime_start') ? 'is-invalid' : '' }}"
                    value="{{ old('datetime_start', $isEdit ? $assignment->datetime_start->format('Y-m-d\TH:i') : '') }}"
                    required>
                @error('datetime_start')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Retour prévu <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="datetime_end_planned"
                    class="form-input {{ $errors->has('datetime_end_planned') ? 'is-invalid' : '' }}"
                    value="{{ old('datetime_end_planned', $isEdit ? $assignment->datetime_end_planned->format('Y-m-d\TH:i') : '') }}"
                    required>
                @error('datetime_end_planned')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Destination</label>
                <input type="text" name="destination" class="form-input"
                    placeholder="ex: Aéroport FHB-Showroom Geomatos"
                    value="{{ old('destination', $isEdit ? $assignment->destination : '') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Mission / Objet</label>
                <input type="text" name="mission" class="form-input"
                    placeholder="ex: Transport délégation ministérielle"
                    value="{{ old('mission', $isEdit ? $assignment->mission : '') }}">
            </div>
        </div>
        @if ($isEdit)
            <div class="form-group">
                <label class="form-label">Type d'affectation</label>
                <select name="type" class="form-input">
                    @foreach ($typeMap as $val => $label)
                        <option value="{{ $val }}" @selected(old('type', $assignment->type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</div>

{{-- ── Section 5 : Kilométrage départ (optionnel à la création) ──────────── --}}
@if (!$isEdit)
    <div class="form-section">
        <div class="form-section-head">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke="#3b82f6" stroke-width="2" />
                <path d="M12 7v5l3 3" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" />
            </svg>
            <span class="form-section-title">Kilométrage & état initial <span
                    style="font-size:.73rem;font-weight:400;color:#94a3b8;">(optionnel — peut être renseigné au
                    départ)</span></span>
        </div>
        <div class="form-section-body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Km au départ</label>
                    <input type="number" name="km_start" class="form-input" placeholder="ex: 45800" min="0"
                        value="{{ old('km_start') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">État du véhicule</label>
                    <select name="condition_start" class="form-input">
                        <option value="">— (non renseigné) —</option>
                        <option value="good" @selected(old('condition_start') === 'good')>Bon état</option>
                        <option value="fair" @selected(old('condition_start') === 'fair')>État moyen</option>
                        <option value="poor" @selected(old('condition_start') === 'poor')>Mauvais état</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Observations</label>
                    <input type="text" name="condition_start_notes" class="form-input"
                        placeholder="Notes éventuelles…" value="{{ old('condition_start_notes') }}">
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── Boutons de soumission ─────────────────────────────────────────────── --}}
<div style="display:flex;gap:.75rem;justify-content:flex-end;">
    <a href="{{ $isEdit ? route('assignments.show', $assignment) : route('assignments.index') }}" class="btn-ghost">
        Annuler
    </a>
    <button type="submit" class="btn-submit">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
        </svg>
        {{ $isEdit ? 'Enregistrer les modifications' : 'Créer l\'affectation' }}
    </button>
</div>

<script>
    // ── Sélection du type ──────────────────────────────────────────────────────
    function selectType(val) {
        document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
        const lbl = document.getElementById('type-label-' + val);
        if (lbl) lbl.classList.add('selected');
    }

    // ── Sélection chauffeur ────────────────────────────────────────────────────
    function selectDriver(id) {
        document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
        const card = document.getElementById('driver-card-' + id);
        if (card) card.classList.add('selected');
    }

    // ── Sélection véhicule ─────────────────────────────────────────────────────
    function selectVehicle(id) {
        document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('selected'));
        const card = document.getElementById('vehicle-card-' + id);
        if (card) card.classList.add('selected');
    }

    // ── Recherche chauffeur ────────────────────────────────────────────────────
    function filterDrivers(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#driver-list .driver-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(q) ? '' : 'none';
        });
    }

    // ── Recherche véhicule ─────────────────────────────────────────────────────
    function filterVehicles(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#vehicle-list .vehicle-card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(q) ? '' : 'none';
        });
    }
</script>
