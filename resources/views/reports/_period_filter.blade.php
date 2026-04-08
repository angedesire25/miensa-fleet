{{-- Composant réutilisable : sélecteur de période + sélecteurs véhicule/chauffeur optionnels --}}
<form method="GET" action="{{ $action }}"
      style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">

    {{-- Champs cachés --}}
    @foreach($hidden ?? [] as $hname => $hval)
    <input type="hidden" name="{{ $hname }}" value="{{ $hval }}">
    @endforeach

    {{-- Sélecteur véhicule --}}
    @if(isset($vehicles))
    <div style="flex:1;min-width:180px;">
        <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:.3rem;">Véhicule</label>
        <select name="vehicle_id" required
                style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.45rem .75rem;font-size:.85rem;">
            <option value="">— Sélectionner —</option>
            @foreach($vehicles as $v)
            <option value="{{ $v->id }}" {{ (request('vehicle_id')==$v->id || (isset($selectedVehicleId) && $selectedVehicleId==$v->id)) ? 'selected':'' }}>
                {{ $v->brand }} {{ $v->model }} — {{ $v->plate }}
            </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Sélecteur chauffeur --}}
    @if(isset($drivers) && !isset($vehicles))
    <div style="flex:1;min-width:180px;">
        <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:.3rem;">Chauffeur</label>
        <select name="driver_id" required
                style="width:100%;background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.45rem .75rem;font-size:.85rem;">
            <option value="">— Sélectionner —</option>
            @foreach($drivers as $d)
            <option value="{{ $d->id }}" {{ (request('driver_id')==$d->id || (isset($selectedDriverId) && $selectedDriverId==$d->id)) ? 'selected':'' }}>
                {{ $d->full_name }}{{ $d->matricule ? ' ('.$d->matricule.')' : '' }}
            </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Du --}}
    <div>
        <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:.3rem;">Du</label>
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
               style="background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.45rem .75rem;font-size:.85rem;">
    </div>

    {{-- Au --}}
    <div>
        <label style="display:block;font-size:.75rem;color:#64748b;margin-bottom:.3rem;">Au</label>
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
               style="background:#0f172a;border:1px solid #475569;border-radius:.4rem;color:#f1f5f9;padding:.45rem .75rem;font-size:.85rem;">
    </div>

    <button type="submit"
            style="padding:.45rem 1.1rem;background:#3b82f6;color:#fff;border:none;border-radius:.4rem;font-size:.85rem;font-weight:600;cursor:pointer;">
        Générer
    </button>
</form>
