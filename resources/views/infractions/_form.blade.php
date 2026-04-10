@php
    $typeOptions = [
        'speeding'        => 'Excès de vitesse',
        'red_light'       => 'Grillage de feu rouge',
        'illegal_parking' => 'Stationnement illicite',
        'drunk_driving'   => 'Alcool au volant',
        'phone_use'       => 'Usage téléphone au volant',
        'accident'        => 'Accident',
        'seatbelt'        => 'Non port de ceinture',
        'overloading'     => 'Surcharge',
        'other'           => 'Autre',
    ];
    $sourceOptions = [
        'police_report'   => 'Police / Gendarmerie',
        'speed_camera'    => 'Radar automatique',
        'internal_report' => 'Signalement interne',
        'joint_report'    => 'Constat amiable',
        'other'           => 'Autre',
    ];
    $editing = isset($infraction);
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

    {{-- Véhicule --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Véhicule <span style="color:#ef4444;">*</span></label>
        <select id="inf-vehicle" name="vehicle_id" required onchange="triggerOccupantLookup()"
                style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('vehicle_id') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;">
            <option value="">— Sélectionner —</option>
            @foreach($vehicles as $v)
            <option value="{{ $v->id }}" {{ old('vehicle_id', $editing ? $infraction->vehicle_id : '') == $v->id ? 'selected' : '' }}>
                {{ $v->brand }} {{ $v->model }} — {{ $v->plate }}
            </option>
            @endforeach
        </select>
        @error('vehicle_id')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Date/heure (déplacé avant le conducteur pour déclencher la recherche) --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Date et heure <span style="color:#ef4444;">*</span></label>
        <input type="datetime-local" id="inf-datetime" name="datetime_occurred" required
               value="{{ old('datetime_occurred', $editing ? $infraction->datetime_occurred?->format('Y-m-d\TH:i') : '') }}"
               max="{{ now()->format('Y-m-d\TH:i') }}"
               onchange="triggerOccupantLookup()"
               style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('datetime_occurred') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;box-sizing:border-box;">
        @error('datetime_occurred')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Conducteur --}}
    <div style="grid-column:span 2;">
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">
            Conducteur
            <span style="font-size:.72rem;color:#64748b;">— pré-rempli automatiquement depuis les affectations</span>
        </label>

        {{-- Badge d'identification automatique --}}
        <div id="inf-occupant-badge" style="display:none;margin-bottom:.65rem;padding:.6rem .9rem;border-radius:.4rem;font-size:.82rem;align-items:center;gap:.65rem;">
            <svg id="inf-badge-icon" width="15" height="15" fill="none" viewBox="0 0 24 24"></svg>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span id="inf-badge-name" style="font-weight:700;"></span>
                    <span id="inf-badge-ref" style="opacity:.7;font-size:.75rem;"></span>
                    <span id="inf-badge-label" style="font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:99px;"></span>
                </div>
                <span id="inf-badge-source" style="font-size:.72rem;opacity:.6;"></span>
                <span id="inf-badge-override-hint" style="display:none;font-size:.72rem;color:#d97706;margin-left:.5rem;">
                    — Modifiez le champ ci-dessous si ce n'était pas lui au moment de l'infraction
                </span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div>
                <select id="inf-driver" name="driver_id"
                        style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('driver_id') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;">
                    <option value="">— Auto-identification —</option>
                    @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ old('driver_id', $editing ? $infraction->driver_id : '') == $d->id ? 'selected' : '' }}>
                        {{ $d->full_name }}{{ $d->matricule ? ' ('.$d->matricule.')' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('driver_id')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
                <p style="font-size:.72rem;color:#64748b;margin:.25rem 0 0;">Chauffeur professionnel</p>
            </div>
            <div>
                {{-- Collaborateur : peut être auto-identifié OU saisi manuellement --}}
                <input type="hidden" id="inf-user-id" name="user_id" value="{{ old('user_id', $editing ? $infraction->user_id : '') }}">
                <select id="inf-collab-select" onchange="onCollabChange(this)"
                        style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;">
                    <option value="">— Aucun collaborateur —</option>
                    @foreach($collaborators ?? [] as $u)
                    <option value="{{ $u->id }}" {{ old('user_id', $editing ? $infraction->user_id : '') == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}{{ $u->department ? ' ('.$u->department.')' : '' }}
                    </option>
                    @endforeach
                </select>
                <p style="font-size:.72rem;color:#64748b;margin:.25rem 0 0;">Collaborateur (si applicable)</p>
            </div>
        </div>
    </div>

    {{-- Lieu --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Lieu</label>
        <input type="text" name="location" placeholder="Adresse ou intersection"
               value="{{ old('location', $editing ? $infraction->location : '') }}"
               style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('location') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;box-sizing:border-box;">
        @error('location')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Type --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Type d'infraction <span style="color:#ef4444;">*</span></label>
        <select name="type" required
                style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('type') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;">
            <option value="">— Sélectionner —</option>
            @foreach($typeOptions as $val => $label)
            <option value="{{ $val }}" {{ old('type', $editing ? $infraction->type : '') === $val ? 'selected' : '' }}>
                {{ $label }}
            </option>
            @endforeach
        </select>
        @error('type')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Source --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Source <span style="color:#ef4444;">*</span></label>
        <select name="source" required
                style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('source') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;">
            <option value="">— Sélectionner —</option>
            @foreach($sourceOptions as $val => $label)
            <option value="{{ $val }}" {{ old('source', $editing ? $infraction->source : '') === $val ? 'selected' : '' }}>
                {{ $label }}
            </option>
            @endforeach
        </select>
        @error('source')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Référence PV --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Référence PV</label>
        <input type="text" name="pv_reference" placeholder="N° du procès-verbal"
               value="{{ old('pv_reference', $editing ? $infraction->pv_reference : '') }}"
               style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('pv_reference') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;box-sizing:border-box;">
        @error('pv_reference')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Montant de l'amende --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Montant de l'amende (FCFA)</label>
        <input type="number" name="fine_amount" min="0" step="100" placeholder="0"
               value="{{ old('fine_amount', $editing ? $infraction->fine_amount : '') }}"
               style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('fine_amount') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;box-sizing:border-box;">
        @error('fine_amount')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

</div>

{{-- Description --}}
<div style="margin-top:1rem;">
    <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Description</label>
    <textarea name="description" rows="3" placeholder="Détails complémentaires..."
              style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('description') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;resize:vertical;box-sizing:border-box;">{{ old('description', $editing ? $infraction->description : '') }}</textarea>
    @error('description')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
</div>

{{-- Sanction interne (edit seulement) --}}
@if($editing)
<div style="margin-top:1rem;">
    <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Sanction interne</label>
    <textarea name="internal_sanction" rows="2" placeholder="Avertissement, retenue sur salaire..."
              style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;resize:vertical;box-sizing:border-box;">{{ old('internal_sanction', $infraction->internal_sanction) }}</textarea>
</div>
@endif

<script>
(function () {
    let _debounce = null;
    const ENDPOINT = '{{ route('infractions.identify-occupant') }}';

    window.triggerOccupantLookup = function () {
        clearTimeout(_debounce);
        _debounce = setTimeout(lookupOccupant, 400);
    };

    // Quand l'utilisateur change le select collaborateur manuellement
    window.onCollabChange = function (sel) {
        const userId = document.getElementById('inf-user-id');
        if (userId) userId.value = sel.value;
    };

    async function lookupOccupant() {
        const vehicleId = document.getElementById('inf-vehicle')?.value;
        const datetime  = document.getElementById('inf-datetime')?.value;
        if (!vehicleId || !datetime) return;

        showBadge({ type: 'loading' });

        try {
            const url = `${ENDPOINT}?vehicle_id=${vehicleId}&datetime_occurred=${encodeURIComponent(datetime)}`;
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            showBadge(await res.json());
        } catch (e) {
            hideBadge();
        }
    }

    function showBadge(data) {
        const badge       = document.getElementById('inf-occupant-badge');
        const name        = document.getElementById('inf-badge-name');
        const ref         = document.getElementById('inf-badge-ref');
        const source      = document.getElementById('inf-badge-source');
        const label       = document.getElementById('inf-badge-label');
        const icon        = document.getElementById('inf-badge-icon');
        const hint        = document.getElementById('inf-badge-override-hint');
        const driverSel   = document.getElementById('inf-driver');
        const collabSel   = document.getElementById('inf-collab-select');
        const userIdField = document.getElementById('inf-user-id');

        // ── Loading ──────────────────────────────────────────────────────────
        if (data.type === 'loading') {
            badge.style.cssText = 'display:flex;background:#1e293b;border:1px solid #334155;margin-bottom:.65rem;padding:.6rem .9rem;border-radius:.4rem;font-size:.82rem;align-items:center;gap:.65rem;';
            name.textContent = 'Recherche en cours…';
            ref.textContent = source.textContent = label.textContent = '';
            icon.innerHTML = '';
            if (hint) hint.style.display = 'none';
            return;
        }

        // ── Chauffeur trouvé (ponctuel ou permanent) ─────────────────────────
        if (data.type === 'driver') {
            const isPermanent = data.is_permanent === true;
            const bg     = isPermanent ? '#451a03' : '#052e16';
            const border = isPermanent ? '#d97706'  : '#16a34a';
            const color  = isPermanent ? '#fbbf24'  : '#4ade80';
            const lblBg  = isPermanent ? '#78350f'  : '#14532d';
            const lblTxt = isPermanent ? 'Conducteur attitré' : 'Chauffeur trouvé';

            badge.style.cssText = `display:flex;background:${bg};border:1px solid ${border};margin-bottom:.65rem;padding:.6rem .9rem;border-radius:.4rem;font-size:.82rem;align-items:center;gap:.65rem;`;
            name.style.color = color;
            name.textContent = data.name;
            ref.textContent  = data.ref ? `(${data.ref})` : '';
            source.textContent = data.source;
            label.textContent  = lblTxt;
            label.style.cssText = `background:${lblBg};color:${color};font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:99px;`;
            icon.innerHTML = '<circle cx="12" cy="8" r="4" stroke="' + color + '" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="' + color + '" stroke-width="2" stroke-linecap="round"/>';

            // Pré-sélectionner dans le dropdown chauffeur
            if (driverSel) {
                const opt = [...driverSel.options].find(o => o.value == data.id);
                if (opt) driverSel.value = data.id;
            }
            // Vider collaborateur
            if (collabSel) collabSel.value = '';
            if (userIdField) userIdField.value = '';

            // Hint "modifiable" uniquement pour permanent
            if (hint) hint.style.display = isPermanent ? 'inline' : 'none';
        }

        // ── Collaborateur trouvé ─────────────────────────────────────────────
        else if (data.type === 'user') {
            const isPermanent = data.is_permanent === true;
            badge.style.cssText = 'display:flex;background:#1e1b4b;border:1px solid #7c3aed;margin-bottom:.65rem;padding:.6rem .9rem;border-radius:.4rem;font-size:.82rem;align-items:center;gap:.65rem;';
            name.style.color = '#a78bfa';
            name.textContent  = data.name;
            ref.textContent   = data.ref ? `· ${data.ref}` : '';
            source.textContent = data.source;
            label.textContent  = isPermanent ? 'Collaborateur attitré' : 'Collaborateur trouvé';
            label.style.cssText = 'background:#2e1065;color:#a78bfa;font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:99px;';
            icon.innerHTML = '<rect x="2" y="7" width="20" height="14" rx="2" stroke="#a78bfa" stroke-width="2"/><path d="M3 17h2l1-3h12l1 3h2" stroke="#a78bfa" stroke-width="1.5" stroke-linecap="round"/>';

            // Pré-sélectionner dans le select collaborateur
            if (collabSel) {
                const opt = [...collabSel.options].find(o => o.value == data.id);
                if (opt) collabSel.value = data.id;
            }
            if (userIdField) userIdField.value = data.id;
            // Vider chauffeur si pas en mode édition
            if (driverSel && !{{ $editing ? 'true' : 'false' }}) driverSel.value = '';

            if (hint) hint.style.display = isPermanent ? 'inline' : 'none';
        }

        // ── Non identifié ────────────────────────────────────────────────────
        else {
            badge.style.cssText = 'display:flex;background:#1e293b;border:1px solid #475569;margin-bottom:.65rem;padding:.6rem .9rem;border-radius:.4rem;font-size:.82rem;align-items:center;gap:.65rem;';
            name.style.color = '#94a3b8';
            name.textContent  = 'Aucun conducteur identifié pour ce véhicule à cette date.';
            ref.textContent = source.textContent = '';
            label.textContent  = 'Non trouvé';
            label.style.cssText = 'background:#334155;color:#94a3b8;font-size:.7rem;font-weight:700;padding:.15rem .5rem;border-radius:99px;';
            icon.innerHTML = '<circle cx="12" cy="12" r="10" stroke="#64748b" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="#64748b" stroke-width="2" stroke-linecap="round"/>';
            if (hint) hint.style.display = 'none';
            if (userIdField) userIdField.value = '';
        }
    }

    function hideBadge() {
        const badge = document.getElementById('inf-occupant-badge');
        if (badge) badge.style.display = 'none';
    }
})();
</script>
