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
.info-banner{background:#eff6ff;border:1px solid #bfdbfe;border-radius:.55rem;padding:.8rem 1rem;font-size:.83rem;color:#1e40af;margin-bottom:1.25rem;display:flex;gap:.5rem;align-items:flex-start;}
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
                        <option value="body_repair"  @selected(old('repair_type')==='body_repair')>Carrosserie</option>
                        <option value="mechanical"   @selected(old('repair_type')==='mechanical')>Mécanique</option>
                        <option value="electrical"   @selected(old('repair_type')==='electrical')>Électrique</option>
                        <option value="tire"         @selected(old('repair_type')==='tire')>Pneus</option>
                        <option value="painting"     @selected(old('repair_type')==='painting')>Peinture</option>
                        <option value="glass"        @selected(old('repair_type')==='glass')>Vitrage</option>
                        <option value="full_service" @selected(old('repair_type')==='full_service')>Révision complète</option>
                        <option value="other"        @selected(old('repair_type')==='other')>Autre</option>
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
