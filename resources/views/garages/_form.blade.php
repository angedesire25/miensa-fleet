{{--
    Formulaire partagé création / édition garage.
    Variables attendues :
      $garage   — null (création) ou instance Garage (édition)
      $action   — URL de soumission
      $method   — 'POST' ou 'PUT'
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
.spec-pill{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border-radius:99px;border:1.5px solid #e2e8f0;font-size:.78rem;cursor:pointer;user-select:none;transition:all .15s;}
.spec-pill.active{background:#10b981;color:#fff;border-color:#10b981;}
</style>

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="card">
        <div class="card-head">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="#10b981" stroke-width="1.8"/><polyline points="9,22 9,12 15,12 15,22" stroke="#10b981" stroke-width="1.8"/></svg>
            <span class="card-title">Informations du garage</span>
        </div>
        <div style="padding:1.25rem;">
            <div class="form-grid">
                {{-- Nom --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Nom du garage <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $garage?->name) }}" placeholder="Auto Service Central…" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Type --}}
                <div class="form-group">
                    <label class="form-label">Type <span class="req">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="">Sélectionner…</option>
                        <option value="general"     @selected(old('type', $garage?->type)==='general')>Général</option>
                        <option value="body_repair" @selected(old('type', $garage?->type)==='body_repair')>Carrosserie</option>
                        <option value="electrical"  @selected(old('type', $garage?->type)==='electrical')>Électrique</option>
                        <option value="tires"       @selected(old('type', $garage?->type)==='tires')>Pneus</option>
                        <option value="painting"    @selected(old('type', $garage?->type)==='painting')>Peinture</option>
                        <option value="glass"       @selected(old('type', $garage?->type)==='glass')>Vitrage</option>
                        <option value="specialized" @selected(old('type', $garage?->type)==='specialized')>Spécialisé</option>
                    </select>
                    @error('type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Note (étoiles) --}}
                <div class="form-group">
                    <label class="form-label">Note (1–5)</label>
                    <select name="rating" class="form-control @error('rating') is-invalid @enderror">
                        <option value="">— Non évalué —</option>
                        @for($i=1;$i<=5;$i++)
                            <option value="{{ $i }}" @selected(old('rating', $garage?->rating) == $i)>
                                {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5-$i) }} ({{ $i }}/5)
                            </option>
                        @endfor
                    </select>
                    @error('rating') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                {{-- Ville --}}
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="city" class="form-control"
                           value="{{ old('city', $garage?->city) }}" placeholder="Abidjan, Dakar…">
                </div>

                {{-- Adresse --}}
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <input type="text" name="address" class="form-control"
                           value="{{ old('address', $garage?->address) }}" placeholder="Rue, quartier…">
                </div>

                {{-- Téléphone --}}
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-control"
                           value="{{ old('phone', $garage?->phone) }}" placeholder="+225 07 XX XX XX XX">
                </div>

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email', $garage?->email) }}" placeholder="contact@garage.ci">
                </div>

                {{-- Contact --}}
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Personne de contact</label>
                    <input type="text" name="contact_person" class="form-control"
                           value="{{ old('contact_person', $garage?->contact_person) }}" placeholder="Nom du responsable">
                </div>
            </div>

            {{-- Spécialisations --}}
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Spécialisations</label>
                @php
                    $allSpecs = [
                        'body'        => 'Carrosserie',
                        'mechanical'  => 'Mécanique',
                        'electrical'  => 'Électrique',
                        'tires'       => 'Pneus',
                        'painting'    => 'Peinture',
                        'glass'       => 'Vitrage',
                        'ac'          => 'Climatisation',
                        'brakes'      => 'Freinage',
                    ];
                    $currentSpecs = old('specializations', $garage?->specializations ?? []);
                @endphp
                <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.3rem;" id="spec-container">
                    @foreach($allSpecs as $val => $lbl)
                    <span class="spec-pill {{ in_array($val, $currentSpecs) ? 'active' : '' }}"
                          onclick="toggleSpec('{{ $val }}', this)">
                        {{ $lbl }}
                    </span>
                    @endforeach
                </div>
                <div id="spec-inputs">
                    @foreach($currentSpecs as $spec)
                    <input type="hidden" name="specializations[]" value="{{ $spec }}" class="spec-input" data-val="{{ $spec }}">
                    @endforeach
                </div>
            </div>

            {{-- Approuvé --}}
            <div style="margin-top:1rem;">
                <label style="display:flex;align-items:center;gap:.6rem;cursor:pointer;">
                    <input type="hidden" name="is_approved" value="0">
                    <input type="checkbox" name="is_approved" value="1" style="width:16px;height:16px;accent-color:#10b981;"
                           @checked(old('is_approved', $garage?->is_approved))>
                    <span style="font-size:.855rem;font-weight:500;color:#374151;">Garage approuvé (visible dans la liste de sélection)</span>
                </label>
            </div>

            {{-- Notes --}}
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Notes internes</label>
                <textarea name="notes" rows="3" class="form-control"
                          placeholder="Observations, conditions particulières…">{{ old('notes', $garage?->notes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:.75rem;justify-content:flex-end;padding-bottom:1rem;">
        <a href="{{ $garage ? route('garages.show', $garage) : route('garages.index') }}" class="btn btn-ghost">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.8"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="1.8"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="1.8"/></svg>
            {{ $garage ? 'Enregistrer les modifications' : 'Ajouter le garage' }}
        </button>
    </div>
</form>

<script>
// Gestion des pills de spécialisation
function toggleSpec(val, el) {
    el.classList.toggle('active');
    const isActive = el.classList.contains('active');
    const container = document.getElementById('spec-inputs');
    const existing = container.querySelector('[data-val="' + val + '"]');
    if (isActive && !existing) {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'specializations[]';
        input.value = val;
        input.className  = 'spec-input';
        input.dataset.val = val;
        container.appendChild(input);
    } else if (!isActive && existing) {
        existing.remove();
    }
}
</script>
