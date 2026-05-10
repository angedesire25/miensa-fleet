@extends('landlord-admin.layouts.app')

@section('title', 'Modifier le plan '.$plan->name)
@section('page-title', 'Plans & Tarifs')
@section('breadcrumb', 'Modifier · '.$plan->name)

@push('styles')
<style>
    .plan-form { max-width: 860px; }
    .form-section {
        background: #1e293b; border: 1px solid rgba(255,255,255,.07);
        border-radius: 12px; padding: 1.5rem; margin-bottom: 1.25rem;
    }
    .form-section-title {
        font-size: .78rem; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: .07em;
        margin: 0 0 1.1rem; padding-bottom: .65rem;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .field-row  { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .field-row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    .field { display: flex; flex-direction: column; gap: .35rem; }
    .field label { font-size: .82rem; font-weight: 600; color: #94a3b8; }
    .field input[type="text"], .field input[type="number"], .field textarea {
        background: #0f172a; border: 1px solid rgba(255,255,255,.1);
        border-radius: 7px; padding: .55rem .85rem;
        color: #e2e8f0; font-size: .875rem; font-family: inherit;
        transition: border-color .15s; outline: none; width: 100%; box-sizing: border-box;
    }
    .field input:focus, .field textarea:focus { border-color: #3b82f6; }
    .field textarea { resize: vertical; min-height: 70px; }
    .field .hint { font-size: .73rem; color: #475569; margin-top: .15rem; }

    .toggle-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .65rem; }
    .toggle-item {
        display: flex; align-items: center; gap: .6rem;
        background: #0f172a; border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px; padding: .6rem .85rem; cursor: pointer;
        transition: border-color .15s;
    }
    .toggle-item:has(input:checked) { border-color: rgba(34,197,94,.35); }
    .toggle-item label { font-size: .83rem; color: #94a3b8; cursor: pointer; }
    .toggle-item:has(input:checked) label { color: #86efac; }
    input[type="checkbox"] { accent-color: #10b981; width: 15px; height: 15px; cursor: pointer; }

    .price-input-wrap { position: relative; }
    .price-input-wrap input { padding-right: 4.5rem; }
    .price-input-wrap .currency {
        position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
        font-size: .78rem; color: #475569; font-weight: 600; pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="plan-form">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('admin.plans.index') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:#1e293b;border:1px solid rgba(255,255,255,.07);border-radius:.45rem;color:#64748b;text-decoration:none;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
        <div>
            <h1 style="font-size:1.1rem;font-weight:700;color:#f1f5f9;margin:0;">Modifier le plan « {{ $plan->name }} »</h1>
            <p style="color:#64748b;font-size:.8rem;margin:.1rem 0 0;">Les modifications sont répercutées immédiatement sur la page d'accueil.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
        @csrf
        @method('PUT')

        {{-- IDENTITÉ ─────────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Identité</div>
            <div class="field-row" style="margin-bottom:1rem;">
                <div class="field">
                    <label>Nom du plan <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required maxlength="80">
                </div>
                <div class="field">
                    <label>Ordre d'affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" min="0" max="100">
                    <span class="hint">Plus petit = affiché en premier.</span>
                </div>
            </div>
            <div class="field">
                <label>Description</label>
                <textarea name="description" maxlength="500" placeholder="Courte description affichée sous le nom du plan…">{{ old('description', $plan->description) }}</textarea>
            </div>
        </div>

        {{-- TARIFICATION ─────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Tarification (FCFA)</div>
            <div class="field-row">
                <div class="field">
                    <label>Prix mensuel <span style="color:#ef4444">*</span></label>
                    <div class="price-input-wrap">
                        <input type="number" name="price_monthly"
                               value="{{ old('price_monthly', $plan->price_monthly) }}"
                               required min="0" step="500" id="priceMonthly">
                        <span class="currency">FCFA</span>
                    </div>
                    <span class="hint">0 = gratuit pour toujours.</span>
                </div>
                <div class="field">
                    <label>Prix annuel <span style="color:#ef4444">*</span></label>
                    <div class="price-input-wrap">
                        <input type="number" name="price_yearly"
                               value="{{ old('price_yearly', $plan->price_yearly) }}"
                               required min="0" step="1000" id="priceYearly">
                        <span class="currency">FCFA</span>
                    </div>
                    <div class="hint" id="savingsHint" style="color:#86efac;"></div>
                </div>
            </div>
            <div class="field" style="margin-top:1rem;">
                <label>Jours d'essai gratuit</label>
                <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="0" max="365" style="max-width:180px;">
                <span class="hint">0 = pas d'essai, accès immédiat.</span>
            </div>
        </div>

        {{-- LIMITES ──────────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Limites</div>
            <div class="field-row3">
                <div class="field">
                    <label>Véhicules max</label>
                    <input type="number" name="max_vehicles" value="{{ old('max_vehicles', $plan->max_vehicles) }}" min="1" max="9999">
                    <span class="hint">9999 = illimité.</span>
                </div>
                <div class="field">
                    <label>Utilisateurs max</label>
                    <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users) }}" min="1" max="9999">
                </div>
                <div class="field">
                    <label>Chauffeurs max</label>
                    <input type="number" name="max_drivers" value="{{ old('max_drivers', $plan->max_drivers) }}" min="1" max="9999">
                </div>
            </div>
        </div>

        {{-- FONCTIONNALITÉS ──────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Fonctionnalités incluses</div>
            <div class="toggle-grid">
                @foreach([
                    'has_inspections' => ['Visites techniques', '🔍'],
                    'has_repairs'     => ['Réparations & maintenances', '🔧'],
                    'has_infractions' => ['Infractions & amendes', '⚠️'],
                    'has_incidents'   => ['Sinistres & incidents', '🛡'],
                    'has_reports'     => ['Rapports exportables', '📊'],
                    'has_api'         => ['Accès API REST', '🔌'],
                ] as $key => [$label, $icon])
                <label class="toggle-item">
                    <input type="checkbox" name="{{ $key }}" value="1"
                           {{ old($key, $plan->$key) ? 'checked' : '' }}>
                    <span>{{ $icon }} {{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- VISIBILITÉ ────────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Visibilité sur la page d'accueil</div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <label class="toggle-item" style="flex:1;min-width:200px;">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                    <div>
                        <div style="font-size:.83rem;color:#94a3b8;font-weight:600;">Plan visible</div>
                        <div style="font-size:.73rem;color:#475569;margin-top:.1rem;">Affiché sur la page tarifaire publique.</div>
                    </div>
                </label>
                <label class="toggle-item" style="flex:1;min-width:200px;">
                    <input type="checkbox" name="is_featured" value="1"
                           {{ old('is_featured', $plan->is_featured) ? 'checked' : '' }}>
                    <div>
                        <div style="font-size:.83rem;color:#94a3b8;font-weight:600;">Plan recommandé</div>
                        <div style="font-size:.73rem;color:#475569;margin-top:.1rem;">Badge "Le plus populaire" + mise en avant visuelle.</div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;padding-top:.25rem;">
            <a href="{{ route('admin.plans.index') }}"
               style="padding:.6rem 1.2rem;border:1px solid rgba(255,255,255,.1);border-radius:8px;font-size:.875rem;color:#64748b;text-decoration:none;font-weight:500;">
                Annuler
            </a>
            <button type="submit"
                    style="padding:.6rem 1.5rem;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:white;border:none;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Calcul de l'économie annuelle vs mensuel
const priceMonthly = document.getElementById('priceMonthly');
const priceYearly  = document.getElementById('priceYearly');
const savingsHint  = document.getElementById('savingsHint');

function updateSavings() {
    const m = parseFloat(priceMonthly.value) || 0;
    const y = parseFloat(priceYearly.value)  || 0;
    if (m > 0 && y > 0) {
        const annual = m * 12;
        const saved  = annual - y;
        const pct    = Math.round((saved / annual) * 100);
        if (saved > 0) {
            savingsHint.textContent = `Économie : ${saved.toLocaleString('fr-FR')} FCFA/an (${pct}% de remise vs mensuel).`;
        } else {
            savingsHint.textContent = '';
        }
    } else {
        savingsHint.textContent = '';
    }
}

priceMonthly.addEventListener('input', updateSavings);
priceYearly.addEventListener('input', updateSavings);
updateSavings();
</script>
@endpush
