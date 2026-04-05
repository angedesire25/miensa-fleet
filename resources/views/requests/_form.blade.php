{{--
  Formulaire partagé : create & edit demandes de véhicule
  Variable attendue : $vehicleRequest (Assignment|null en mode édition)
--}}
@php $isEdit = isset($vehicleRequest); @endphp

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

    .type-pref-card {
        border: 2px solid #e2e8f0;
        border-radius: .55rem;
        padding: .6rem .85rem;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .25rem;
        transition: border-color .15s, background .15s;
        user-select: none;
        text-align: center;
    }

    .type-pref-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .type-pref-card.selected {
        border-color: #10b981;
        background: #f0fdf4;
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
</style>

{{-- Erreurs --}}
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

{{-- ── Section 1 : Destination & objet ─────────────────────────────────── --}}
<div class="form-section">
    <div class="form-section-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" stroke="#10b981" stroke-width="2" />
            <circle cx="12" cy="10" r="3" stroke="#10b981" stroke-width="2" />
        </svg>
        <span class="form-section-title">Destination & objet du déplacement</span>
    </div>
    <div class="form-section-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Destination <span style="color:#ef4444;">*</span></label>
                <input type="text" name="destination"
                    class="form-input {{ $errors->has('destination') ? 'is-invalid' : '' }}"
                    placeholder="ex: Aéroport FHB — Showroom Geomatos  "
                    value="{{ old('destination', $isEdit ? $vehicleRequest->destination : '') }}" required>
                @error('destination')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Objet / Raison <span style="color:#ef4444;">*</span></label>
                <input type="text" name="purpose"
                    class="form-input {{ $errors->has('purpose') ? 'is-invalid' : '' }}"
                    placeholder="ex: Test de portée- Démonstration client — Réunion projet"
                    value="{{ old('purpose', $isEdit ? $vehicleRequest->purpose : '') }}" required>
                @error('purpose')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Notes complémentaires (optionnel)</label>
            <textarea name="requester_notes" class="form-input" rows="2"
                placeholder="Instructions particulières, contraintes, équipements nécessaires…">{{ old('requester_notes', $isEdit ? $vehicleRequest->requester_notes : '') }}</textarea>
        </div>
    </div>
</div>

{{-- ── Section 2 : Dates ────────────────────────────────────────────────── --}}
<div class="form-section">
    <div class="form-section-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="16" rx="2" stroke="#d97706" stroke-width="2" />
            <path d="M8 2v4M16 2v4M3 10h18" stroke="#d97706" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span class="form-section-title">Dates de déplacement</span>
    </div>
    <div class="form-section-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label class="form-label">Date et heure de départ <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="datetime_start"
                    class="form-input {{ $errors->has('datetime_start') ? 'is-invalid' : '' }}"
                    value="{{ old('datetime_start', $isEdit ? $vehicleRequest->datetime_start->format('Y-m-d\TH:i') : '') }}"
                    @if (!$isEdit) min="{{ now()->format('Y-m-d\TH:i') }}" @endif required>
                @error('datetime_start')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Date et heure de retour prévue <span style="color:#ef4444;">*</span></label>
                <input type="datetime-local" name="datetime_end_planned"
                    class="form-input {{ $errors->has('datetime_end_planned') ? 'is-invalid' : '' }}"
                    value="{{ old('datetime_end_planned', $isEdit ? $vehicleRequest->datetime_end_planned->format('Y-m-d\TH:i') : '') }}"
                    required>
                @error('datetime_end_planned')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- ── Section 3 : Passagers & type de véhicule ────────────────────────── --}}
<div class="form-section">
    <div class="form-section-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <path d="M3 17h2l1-3h12l1 3h2" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" />
            <circle cx="7.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5" />
            <circle cx="16.5" cy="18.5" r="1.5" stroke="#3b82f6" stroke-width="1.5" />
        </svg>
        <span class="form-section-title">Passagers & type de véhicule souhaité</span>
    </div>
    <div class="form-section-body">
        <div class="form-group" style="max-width:200px;">
            <label class="form-label">Nombre de passagers <span style="color:#ef4444;">*</span></label>
            <input type="number" name="passengers"
                class="form-input {{ $errors->has('passengers') ? 'is-invalid' : '' }}" min="1"
                max="50" placeholder="ex: 3"
                value="{{ old('passengers', $isEdit ? $vehicleRequest->passengers : '') }}" required>
            @error('passengers')
                <div class="invalid-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" style="margin-bottom:.6rem;">Type de véhicule préféré</label>
            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem;">
                @foreach (['any' => ['Indifférent', '🔀'], 'city' => ['Citadine', '🏙️'], 'sedan' => ['Berline', '🚗'], 'suv' => ['SUV', '🚙'], 'pickup' => ['Pickup', '🛻'], 'van' => ['Fourgon', '🚐'], 'truck' => ['Camion', '🚚']] as $val => [$lbl, $icon])
                    <label
                        class="type-pref-card {{ old('vehicle_type_preferred', $isEdit ? $vehicleRequest->vehicle_type_preferred : 'any') === $val ? 'selected' : '' }}"
                        id="vpref-{{ $val }}">
                        <input type="radio" name="vehicle_type_preferred" value="{{ $val }}"
                            style="display:none;" @checked(old('vehicle_type_preferred', $isEdit ? $vehicleRequest->vehicle_type_preferred : 'any') === $val)
                            onchange="selectVPref('{{ $val }}')">
                        <div style="font-size:1.25rem;">{{ $icon }}</div>
                        <div style="font-size:.72rem;font-weight:600;color:#374151;">{{ $lbl }}</div>
                    </label>
                @endforeach
            </div>
            @error('vehicle_type_preferred')
                <div class="invalid-msg" style="margin-top:.35rem;">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ── Section 4 : Urgence ─────────────────────────────────────────────── --}}
<div class="form-section">
    <div class="form-section-head">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round" />
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                stroke="#ef4444" stroke-width="2" />
        </svg>
        <span class="form-section-title">Niveau de priorité</span>
    </div>
    <div class="form-section-body" style="padding:1rem 1.5rem;">
        <label
            style="display:flex;align-items:center;gap:.85rem;cursor:pointer;padding:.75rem 1rem;border:2px solid {{ old('is_urgent', $isEdit && $vehicleRequest->is_urgent ? '1' : '0') === '1' ? '#ef4444' : '#e2e8f0' }};border-radius:.55rem;background:{{ old('is_urgent', $isEdit && $vehicleRequest->is_urgent ? '1' : '0') === '1' ? '#fef2f2' : '#fff' }};"
            id="urgent-label">
            <input type="checkbox" name="is_urgent" value="1" id="urgent-chk" @checked(old('is_urgent', $isEdit && $vehicleRequest->is_urgent))
                onchange="toggleUrgent(this)" style="width:18px;height:18px;accent-color:#ef4444;flex-shrink:0;">
            <div>
                <div style="font-weight:700;font-size:.84rem;color:#0f172a;">🚨 Demande urgente</div>
                <div style="font-size:.76rem;color:#94a3b8;margin-top:.1rem;">Cochez si cette demande nécessite un
                    traitement prioritaire immédiat.</div>
            </div>
        </label>
    </div>
</div>

{{-- ── Boutons ──────────────────────────────────────────────────────────── --}}
<div style="display:flex;gap:.75rem;justify-content:flex-end;">
    <a href="{{ $isEdit ? route('requests.show', $vehicleRequest) : route('requests.index') }}" class="btn-ghost">
        Annuler
    </a>
    <button type="submit" class="btn-submit">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24">
            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
        </svg>
        {{ $isEdit ? 'Mettre à jour la demande' : 'Soumettre la demande' }}
    </button>
</div>

<script>
    function selectVPref(val) {
        document.querySelectorAll('.type-pref-card').forEach(c => c.classList.remove('selected'));
        const el = document.getElementById('vpref-' + val);
        if (el) el.classList.add('selected');
    }

    function toggleUrgent(chk) {
        const lbl = document.getElementById('urgent-label');
        if (chk.checked) {
            lbl.style.borderColor = '#ef4444';
            lbl.style.background = '#fef2f2';
        } else {
            lbl.style.borderColor = '#e2e8f0';
            lbl.style.background = '#fff';
        }
    }
</script>
