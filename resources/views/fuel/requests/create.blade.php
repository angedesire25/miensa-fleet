@extends('layouts.dashboard')

@section('title', 'Nouvelle demande carburant')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Nouvelle demande')

@section('content')
<div class="page-content" style="max-width:720px;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('fuel.requests.index') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#64748b;text-decoration:none;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
        <div>
            <h1 style="font-size:1.25rem;font-weight:700;color:#0f172a;margin:0;">Nouvelle demande de carburant</h1>
            <p style="color:#64748b;font-size:.875rem;margin:.15rem 0 0;">Remplissez le formulaire ci-dessous pour soumettre votre demande.</p>
        </div>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.65rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;">
        <ul style="margin:0;padding-left:1.25rem;color:#b91c1c;font-size:.85rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('fuel.requests.store') }}"
          style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.75rem;">
        @csrf

        {{-- Véhicule --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                Véhicule <span style="color:#ef4444;">*</span>
            </label>
            <select name="vehicle_id" required
                    style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                <option value="">— Sélectionner un véhicule —</option>
                @foreach($vehicles as $v)
                <option value="{{ $v->id }}" {{ old('vehicle_id')==$v->id?'selected':'' }}>
                    {{ $v->brand }} {{ $v->model }} — {{ $v->plate }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Type de carburant --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                Type de carburant <span style="color:#ef4444;">*</span>
            </label>
            <select name="fuel_type" id="fuelTypeSelect" required onchange="onFuelTypeChange()"
                    style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                <option value="">— Sélectionner —</option>
                @foreach($fuelTypes as $val => $label)
                <option value="{{ $val }}" {{ old('fuel_type')===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Prix unitaire de référence (caché, utilisé pour le calcul) --}}
        {{-- Prix indicatifs CIV (FCFA/L) — mis à jour manuellement si besoin --}}
        <div style="margin-bottom:1.25rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:.75rem 1rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                <label style="font-size:.82rem;font-weight:600;color:#374151;">
                    Prix unitaire de référence (FCFA/L)
                    <span style="font-size:.72rem;font-weight:400;color:#94a3b8;margin-left:.3rem;">utilisé pour calculer les litres</span>
                </label>
            </div>
            <input type="number" id="unitPriceRef" name="_unit_price_ref"
                   value="{{ old('_unit_price_ref', 680) }}"
                   min="1" step="1"
                   onchange="recalcFromAmount()"
                   style="width:150px;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:.4rem;font-size:.875rem;color:#374151;">
            <span style="font-size:.78rem;color:#94a3b8;margin-left:.5rem;" id="priceHint">Diesel ≈ 675 FCFA/L</span>
        </div>

        {{-- Montant + Litres (bidirectionnel) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                    Montant (FCFA) <span style="color:#ef4444;">*</span>
                </label>
                <input type="number" name="estimated_amount" id="amountInput"
                       value="{{ old('estimated_amount') }}"
                       required min="1" step="1"
                       oninput="recalcFromAmount()"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;box-sizing:border-box;font-weight:600;"
                       placeholder="Ex: 30 000">
                <p style="font-size:.72rem;color:#94a3b8;margin:.25rem 0 0;">Montant à dépenser</p>
            </div>
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                    Litres estimés
                    <span style="font-size:.72rem;font-weight:400;color:#94a3b8;">(calculé)</span>
                </label>
                <input type="number" name="liters_requested" id="litersInput"
                       value="{{ old('liters_requested') }}"
                       min="0.1" max="1000" step="any"
                       oninput="recalcFromLiters()"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.875rem;color:#374151;box-sizing:border-box;background:#f8fafc;"
                       placeholder="Auto">
                <p style="font-size:.72rem;color:#94a3b8;margin:.25rem 0 0;">Modifiable si besoin</p>
            </div>
        </div>

        {{-- Kilométrage — obligatoire pour les chauffeurs, optionnel sinon --}}
        @php $isDriver = auth()->user()->hasRole('driver_user'); @endphp
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                Kilométrage actuel
                @if($isDriver)
                    <span style="color:#ef4444;">*</span>
                @else
                    <span style="font-size:.75rem;font-weight:400;color:#94a3b8;">(recommandé)</span>
                @endif
            </label>
            <input type="number" name="odometer_km" value="{{ old('odometer_km') }}"
                   {{ $isDriver ? 'required' : '' }} min="0"
                   style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;box-sizing:border-box;"
                   placeholder="{{ $isDriver ? 'Relevé compteur obligatoire' : 'Relevé compteur en km (facultatif)' }}">
            @if(!$isDriver)
            <p style="font-size:.72rem;color:#94a3b8;margin:.25rem 0 0;">
                Renseignez le compteur si vous avez l'information — obligatoire pour les chauffeurs professionnels.
            </p>
            @endif
        </div>

        {{-- Station suggérée --}}
        @if($stations->isNotEmpty())
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                Station suggérée
            </label>
            <select name="fuel_station_id"
                    style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;background:#fff;">
                <option value="">— Aucune préférence —</option>
                @foreach($stations as $st)
                <option value="{{ $st->id }}" {{ old('fuel_station_id')==$st->id?'selected':'' }}>
                    {{ $st->brand ? $st->brand.' — ' : '' }}{{ $st->name }}{{ $st->city ? ' ('.$st->city.')' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Motif --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                Motif / Justification <span style="color:#ef4444;">*</span>
            </label>
            <textarea name="reason" required rows="3" maxlength="500"
                      style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;resize:vertical;box-sizing:border-box;"
                      placeholder="Décrivez brièvement le besoin en carburant…">{{ old('reason') }}</textarea>
        </div>

        {{-- Notes --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">
                Notes complémentaires
            </label>
            <textarea name="notes" rows="2" maxlength="1000"
                      style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;color:#374151;resize:vertical;box-sizing:border-box;"
                      placeholder="Informations supplémentaires (facultatif)">{{ old('notes') }}</textarea>
        </div>

        {{-- Urgent --}}
        <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.75rem;padding:.75rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.5rem;">
            <input type="checkbox" name="is_urgent" id="is_urgent" value="1"
                   {{ old('is_urgent') ? 'checked' : '' }}
                   style="width:16px;height:16px;accent-color:#f59e0b;cursor:pointer;">
            <label for="is_urgent" style="font-size:.875rem;color:#92400e;font-weight:500;cursor:pointer;">
                Demande urgente — le gestionnaire sera alerté en priorité
            </label>
        </div>

        {{-- Actions --}}
        <div class="form-actions">
            <a href="{{ route('fuel.requests.index') }}"
               style="padding:.6rem 1.2rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.875rem;color:#64748b;text-decoration:none;font-weight:500;display:inline-flex;align-items:center;justify-content:center;">
                Annuler
            </a>
            <button type="submit"
                    style="padding:.6rem 1.4rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:.45rem;font-size:.875rem;font-weight:600;cursor:pointer;">
                Soumettre la demande
            </button>
        </div>
    </form>

</div>
<script>
// Prix indicatifs par défaut (FCFA/L) — Côte d'Ivoire
const defaultPrices = {
    diesel:   675,
    gasoline: 820,
    hybrid:   720,
    electric: 0,   // pas de prix/L pour l'électrique
    lpg:      500,
};

const priceHints = {
    diesel:   'Diesel ≈ 675 FCFA/L',
    gasoline: 'Essence ≈ 820 FCFA/L',
    hybrid:   'Hybride ≈ 720 FCFA/L',
    electric: 'Électrique — saisir en kWh',
    lpg:      'GPL ≈ 500 FCFA/L',
};

function onFuelTypeChange() {
    const type = document.getElementById('fuelTypeSelect').value;
    const priceInput = document.getElementById('unitPriceRef');
    const hint = document.getElementById('priceHint');

    if (type && defaultPrices[type] !== undefined) {
        priceInput.value = defaultPrices[type] || '';
        hint.textContent = priceHints[type] || '';
    }
    recalcFromAmount();
}

function recalcFromAmount() {
    const amount = parseFloat(document.getElementById('amountInput').value) || 0;
    const price  = parseFloat(document.getElementById('unitPriceRef').value) || 0;
    const litersInput = document.getElementById('litersInput');

    if (amount > 0 && price > 0) {
        litersInput.value = (amount / price).toFixed(1);
    } else {
        litersInput.value = '';
    }
}

function recalcFromLiters() {
    const liters = parseFloat(document.getElementById('litersInput').value) || 0;
    const price  = parseFloat(document.getElementById('unitPriceRef').value) || 0;
    const amountInput = document.getElementById('amountInput');

    if (liters > 0 && price > 0) {
        amountInput.value = Math.round(liters * price);
    }
}

// Init au chargement si des valeurs sont déjà présentes (old())
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('fuelTypeSelect').value;
    if (type) onFuelTypeChange();

    // Si montant déjà renseigné (old()), recalculer les litres
    const amount = document.getElementById('amountInput').value;
    if (amount) recalcFromAmount();
});
</script>
@endsection
