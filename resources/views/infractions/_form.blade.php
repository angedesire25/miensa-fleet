@php
    $typeOptions = [
        'speeding'          => 'Excès de vitesse',
        'red_light'         => 'Grillage de feu rouge',
        'parking'           => 'Stationnement interdit',
        'phone_use'         => 'Usage téléphone au volant',
        'seatbelt'          => 'Non port de ceinture',
        'alcohol'           => 'Alcool au volant',
        'dangerous_driving' => 'Conduite dangereuse',
        'overload'          => 'Surcharge',
        'invalid_documents' => 'Documents non valides',
        'other'             => 'Autre',
    ];
    $sourceOptions = [
        'police'             => 'Police / Gendarmerie',
        'radar'              => 'Radar automatique',
        'internal'           => 'Signalement interne',
        'reported_by_driver' => 'Signalé par le conducteur',
        'third_party'        => 'Tiers',
    ];
    $editing = isset($infraction);
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

    {{-- Véhicule --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Véhicule <span style="color:#ef4444;">*</span></label>
        <select name="vehicle_id" required
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

    {{-- Conducteur --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">
            Conducteur
            <span style="font-size:.72rem;color:#64748b;">(laissez vide pour auto-identification)</span>
        </label>
        <select name="driver_id"
                style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('driver_id') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;">
            <option value="">— Identification automatique —</option>
            @foreach($drivers as $d)
            <option value="{{ $d->id }}" {{ old('driver_id', $editing ? $infraction->driver_id : '') == $d->id ? 'selected' : '' }}>
                {{ $d->full_name }}{{ $d->matricule ? ' ('.$d->matricule.')' : '' }}
            </option>
            @endforeach
        </select>
        @error('driver_id')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
    </div>

    {{-- Date/heure --}}
    <div>
        <label style="display:block;font-size:.82rem;color:#94a3b8;margin-bottom:.35rem;">Date et heure <span style="color:#ef4444;">*</span></label>
        <input type="datetime-local" name="datetime_occurred" required
               value="{{ old('datetime_occurred', $editing ? $infraction->datetime_occurred?->format('Y-m-d\TH:i') : '') }}"
               max="{{ now()->format('Y-m-d\TH:i') }}"
               style="width:100%;background:#0f172a;border:1px solid {{ $errors->has('datetime_occurred') ? '#ef4444' : '#475569' }};border-radius:.4rem;color:#f1f5f9;padding:.55rem .75rem;font-size:.88rem;box-sizing:border-box;">
        @error('datetime_occurred')<p style="color:#ef4444;font-size:.75rem;margin:.25rem 0 0;">{{ $message }}</p>@enderror
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
