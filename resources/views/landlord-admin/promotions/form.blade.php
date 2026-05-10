@extends('landlord-admin.layouts.app')

@php $editing = isset($promotion); @endphp

@section('title', $editing ? 'Modifier la promotion' : 'Nouvelle promotion')
@section('page-title', 'Promotions')
@section('breadcrumb', $editing ? 'Modifier · '.$promotion->label : 'Nouvelle promotion')

@push('styles')
<style>
    .promo-form { max-width: 720px; }
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
    .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .field { display: flex; flex-direction: column; gap: .35rem; }
    .field label { font-size: .82rem; font-weight: 600; color: #94a3b8; }
    .field input, .field select, .field textarea {
        background: #0f172a; border: 1px solid rgba(255,255,255,.1);
        border-radius: 7px; padding: .55rem .85rem;
        color: #e2e8f0; font-size: .875rem; font-family: inherit;
        transition: border-color .15s; outline: none; width: 100%; box-sizing: border-box;
    }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: #3b82f6; }
    .field textarea { resize: vertical; min-height: 65px; }
    .field .hint { font-size: .73rem; color: #475569; margin-top: .15rem; }

    .radio-group { display: flex; gap: .5rem; }
    .radio-opt {
        flex: 1; display: flex; flex-direction: column; align-items: center; gap: .35rem;
        background: #0f172a; border: 1.5px solid rgba(255,255,255,.08);
        border-radius: 8px; padding: .75rem; cursor: pointer; transition: border-color .15s;
    }
    .radio-opt:has(input:checked) { border-color: #3b82f6; background: rgba(59,130,246,.08); }
    .radio-opt input { display: none; }
    .radio-opt .icon { font-size: 1.3rem; }
    .radio-opt .lbl  { font-size: .8rem; color: #94a3b8; font-weight: 600; text-align: center; }
    .radio-opt:has(input:checked) .lbl { color: #93c5fd; }

    .preview-box {
        background: #0f172a; border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px; padding: 1rem; margin-top: 1rem;
    }
</style>
@endpush

@section('content')
<div class="promo-form">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('admin.promotions.index') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:#1e293b;border:1px solid rgba(255,255,255,.07);border-radius:.45rem;color:#64748b;text-decoration:none;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
        <h1 style="font-size:1.1rem;font-weight:700;color:#f1f5f9;margin:0;">
            {{ $editing ? 'Modifier la promotion' : 'Créer une promotion' }}
        </h1>
    </div>

    <form method="POST"
          action="{{ $editing ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        {{-- INFOS GÉNÉRALES ──────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Informations</div>
            <div class="field" style="margin-bottom:1rem;">
                <label>Libellé interne <span style="color:#ef4444">*</span></label>
                <input type="text" name="label"
                       value="{{ old('label', $editing ? $promotion->label : '') }}"
                       required maxlength="120" placeholder="Ex: Promo lancement Avril 2026">
                <span class="hint">Visible uniquement dans ce panel.</span>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Badge affiché sur la carte</label>
                    <input type="text" name="badge_text"
                           value="{{ old('badge_text', $editing ? $promotion->badge_text : '') }}"
                           maxlength="40" placeholder="Ex: 🎉 -20% ce mois" id="badgeInput">
                    <span class="hint">Court, percutant. Laissez vide pour afficher la remise automatiquement.</span>
                </div>
                <div class="field">
                    <label>Message d'accompagnement</label>
                    <input type="text" name="description"
                           value="{{ old('description', $editing ? $promotion->description : '') }}"
                           maxlength="300" placeholder="Ex: Offre réservée aux nouvelles entreprises">
                </div>
            </div>
        </div>

        {{-- REMISE ──────────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Remise</div>
            <div class="field" style="margin-bottom:1rem;">
                <label>Type de remise</label>
                <div class="radio-group">
                    <label class="radio-opt">
                        <input type="radio" name="discount_type" value="percent"
                               {{ old('discount_type', $editing ? $promotion->discount_type : 'percent') === 'percent' ? 'checked' : '' }}
                               onchange="updateDiscountHint()">
                        <span class="icon">%</span>
                        <span class="lbl">Pourcentage</span>
                    </label>
                    <label class="radio-opt">
                        <input type="radio" name="discount_type" value="fixed"
                               {{ old('discount_type', $editing ? $promotion->discount_type : '') === 'fixed' ? 'checked' : '' }}
                               onchange="updateDiscountHint()">
                        <span class="icon">₣</span>
                        <span class="lbl">Montant fixe (FCFA)</span>
                    </label>
                </div>
            </div>
            <div class="field">
                <label>Valeur de la remise <span style="color:#ef4444">*</span></label>
                <input type="number" name="discount_value"
                       value="{{ old('discount_value', $editing ? $promotion->discount_value : '') }}"
                       required min="0.01" step="0.01" id="discountValue"
                       placeholder="Ex: 20 pour 20% ou 10000 pour 10 000 FCFA">
                <span class="hint" id="discountHint"></span>
            </div>
        </div>

        {{-- APPLICABILITÉ ────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Applicabilité</div>
            <div class="field-row">
                <div class="field">
                    <label>Plan ciblé</label>
                    <select name="plan_id">
                        <option value="">— Tous les plans —</option>
                        @foreach($plans as $plan)
                        <option value="{{ $plan->id }}"
                            {{ old('plan_id', $editing ? $promotion->plan_id : '') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }}
                        </option>
                        @endforeach
                    </select>
                    <span class="hint">Laisser vide pour appliquer à tous les plans.</span>
                </div>
                <div class="field">
                    <label>Période de facturation</label>
                    <select name="billing_period">
                        <option value="all"     {{ old('billing_period', $editing ? $promotion->billing_period : 'all') === 'all'     ? 'selected' : '' }}>Mensuel & annuel</option>
                        <option value="monthly" {{ old('billing_period', $editing ? $promotion->billing_period : '') === 'monthly' ? 'selected' : '' }}>Mensuel uniquement</option>
                        <option value="yearly"  {{ old('billing_period', $editing ? $promotion->billing_period : '') === 'yearly'  ? 'selected' : '' }}>Annuel uniquement</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- VALIDITÉ ─────────────────────────────────────────────────── --}}
        <div class="form-section">
            <div class="form-section-title">Période de validité</div>
            <div class="field-row">
                <div class="field">
                    <label>Date de début</label>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', $editing && $promotion->starts_at ? $promotion->starts_at->format('Y-m-d\TH:i') : '') }}">
                    <span class="hint">Laisser vide = active immédiatement.</span>
                </div>
                <div class="field">
                    <label>Date de fin</label>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', $editing && $promotion->ends_at ? $promotion->ends_at->format('Y-m-d\TH:i') : '') }}">
                    <span class="hint">Laisser vide = sans limite de temps.</span>
                </div>
            </div>

            <div style="margin-top:1rem;">
                <label style="display:flex;align-items:center;gap:.65rem;cursor:pointer;background:#0f172a;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:.75rem 1rem;">
                    <input type="checkbox" name="is_active" value="1" style="accent-color:#10b981;width:15px;height:15px;"
                           {{ old('is_active', $editing ? $promotion->is_active : true) ? 'checked' : '' }}>
                    <div>
                        <div style="font-size:.83rem;color:#94a3b8;font-weight:600;">Promotion active</div>
                        <div style="font-size:.73rem;color:#475569;margin-top:.1rem;">Désactiver pour préparer la promo sans la publier encore.</div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <a href="{{ route('admin.promotions.index') }}"
               style="padding:.6rem 1.2rem;border:1px solid rgba(255,255,255,.1);border-radius:8px;font-size:.875rem;color:#64748b;text-decoration:none;font-weight:500;">
                Annuler
            </a>
            <button type="submit"
                    style="padding:.6rem 1.5rem;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:white;border:none;border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;">
                {{ $editing ? 'Enregistrer les modifications' : 'Créer la promotion' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateDiscountHint() {
    const type  = document.querySelector('input[name="discount_type"]:checked')?.value ?? 'percent';
    const hint  = document.getElementById('discountHint');
    const value = parseFloat(document.getElementById('discountValue').value) || 0;

    if (type === 'percent') {
        hint.style.color = '#86efac';
        hint.textContent = value > 0 ? `La remise sera de ${value}% sur le prix affiché.` : 'Saisissez un pourcentage (ex: 20 pour -20%).';
    } else {
        hint.style.color = '#86efac';
        hint.textContent = value > 0
            ? `La remise sera de ${value.toLocaleString('fr-FR')} FCFA sur le prix affiché.`
            : 'Saisissez un montant en FCFA.';
    }
}

document.querySelectorAll('input[name="discount_type"]').forEach(r => r.addEventListener('change', updateDiscountHint));
document.getElementById('discountValue').addEventListener('input', updateDiscountHint);
updateDiscountHint();
</script>
@endpush
