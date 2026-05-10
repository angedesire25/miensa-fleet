{{-- Formulaire partagé création / modification station --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;">

    <div style="grid-column:1/-1;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Nom <span style="color:#ef4444;">*</span></label>
        <input type="text" name="name" value="{{ old('name', $station?->name) }}" required maxlength="100"
               style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
               placeholder="Ex: Station du Centre">
    </div>

    <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Marque / Enseigne</label>
        <input type="text" name="brand" value="{{ old('brand', $station?->brand) }}" maxlength="60"
               style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
               placeholder="Ex: Total, Shell, Oryx…">
    </div>

    <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Ville</label>
        <input type="text" name="city" value="{{ old('city', $station?->city) }}" maxlength="80"
               style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
               placeholder="Ex: Abidjan">
    </div>

    <div style="grid-column:1/-1;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Adresse</label>
        <input type="text" name="address" value="{{ old('address', $station?->address) }}" maxlength="255"
               style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
               placeholder="Ex: Avenue de la République, km 4">
    </div>

    <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Téléphone</label>
        <input type="text" name="phone" value="{{ old('phone', $station?->phone) }}" maxlength="30"
               style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
               placeholder="+225 07 00 00 00 00">
    </div>

    <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Personne de contact</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $station?->contact_person) }}" maxlength="100"
               style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;box-sizing:border-box;"
               placeholder="Nom du gérant">
    </div>

    {{-- Types de carburant --}}
    <div style="grid-column:1/-1;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.5rem;">Types de carburant disponibles <span style="color:#ef4444;">*</span></label>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
            @foreach($fuelTypes as $val => $label)
            @php
                $checked = old('fuel_types')
                    ? in_array($val, old('fuel_types', []))
                    : in_array($val, $station?->fuel_types ?? ['diesel']);
            @endphp
            <label style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .8rem;border:1px solid {{ $checked ? '#10b981' : '#d1d5db' }};border-radius:.35rem;cursor:pointer;font-size:.85rem;color:{{ $checked ? '#059669' : '#374151' }};background:{{ $checked ? 'rgba(16,185,129,.06)' : '#fff' }};"
                   id="label-{{ $val }}">
                <input type="checkbox" name="fuel_types[]" value="{{ $val }}" {{ $checked ? 'checked' : '' }}
                       onchange="toggleLabel(this, 'label-{{ $val }}')"
                       style="accent-color:#10b981;">
                {{ $label }}
            </label>
            @endforeach
        </div>
    </div>

    {{-- Statut --}}
    <div style="grid-column:1/-1;">
        <label style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $station?->is_active ?? true) ? 'checked' : '' }}
                   style="width:16px;height:16px;accent-color:#10b981;">
            <span style="font-size:.875rem;font-weight:500;color:#374151;">Station active (visible dans les formulaires)</span>
        </label>
    </div>

    {{-- Notes --}}
    <div style="grid-column:1/-1;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Notes</label>
        <textarea name="notes" rows="2" maxlength="500"
                  style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:.45rem;font-size:.875rem;resize:vertical;box-sizing:border-box;"
                  placeholder="Informations complémentaires…">{{ old('notes', $station?->notes) }}</textarea>
    </div>

</div>

<script>
function toggleLabel(cb, labelId) {
    const lbl = document.getElementById(labelId);
    if (cb.checked) {
        lbl.style.borderColor = '#10b981';
        lbl.style.color = '#059669';
        lbl.style.background = 'rgba(16,185,129,.06)';
    } else {
        lbl.style.borderColor = '#d1d5db';
        lbl.style.color = '#374151';
        lbl.style.background = '#fff';
    }
}
</script>
