{{--
    Formulaire partagé création / édition sinistre.
    Variables attendues :
      $incident   — null (création) ou instance Incident (édition)
      $vehicles   — Collection Vehicle
      $drivers    — Collection Driver
      $action     — URL de soumission
      $method     — 'POST' ou 'PUT'
--}}
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
.section-divider{font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;padding:.75rem 0 .35rem;margin-top:.5rem;border-top:1px solid #f1f5f9;}
.toggle-group{display:flex;align-items:center;gap:.6rem;}
.toggle-check{width:16px;height:16px;accent-color:#10b981;cursor:pointer;}
</style>

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    {{-- ── Informations principales ──────────────────────────────────────── --}}
    <div class="card">
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#10b981" stroke-width="1.8"/></svg>
            <span class="card-title">Informations du sinistre</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                {{-- Véhicule --}}
                <div class="form-group">
                    <label class="form-label">Véhicule <span class="req">*</span></label>
                    <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror" required>
                        <option value="">Sélectionner un véhicule…</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $incident?->vehicle_id) == $vehicle->id)>
                                {{ $vehicle->plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Chauffeur --}}
                <div class="form-group">
                    <label class="form-label">Chauffeur au volant</label>
                    <select name="driver_id" class="form-control @error('driver_id') is-invalid @enderror">
                        <option value="">— Aucun / collaborateur —</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected(old('driver_id', $incident?->driver_id) == $driver->id)>
                                {{ $driver->full_name }} ({{ $driver->matricule }})
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Type --}}
                <div class="form-group">
                    <label class="form-label">Type d'incident <span class="req">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="">Sélectionner…</option>
                        @foreach([
                            'accident'         => 'Accident',
                            'breakdown'        => 'Panne mécanique',
                            'flat_tire'        => 'Crevaison / pneu',
                            'electrical_fault' => 'Panne électrique',
                            'body_damage'      => 'Dommage carrosserie',
                            'theft_attempt'    => 'Tentative de vol',
                            'theft'            => 'Vol',
                            'flood_damage'     => 'Dommage inondation',
                            'fire'             => 'Incendie',
                            'vandalism'        => 'Vandalisme',
                            'other'            => 'Autre',
                        ] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('type', $incident?->type) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Sévérité --}}
                <div class="form-group">
                    <label class="form-label">Sévérité <span class="req">*</span></label>
                    <select name="severity" class="form-control @error('severity') is-invalid @enderror" required>
                        <option value="">Évaluer la sévérité…</option>
                        <option value="minor"      @selected(old('severity', $incident?->severity) === 'minor')>Mineur</option>
                        <option value="moderate"   @selected(old('severity', $incident?->severity) === 'moderate')>Modéré</option>
                        <option value="major"      @selected(old('severity', $incident?->severity) === 'major')>Majeur</option>
                        <option value="total_loss" @selected(old('severity', $incident?->severity) === 'total_loss')>Perte totale</option>
                    </select>
                    @error('severity') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Date/heure --}}
                <div class="form-group">
                    <label class="form-label">Date et heure de l'incident <span class="req">*</span></label>
                    <input type="datetime-local" name="datetime_occurred" class="form-control @error('datetime_occurred') is-invalid @enderror"
                           value="{{ old('datetime_occurred', $incident?->datetime_occurred?->format('Y-m-d\TH:i')) }}" required>
                    @error('datetime_occurred') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Lieu --}}
                <div class="form-group">
                    <label class="form-label">Lieu de l'incident</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                           value="{{ old('location', $incident?->location) }}" placeholder="Adresse, intersection, km…">
                    @error('location') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Description --}}
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Description <span class="req">*</span></label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                          placeholder="Décrivez les circonstances de l'incident…">{{ old('description', $incident?->description) }}</textarea>
                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            {{-- Checkboxes --}}
            <div style="display:flex;gap:2rem;margin-top:1rem;flex-wrap:wrap;">
                <label class="toggle-group">
                    <input type="hidden" name="vehicle_immobilized" value="0">
                    <input type="checkbox" name="vehicle_immobilized" value="1" class="toggle-check"
                           @checked(old('vehicle_immobilized', $incident?->vehicle_immobilized))>
                    <span style="font-size:.855rem;font-weight:500;color:#374151;">Véhicule immobilisé</span>
                </label>
                <label class="toggle-group">
                    <input type="hidden" name="third_party_involved" value="0">
                    <input type="checkbox" name="third_party_involved" value="1" class="toggle-check" id="chk-tiers"
                           @checked(old('third_party_involved', $incident?->third_party_involved))>
                    <span style="font-size:.855rem;font-weight:500;color:#374151;">Tiers impliqué</span>
                </label>
                <label class="toggle-group">
                    <input type="hidden" name="insurance_declared" value="0">
                    <input type="checkbox" name="insurance_declared" value="1" class="toggle-check" id="chk-assurance"
                           @checked(old('insurance_declared', $incident?->insurance_declared))>
                    <span style="font-size:.855rem;font-weight:500;color:#374151;">Déclaré à l'assurance</span>
                </label>
            </div>
        </div>
    </div>

    {{-- ── Tiers impliqué ────────────────────────────────────────────────── --}}
    <div class="card" id="tiers-section" style="{{ old('third_party_involved', $incident?->third_party_involved) ? '' : 'display:none;' }}">
        <div class="card-head">
            <span class="card-title">Informations du tiers</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nom du tiers</label>
                    <input type="text" name="third_party_name" class="form-control"
                           value="{{ old('third_party_name', $incident?->third_party_name) }}" placeholder="Nom et prénom">
                </div>
                <div class="form-group">
                    <label class="form-label">Plaque du tiers</label>
                    <input type="text" name="third_party_plate" class="form-control"
                           value="{{ old('third_party_plate', $incident?->third_party_plate) }}" placeholder="Immatriculation">
                </div>
                <div class="form-group">
                    <label class="form-label">Assurance du tiers</label>
                    <input type="text" name="third_party_insurance" class="form-control"
                           value="{{ old('third_party_insurance', $incident?->third_party_insurance) }}" placeholder="Compagnie d'assurance">
                </div>
                <div class="form-group">
                    <label class="form-label">N° procès-verbal (PV)</label>
                    <input type="text" name="police_report_number" class="form-control"
                           value="{{ old('police_report_number', $incident?->police_report_number) }}" placeholder="Numéro de PV de police">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Assurance ─────────────────────────────────────────────────────── --}}
    <div class="card" id="assurance-section" style="{{ old('insurance_declared', $incident?->insurance_declared) ? '' : 'display:none;' }}">
        <div class="card-head">
            <span class="card-title">Déclaration d'assurance</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">N° de sinistre assurance</label>
                    <input type="text" name="insurance_claim_number" class="form-control"
                           value="{{ old('insurance_claim_number', $incident?->insurance_claim_number) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date de déclaration</label>
                    <input type="date" name="insurance_declaration_date" class="form-control"
                           value="{{ old('insurance_declaration_date', $incident?->insurance_declaration_date?->format('Y-m-d')) }}">
                </div>
                @if($incident)
                <div class="form-group">
                    <label class="form-label">Montant réclamé (FCFA)</label>
                    <input type="number" name="insurance_amount_claimed" class="form-control" min="0" step="1000"
                           value="{{ old('insurance_amount_claimed', $incident?->insurance_amount_claimed) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Montant reçu (FCFA)</label>
                    <input type="number" name="insurance_amount_received" class="form-control" min="0" step="1000"
                           value="{{ old('insurance_amount_received', $incident?->insurance_amount_received) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Statut assurance</label>
                    <select name="insurance_status" class="form-control">
                        <option value="">—</option>
                        <option value="not_declared" @selected(old('insurance_status', $incident?->insurance_status)==='not_declared')>Non déclarée</option>
                        <option value="pending"      @selected(old('insurance_status', $incident?->insurance_status)==='pending')>En attente</option>
                        <option value="accepted"     @selected(old('insurance_status', $incident?->insurance_status)==='accepted')>Acceptée</option>
                        <option value="rejected"     @selected(old('insurance_status', $incident?->insurance_status)==='rejected')>Rejetée</option>
                        <option value="partial"      @selected(old('insurance_status', $incident?->insurance_status)==='partial')>Partielle</option>
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Coûts ─────────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Estimation des coûts</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Coût estimé de réparation (FCFA)</label>
                    <input type="number" name="estimated_repair_cost" class="form-control @error('estimated_repair_cost') is-invalid @enderror"
                           min="0" step="1000" value="{{ old('estimated_repair_cost', $incident?->estimated_repair_cost) }}">
                    @error('estimated_repair_cost') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                @if($incident)
                <div class="form-group">
                    <label class="form-label">Coût réel de réparation (FCFA)</label>
                    <input type="number" name="actual_repair_cost" class="form-control @error('actual_repair_cost') is-invalid @enderror"
                           min="0" step="1000" value="{{ old('actual_repair_cost', $incident?->actual_repair_cost) }}">
                    @error('actual_repair_cost') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Photos --}}
    @include('partials._photo_upload', [
        'contextOptions'   => ['incident_before' => 'État avant', 'incident_damage' => 'Dégâts constatés'],
        'defaultContext'   => 'incident_damage',
        'existingPhotos'   => $incident?->photos ?? collect(),
        'deleteRoute'      => $incident ? 'incidents.delete-photo' : null,
        'deleteRouteParam' => $incident,
    ])

    {{-- Actions --}}
    <div style="display:flex;gap:.75rem;justify-content:flex-end;padding-bottom:1rem;">
        <a href="{{ $incident ? route('incidents.show', $incident) : route('incidents.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.8"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="1.8"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="1.8"/></svg>
            {{ $incident ? 'Enregistrer les modifications' : 'Déclarer le sinistre' }}
        </button>
    </div>
</form>

<script>
// Afficher/masquer les sections conditionnelles
document.getElementById('chk-tiers').addEventListener('change', function() {
    document.getElementById('tiers-section').style.display = this.checked ? '' : 'none';
});
document.getElementById('chk-assurance').addEventListener('change', function() {
    document.getElementById('assurance-section').style.display = this.checked ? '' : 'none';
});
</script>
