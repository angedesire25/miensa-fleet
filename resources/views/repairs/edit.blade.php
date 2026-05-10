@extends('layouts.dashboard')

@section('title', 'Modifier — ' . $repair->di_number_formatted)
@section('page-title', 'Réparation — ' . $repair->di_number_formatted)

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
.form-control:read-only{background:#f8fafc;color:#64748b;}
.btn{padding:.5rem 1rem;border-radius:.45rem;font-size:.855rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;}
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

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
    <div>
        @if($repair->di_number)
            <span style="font-family:monospace;font-size:.85rem;background:#f0f9ff;color:#0369a1;padding:.25rem .65rem;border-radius:99px;border:1px solid #bae6fd;">
                {{ $repair->di_number }}
            </span>
        @endif
    </div>
    <a href="{{ route('repairs.show', $repair) }}" class="btn btn-ghost">← Retour à la fiche</a>
</div>

<form method="POST" action="{{ route('repairs.update', $repair) }}">
    @csrf @method('PUT')

    {{-- ── Informations générales ──────────────────────────────────────── --}}
    <div class="card">
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#10b981" stroke-width="1.8"/></svg>
            <span class="card-title">Informations générales</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                {{-- Véhicule (readonly) --}}
                <div class="form-group">
                    <label class="form-label">Véhicule</label>
                    <input type="text" class="form-control" readonly
                           value="{{ $repair->vehicle?->plate }} — {{ $repair->vehicle?->brand }} {{ $repair->vehicle?->model }}">
                </div>

                {{-- Garage --}}
                <div class="form-group">
                    <label class="form-label">Garage</label>
                    <select name="garage_id" class="form-control">
                        @foreach($garages as $garage)
                            <option value="{{ $garage->id }}" @selected(old('garage_id', $repair->garage_id) == $garage->id)>
                                {{ $garage->name }} — {{ $garage->city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Type de réparation --}}
                <div class="form-group">
                    <label class="form-label">Type de réparation</label>
                    <select name="repair_type" class="form-control">
                        @foreach(['corrective'=>'Corrective (Retour Atelier)','preventive'=>'Réglementaire (Révision)','warranty'=>'Sous Garantie','recall'=>'Rappel Constructeur'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('repair_type', $repair->repair_type) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Devis --}}
                <div class="form-group">
                    <label class="form-label">Montant du devis (FCFA)</label>
                    <input type="number" name="quote_amount" class="form-control" min="0" step="1000"
                           value="{{ old('quote_amount', $repair->quote_amount) }}">
                </div>

                {{-- Notes --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $repair->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Informations DI ──────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#10b981" stroke-width="1.8"/><path d="M7 8h10M7 12h10M7 16h6" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="card-title">Informations DI</span>
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
                                   @checked(old('vehicle_type_body', $repair->vehicle_type_body) === $bt)>
                            <label for="bt_{{ Str::slug($bt) }}">{{ $bt }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Référence OR initial --}}
                <div class="form-group">
                    <label class="form-label">Référence OR initial</label>
                    <input type="text" name="or_initial_reference" class="form-control"
                           value="{{ old('or_initial_reference', $repair->or_initial_reference) }}" placeholder="N/A si vide">
                </div>

                {{-- Date de disponibilité souhaitée --}}
                <div class="form-group">
                    <label class="form-label">Date de disponibilité souhaitée</label>
                    <input type="date" name="availability_date_requested" class="form-control"
                           value="{{ old('availability_date_requested', $repair->availability_date_requested?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Inventaire des dysfonctionnements ────────────────────────────── --}}
    <div class="card" x-data="faultCodesApp()" x-cloak>
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#f59e0b" stroke-width="1.8"/></svg>
            <span class="card-title">Inventaire des dysfonctionnements</span>
            <span style="margin-left:auto;font-size:.75rem;color:#94a3b8;" x-text="faultCodes.length + ' ligne(s)'"></span>
        </div>
        <div style="padding:1.25rem;">
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
                                    <span class="code-badge" x-text="codePreview(idx, fc)"></span>
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
                                Aucun dysfonctionnement. Cliquez sur "+ Ajouter" pour en créer un.
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

    {{-- Actions --}}
    <div style="display:flex;gap:.75rem;justify-content:flex-end;padding-bottom:1rem;">
        <a href="{{ route('repairs.show', $repair) }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.8"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="1.8"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="1.8"/></svg>
            Enregistrer les modifications
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
<script>
const FC_PREFIXES = { breakdown:'PN', anomaly:'AN', wear:'US', accident:'AC', other:'AU' };

function faultCodesApp() {
    const existing = @json($repair->faultCodes->map(fn($fc) => ['category' => $fc->category, 'label' => $fc->label]));
    return {
        faultCodes: existing.length ? existing : [],
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
