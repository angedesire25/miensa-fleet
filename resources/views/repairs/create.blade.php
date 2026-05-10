@extends('layouts.dashboard')

@section('title', 'Nouvelle réparation')
@section('page-title', 'Réparations — Nouveau bon de réparation')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.form-group{display:flex;flex-direction:column;gap:.4rem;}
.form-label{font-size:.8rem;font-weight:600;color:#374151;}
.form-label .req{color:#ef4444;}
.form-control{padding:.5rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.855rem;color:#0f172a;outline:none;background:#fff;width:100%;box-sizing:border-box;}
.form-control:focus{border-color:#10b981;}
.form-control.is-invalid{border-color:#ef4444;}
.invalid-feedback{font-size:.75rem;color:#ef4444;margin-top:.25rem;}
.btn{padding:.5rem 1rem;border-radius:.45rem;font-size:.855rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;}
.btn-danger:hover{background:#fee2e2;}
.info-banner{background:#eff6ff;border:1px solid #bfdbfe;border-radius:.55rem;padding:.8rem 1rem;font-size:.83rem;color:#1e40af;margin-bottom:1.25rem;display:flex;gap:.5rem;align-items:flex-start;}
/* ── Carrosserie pills ── */
.body-pills{display:flex;flex-wrap:wrap;gap:.5rem;}
.body-pills input[type=radio]{display:none;}
.body-pills label{padding:.35rem .85rem;border-radius:99px;border:1.5px solid #e2e8f0;font-size:.8rem;font-weight:500;color:#374151;cursor:pointer;transition:all .15s;}
.body-pills input[type=radio]:checked + label{background:#10b981;border-color:#10b981;color:#fff;}
/* ── Fault codes table ── */
.fc-table{width:100%;border-collapse:collapse;font-size:.83rem;}
.fc-table th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;padding:.45rem .65rem;text-align:left;border-bottom:1.5px solid #e2e8f0;}
.fc-table td{padding:.5rem .65rem;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.code-badge{display:inline-block;padding:.2rem .55rem;border-radius:99px;font-size:.75rem;font-weight:700;background:#eff6ff;color:#1e40af;letter-spacing:.04em;}
[x-cloak]{display:none!important;}
</style>

<div class="info-banner">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.05rem;"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    <span>Pour un sinistre existant, vous pouvez aussi utiliser le bouton <strong>« Envoyer au garage »</strong> depuis la fiche sinistre. Ce formulaire est réservé aux réparations préventives ou sans sinistre déclaré.</span>
</div>

<form method="POST" action="{{ route('repairs.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card">
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#10b981" stroke-width="1.8"/></svg>
            <span class="card-title">Informations de la réparation</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">

                {{-- Véhicule --}}
                <div class="form-group">
                    <label class="form-label">Véhicule <span class="req">*</span></label>
                    <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un véhicule…</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id') == $vehicle->id)>
                                {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                                @if($vehicle->status !== 'available') ({{ $vehicle->status }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Garage --}}
                <div class="form-group">
                    <label class="form-label">Garage <span class="req">*</span></label>
                    <select name="garage_id" class="form-control @error('garage_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un garage…</option>
                        @foreach($garages as $garage)
                            <option value="{{ $garage->id }}" @selected(old('garage_id') == $garage->id)>
                                {{ $garage->name }} — {{ $garage->city }}
                            </option>
                        @endforeach
                    </select>
                    @error('garage_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    @if($garages->isEmpty())
                        <span style="font-size:.75rem;color:#f59e0b;">
                            Aucun garage approuvé. <a href="{{ route('garages.create') }}" style="color:#10b981;">Ajouter un garage</a>.
                        </span>
                    @endif
                </div>

                {{-- Type de réparation --}}
                <div class="form-group">
                    <label class="form-label">Type de réparation <span class="req">*</span></label>
                    <select name="repair_type" class="form-control @error('repair_type') is-invalid @enderror" required>
                        <option value="">Sélectionner…</option>
                        <option value="corrective" @selected(old('repair_type')==='corrective')>Corrective (Retour Atelier)</option>
                        <option value="preventive" @selected(old('repair_type')==='preventive')>Réglementaire (Révision)</option>
                        <option value="warranty"   @selected(old('repair_type')==='warranty')>Sous Garantie</option>
                        <option value="recall"     @selected(old('repair_type')==='recall')>Rappel Constructeur</option>
                    </select>
                    @error('repair_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Date d'envoi --}}
                <div class="form-group">
                    <label class="form-label">Date d'envoi au garage <span class="req">*</span></label>
                    <input type="datetime-local" name="datetime_sent" class="form-control @error('datetime_sent') is-invalid @enderror"
                           value="{{ old('datetime_sent', now()->format('Y-m-d\TH:i')) }}" required>
                    @error('datetime_sent') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Kilométrage --}}
                <div class="form-group">
                    <label class="form-label">Kilométrage au départ</label>
                    <input type="number" name="km_at_departure" class="form-control @error('km_at_departure') is-invalid @enderror"
                           min="0" value="{{ old('km_at_departure') }}" placeholder="km">
                    @error('km_at_departure') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Devis --}}
                <div class="form-group">
                    <label class="form-label">Montant du devis (FCFA)</label>
                    <input type="number" name="quote_amount" class="form-control"
                           min="0" step="1000" value="{{ old('quote_amount') }}" placeholder="Optionnel">
                </div>

                {{-- Sinistre lié (optionnel) --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Sinistre lié <span style="font-weight:400;color:#94a3b8;">(optionnel — si cette réparation fait suite à un sinistre ouvert)</span></label>
                    <select name="incident_id" class="form-control @error('incident_id') is-invalid @enderror">
                        <option value="">— Réparation préventive / sans sinistre —</option>
                        @foreach($incidents as $incident)
                            <option value="{{ $incident->id }}" @selected(old('incident_id') == $incident->id)>
                                #{{ $incident->id }} — {{ $incident->vehicle?->plate }} — {{ ucfirst(str_replace('_',' ',$incident->type)) }}
                                ({{ $incident->datetime_occurred?->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('incident_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- État au départ --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">État du véhicule au départ</label>
                    <textarea name="condition_at_departure" class="form-control" rows="2"
                              placeholder="Description de l'état au moment de l'envoi au garage…">{{ old('condition_at_departure') }}</textarea>
                </div>

                {{-- Notes --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Notes / instructions pour le garage</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Instructions particulières, pièces à vérifier…">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Section Informations DI ──────────────────────────────────────── --}}
    <div class="card">
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#10b981" stroke-width="1.8"/><path d="M7 8h10M7 12h10M7 16h6" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="card-title">Informations DI</span>
            <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;">Numéro généré automatiquement à la création</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                {{-- Type carrosserie --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Type de carrosserie</label>
                    <div class="body-pills">
                        @foreach(['Berline','SUV','Pick-up','Utilitaire','Camion','Autre'] as $bt)
                        <div>
                            <input type="radio" name="vehicle_type_body" id="bt_{{ Str::slug($bt) }}" value="{{ $bt }}"
                                   @checked(old('vehicle_type_body') === $bt)>
                            <label for="bt_{{ Str::slug($bt) }}">{{ $bt }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Référence OR initial --}}
                <div class="form-group">
                    <label class="form-label">Référence OR initial</label>
                    <input type="text" name="or_initial_reference" class="form-control"
                           value="{{ old('or_initial_reference') }}" placeholder="N/A si vide">
                </div>

                {{-- Date de disponibilité souhaitée --}}
                <div class="form-group">
                    <label class="form-label">Date de disponibilité souhaitée</label>
                    <input type="date" name="availability_date_requested" class="form-control"
                           value="{{ old('availability_date_requested') }}" min="{{ now()->toDateString() }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Inventaire des dysfonctionnements ──────────────────────────────── --}}
    <div class="card" x-data="faultCodesApp()" x-cloak>
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#f59e0b" stroke-width="1.8"/></svg>
            <span class="card-title">Inventaire des dysfonctionnements</span>
            <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;" x-text="faultCodes.length + ' ligne(s)'"></span>
        </div>
        <div style="padding:1.25rem;">
            {{-- Tableau --}}
            <div style="overflow-x:auto;">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th style="width:70px;">Code</th>
                            <th style="width:160px;">Catégorie</th>
                            <th>Libellé de l'anomalie</th>
                            <th style="width:44px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(fc, idx) in faultCodes" :key="idx">
                            <tr>
                                <td>
                                    <span class="code-badge" x-text="codePreview(idx)"></span>
                                    <input type="hidden" :name="`fault_codes[${idx}][category]`" :value="fc.category">
                                    <input type="hidden" :name="`fault_codes[${idx}][label]`"    :value="fc.label">
                                </td>
                                <td>
                                    <select class="form-control" x-model="fc.category" style="font-size:.8rem;padding:.35rem .6rem;">
                                        <option value="breakdown">Panne</option>
                                        <option value="anomaly">Anomalie</option>
                                        <option value="wear">Usure</option>
                                        <option value="accident">Accident</option>
                                        <option value="other">Autre</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control" x-model="fc.label"
                                           placeholder="Ex : Climatisation inopérante" style="font-size:.83rem;">
                                </td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn btn-danger" style="padding:.3rem .5rem;font-size:.75rem;"
                                            @click="removeCode(idx)" title="Supprimer">🗑</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="faultCodes.length === 0">
                            <td colspan="4" style="text-align:center;color:#94a3b8;font-size:.83rem;padding:1rem;">
                                Aucun dysfonctionnement ajouté. Cliquez sur "+ Ajouter" pour commencer.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:.85rem;">
                <button type="button" class="btn btn-ghost" @click="addCode()">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Ajouter une anomalie
                </button>
            </div>
        </div>
    </div>

    {{-- Photos --}}
    @include('partials._photo_upload', [
        'contextOptions' => ['repair_in_progress' => 'En cours de réparation', 'repair_after' => 'Après réparation'],
        'defaultContext' => 'repair_in_progress',
        'existingPhotos' => collect(),
    ])

    {{-- Actions --}}
    <div style="display:flex;gap:.75rem;justify-content:flex-end;padding-bottom:1rem;">
        <a href="{{ route('repairs.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.8"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="1.8"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="1.8"/></svg>
            Créer le bon de réparation
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
<script>
const FC_PREFIXES = { breakdown:'PN', anomaly:'AN', wear:'US', accident:'AC', other:'AU' };

function faultCodesApp() {
    return {
        faultCodes: @json(old('fault_codes', [])),
        addCode() {
            this.faultCodes.push({ category: 'breakdown', label: '' });
        },
        removeCode(idx) {
            this.faultCodes.splice(idx, 1);
        },
        codePreview(idx) {
            const fc     = this.faultCodes[idx];
            const prefix = FC_PREFIXES[fc.category] || 'AU';
            const count  = this.faultCodes.slice(0, idx).filter(f => f.category === fc.category).length;
            return prefix + String(count + 1).padStart(2, '0');
        },
    };
}
</script>
@endpush
