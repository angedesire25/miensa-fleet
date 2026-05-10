@extends('layouts.dashboard')

@section('title', 'Enregistrer un plein')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Nouveau plein')

@section('content')
<div class="page-content" style="max-width:800px;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('fuel.admin.transactions') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#64748b;text-decoration:none;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
        <div>
            <h1 style="font-size:1.25rem;font-weight:700;color:#0f172a;margin:0;">Enregistrer un ravitaillement</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.15rem 0 0;">
                @if($fuelRequest) Plein lié à la demande <strong>{{ $fuelRequest->reference }}</strong>. @else Saisie directe sans demande préalable. @endif
            </p>
        </div>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.65rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;">
        <ul style="margin:0;padding-left:1.25rem;color:#b91c1c;font-size:.85rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('fuel.admin.transaction-store') }}" enctype="multipart/form-data"
          style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.75rem;">
        @csrf

        {{-- Lien demande --}}
        @if($fuelRequest)
        <input type="hidden" name="fuel_request_id" value="{{ $fuelRequest->id }}">
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:.55rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#1e40af;">
            Demande liée : <strong>{{ $fuelRequest->reference }}</strong> —
            {{ $fuelRequest->vehicle?->brand }} {{ $fuelRequest->vehicle?->model }} ({{ $fuelRequest->vehicle?->plate }})
            · {{ number_format($fuelRequest->liters_requested, 0, ',', ' ') }} L demandés
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;">

            {{-- Véhicule --}}
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Véhicule <span style="color:#ef4444;">*</span></label>
                <select name="vehicle_id" required id="vehicleSelect"
                        style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                    <option value="">— Sélectionner —</option>
                    @foreach($vehicles as $v)
                    <option value="{{ $v->id }}"
                            data-km="{{ $v->km_last_fill ?? '' }}"
                            {{ old('vehicle_id', $fuelRequest?->vehicle_id)==$v->id?'selected':'' }}>
                        {{ $v->brand }} {{ $v->model }} — {{ $v->plate }}
                        @if($v->km_last_fill) (dernier plein : {{ number_format($v->km_last_fill,0,',',' ') }} km)@endif
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Chauffeur --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Chauffeur</label>
                <select name="driver_id"
                        style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                    <option value="">— Aucun —</option>
                    @foreach($drivers as $d)
                    <option value="{{ $d->id }}" {{ old('driver_id', $fuelRequest?->driver_id)==$d->id?'selected':'' }}>
                        {{ $d->full_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Date plein --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Date du plein <span style="color:#ef4444;">*</span></label>
                <input type="date" name="fueled_at" value="{{ old('fueled_at', today()->format('Y-m-d')) }}" required
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;box-sizing:border-box;">
            </div>

            {{-- Type carburant --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Type carburant <span style="color:#ef4444;">*</span></label>
                <select name="fuel_type" required
                        style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                    @foreach($fuelTypes as $val => $label)
                    <option value="{{ $val }}" {{ old('fuel_type', $fuelRequest?->fuel_type)===$val?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Litres --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Litres distribués <span style="color:#ef4444;">*</span></label>
                <input type="number" name="liters" value="{{ old('liters', $fuelRequest?->liters_requested) }}" required min="0.1" step="0.01"
                       id="litersInput"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;">
            </div>

            {{-- Prix unitaire --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Prix/L (FCFA) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="unit_price" value="{{ old('unit_price') }}" required min="0" step="1"
                       id="unitPriceInput"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;">
            </div>

            {{-- Montant total (calculé) --}}
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Montant total (FCFA) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="total_amount" value="{{ old('total_amount') }}" required min="0" step="1"
                       id="totalInput"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;font-weight:700;">
                <p style="font-size:.75rem;color:#94a3b8;margin:.3rem 0 0;">Calculé automatiquement (litres × prix), modifiable si besoin.</p>
            </div>

            {{-- Kilométrage --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Kilométrage au plein <span style="color:#ef4444;">*</span></label>
                <input type="number" name="odometer_km" value="{{ old('odometer_km', $fuelRequest?->odometer_km) }}" required min="0"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;">
            </div>

            {{-- Station --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Station</label>
                <select name="fuel_station_id" id="stationSelect" onchange="toggleFreeStation()"
                        style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                    <option value="">— Autre (saisie libre) —</option>
                    @foreach($stations as $st)
                    <option value="{{ $st->id }}" {{ old('fuel_station_id', $fuelRequest?->fuel_station_id)==$st->id?'selected':'' }}>
                        {{ $st->brand ? $st->brand.' — ' : '' }}{{ $st->name }}{{ $st->city ? ' ('.$st->city.')' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Station libre --}}
            <div id="freeStationWrap" style="grid-column:1/-1;{{ old('fuel_station_id') ? 'display:none' : '' }}">
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Nom de la station (si non référencée)</label>
                <input type="text" name="station_name_free" value="{{ old('station_name_free') }}" maxlength="150"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
                       placeholder="Ex: Total Plateau Centre">
            </div>

            {{-- Carte carburant --}}
            <div style="grid-column:1/-1;">
                <div style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;">
                    <input type="checkbox" name="fuel_card_used" id="fuelCardUsed" value="1"
                           {{ old('fuel_card_used') ? 'checked' : '' }} onchange="toggleCardNumber()"
                           style="width:16px;height:16px;accent-color:#10b981;cursor:pointer;">
                    <label for="fuelCardUsed" style="font-size:.875rem;color:#374151;font-weight:500;cursor:pointer;">
                        Paiement par carte carburant
                    </label>
                </div>
            </div>

            <div id="cardNumberWrap" style="{{ old('fuel_card_used') ? '' : 'display:none;' }}grid-column:1/-1;">
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Numéro de carte</label>
                <input type="text" name="fuel_card_number" value="{{ old('fuel_card_number') }}" maxlength="30"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
                       placeholder="Ex: 4567-1234-XXXX">
            </div>

            {{-- Ticket --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">N° ticket/facture</label>
                <input type="text" name="receipt_number" value="{{ old('receipt_number') }}" maxlength="60"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
                       placeholder="Ex: FAC-2026-00123">
            </div>

            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Photo du ticket</label>
                <input type="file" name="receipt_photo" accept="image/*"
                       style="width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.82rem;box-sizing:border-box;">
            </div>

            {{-- Notes --}}
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Notes</label>
                <textarea name="notes" rows="2" maxlength="1000"
                          style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;resize:vertical;box-sizing:border-box;"
                          placeholder="Observations sur le plein…">{{ old('notes') }}</textarea>
            </div>

        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;">
            <a href="{{ route('fuel.admin.transactions') }}"
               style="padding:.6rem 1.2rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.875rem;color:#64748b;text-decoration:none;font-weight:500;">
                Annuler
            </a>
            <button type="submit"
                    style="padding:.6rem 1.4rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:.45rem;font-size:.875rem;font-weight:600;cursor:pointer;">
                Enregistrer le plein
            </button>
        </div>
    </form>

</div>

<script>
// Calcul automatique bidirectionnel
// Règle : total = litres × prix
// → Si on change litres ou prix : recalcule total
// → Si on change total : recalcule litres (total ÷ prix)
// → Si on change prix avec un total déjà saisi : recalcule litres
const litersInput    = document.getElementById('litersInput');
const unitPriceInput = document.getElementById('unitPriceInput');
const totalInput     = document.getElementById('totalInput');

// Mémorise quel champ a été modifié en dernier pour arbitrer les conflits
let lastEdited = 'liters'; // 'liters' | 'total'

litersInput.addEventListener('input', function () {
    lastEdited = 'liters';
    const l = parseFloat(this.value) || 0;
    const p = parseFloat(unitPriceInput.value) || 0;
    if (l > 0 && p > 0) totalInput.value = Math.round(l * p);
});

unitPriceInput.addEventListener('input', function () {
    const p = parseFloat(this.value) || 0;
    if (p <= 0) return;
    if (lastEdited === 'total') {
        // On connaît le total → on recalcule les litres
        const t = parseFloat(totalInput.value) || 0;
        if (t > 0) litersInput.value = (t / p).toFixed(2);
    } else {
        // On connaît les litres → on recalcule le total
        const l = parseFloat(litersInput.value) || 0;
        if (l > 0) totalInput.value = Math.round(l * p);
    }
});

totalInput.addEventListener('input', function () {
    lastEdited = 'total';
    const t = parseFloat(this.value) || 0;
    const p = parseFloat(unitPriceInput.value) || 0;
    if (t > 0 && p > 0) litersInput.value = (t / p).toFixed(2);
});

function toggleFreeStation() {
    const wrap = document.getElementById('freeStationWrap');
    wrap.style.display = document.getElementById('stationSelect').value ? 'none' : '';
}

function toggleCardNumber() {
    const wrap = document.getElementById('cardNumberWrap');
    wrap.style.display = document.getElementById('fuelCardUsed').checked ? '' : 'none';
}

// Init
toggleFreeStation();
toggleCardNumber();
</script>
@endsection
