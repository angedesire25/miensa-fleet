@extends('layouts.dashboard')

@section('title', 'Alertes')

@section('content')
<div style="padding:1.5rem;">

    {{-- En-tête --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h1 style="font-size:1.35rem;font-weight:700;color:#f1f5f9;margin:0;">Alertes système</h1>
            <p style="color:#94a3b8;font-size:.85rem;margin:.2rem 0 0;">Surveillance et suivi des alertes de la flotte</p>
        </div>
        @can('alerts.manage')
        <form method="POST" action="{{ route('alerts.bulk-process') }}" id="bulk-form">
            @csrf
            <div id="bulk-ids-container"></div>
            <button type="submit" id="bulk-btn"
                    style="display:none;padding:.5rem 1rem;background:#10b981;color:#fff;border:none;border-radius:.45rem;font-size:.85rem;font-weight:600;cursor:pointer;">
                Traiter la sélection (<span id="bulk-count">0</span>)
            </button>
        </form>
        @endcan
    </div>

    {{-- Statistiques --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Actives</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f1f5f9;">{{ $stats['total'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(239,68,68,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Critiques</div>
            <div style="font-size:1.6rem;font-weight:700;color:#ef4444;">{{ $stats['critiques'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(245,158,11,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Avertissements</div>
            <div style="font-size:1.6rem;font-weight:700;color:#f59e0b;">{{ $stats['warnings'] }}</div>
        </div>
        <div style="background:#1e293b;border:1px solid rgba(16,185,129,.3);border-radius:.65rem;padding:1rem 1.25rem;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:.35rem;">Traitées (total)</div>
            <div style="font-size:1.6rem;font-weight:700;color:#10b981;">{{ $stats['traitees'] }}</div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('alerts.index') }}"
          style="display:flex;flex-wrap:wrap;gap:.65rem;margin-bottom:1.25rem;">
        <select name="status" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Actives (défaut)</option>
            <option value="all"     {{ request('status')==='all'       ? 'selected':'' }}>Toutes</option>
            <option value="new"     {{ request('status')==='new'       ? 'selected':'' }}>Nouvelles</option>
            <option value="seen"    {{ request('status')==='seen'      ? 'selected':'' }}>Vues</option>
            <option value="processed" {{ request('status')==='processed' ? 'selected':'' }}>Traitées</option>
        </select>
        <select name="severity" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Sévérité</option>
            <option value="all"      {{ request('severity')==='all'      ? 'selected':'' }}>Toutes</option>
            <option value="critical" {{ request('severity')==='critical' ? 'selected':'' }}>Critique</option>
            <option value="warning"  {{ request('severity')==='warning'  ? 'selected':'' }}>Avertissement</option>
            <option value="info"     {{ request('severity')==='info'     ? 'selected':'' }}>Info</option>
        </select>
        <select name="type" style="background:#1e293b;color:#e2e8f0;border:1px solid #475569;border-radius:.4rem;padding:.4rem .75rem;font-size:.85rem;">
            <option value="">Type</option>
            <option value="all" {{ request('type')==='all' ? 'selected':'' }}>Tous</option>
            @php
            $alertTypes = [
                'document_expiring'   => 'Doc. expirant',
                'maintenance_due'     => 'Maintenance',
                'vehicle_idle'        => 'Véhicule immobilisé',
                'infraction_unpaid'   => 'Amende impayée',
                'assignment_conflict' => 'Conflit affectation',
                'inspection_overdue'  => 'Inspection en retard',
                'fuel_anomaly'        => 'Anomalie carburant',
            ];
        @endphp
        @foreach($alertTypes as $t => $label)
            <option value="{{ $t }}" {{ request('type')===$t ? 'selected':'' }}>{{ $label }}</option>
        @endforeach
        </select>
        <button type="submit" style="padding:.4rem .9rem;background:#3b82f6;color:#fff;border:none;border-radius:.4rem;font-size:.85rem;cursor:pointer;">
            Filtrer
        </button>
        @if(request()->hasAny(['status','severity','type']))
        <a href="{{ route('alerts.index') }}"
           style="padding:.4rem .9rem;background:#334155;color:#94a3b8;border-radius:.4rem;font-size:.85rem;text-decoration:none;">
            Réinitialiser
        </a>
        @endif
    </form>

    {{-- Liste --}}
    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;overflow:hidden;">
        @forelse($alerts as $alert)
        @php
            $severityColor = match($alert->severity) {
                'critical' => '#ef4444',
                'warning'  => '#f59e0b',
                default    => '#3b82f6',
            };
            $borderLeft = "border-left:3px solid {$severityColor};";
        @endphp
        <div style="display:flex;align-items:center;gap:1rem;padding:.9rem 1.25rem;border-bottom:1px solid #334155;{{ $borderLeft }}{{ $loop->last ? 'border-bottom:none;' : '' }}"
             class="alert-row">

            {{-- Checkbox --}}
            @can('alerts.manage')
            @if($alert->status !== 'processed')
            <input type="checkbox" class="alert-checkbox" value="{{ $alert->id }}"
                   style="width:16px;height:16px;accent-color:#10b981;flex-shrink:0;">
            @else
            <div style="width:16px;flex-shrink:0;"></div>
            @endif
            @endcan

            {{-- Badge sévérité --}}
            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:{{ $severityColor }}20;flex-shrink:0;">
                @if($alert->severity === 'critical')
                <svg style="width:16px;height:16px;color:{{ $severityColor }};stroke:{{ $severityColor }};" fill="none" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.8"/></svg>
                @elseif($alert->severity === 'warning')
                <svg style="width:16px;height:16px;stroke:{{ $severityColor }};" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.8"/></svg>
                @else
                <svg style="width:16px;height:16px;stroke:{{ $severityColor }};" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 16v-4m0-4h.01" stroke="currentColor" stroke-width="1.8"/></svg>
                @endif
            </span>

            {{-- Contenu principal --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                    <a href="{{ route('alerts.show', $alert) }}"
                       style="font-size:.9rem;font-weight:600;color:#f1f5f9;text-decoration:none;">
                        {{ $alert->title }}
                    </a>
                    @if($alert->status === 'new')
                    <span style="padding:.1rem .45rem;background:#3b82f620;color:#3b82f6;border-radius:3px;font-size:.7rem;font-weight:700;">NEW</span>
                    @endif
                    <span style="padding:.1rem .45rem;background:#0f172a;color:#94a3b8;border-radius:3px;font-size:.7rem;">{{ $alert->type }}</span>
                </div>
                <div style="font-size:.8rem;color:#94a3b8;margin-top:.2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $alert->message }}
                </div>
                <div style="font-size:.75rem;color:#64748b;margin-top:.25rem;display:flex;gap:1rem;flex-wrap:wrap;">
                    @if($alert->vehicle)
                    <span>{{ $alert->vehicle->brand }} {{ $alert->vehicle->model }} — {{ $alert->vehicle->plate }}</span>
                    @endif
                    @if($alert->driver)
                    <span>{{ $alert->driver->full_name }}</span>
                    @endif
                    <span>{{ $alert->created_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                @if($alert->status === 'processed')
                <span style="padding:.25rem .6rem;background:#10b98120;color:#10b981;border-radius:.35rem;font-size:.75rem;">Traité</span>
                @else
                @can('alerts.manage')
                <form method="POST" action="{{ route('alerts.process', $alert) }}" style="display:inline;">
                    @csrf
                    <button type="submit"
                            style="padding:.3rem .7rem;background:#10b98120;color:#10b981;border:1px solid #10b98140;border-radius:.35rem;font-size:.75rem;cursor:pointer;">
                        Traiter
                    </button>
                </form>
                @endcan
                @endif
                <a href="{{ route('alerts.show', $alert) }}"
                   style="padding:.3rem .7rem;background:#334155;color:#94a3b8;border-radius:.35rem;font-size:.75rem;text-decoration:none;">
                    Voir
                </a>
            </div>
        </div>
        @empty
        <div style="padding:3rem;text-align:center;color:#64748b;">
            <svg style="width:48px;height:48px;margin:0 auto 1rem;opacity:.4;display:block;" fill="none" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <p style="margin:0;font-size:.9rem;">Aucune alerte</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($alerts->hasPages())
    <div style="margin-top:1rem;">{{ $alerts->links() }}</div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.alert-checkbox');
    const bulkBtn    = document.getElementById('bulk-btn');
    const bulkCount  = document.getElementById('bulk-count');
    const container  = document.getElementById('bulk-ids-container');

    if (!bulkBtn) return;

    function syncBulk() {
        const checked = [...checkboxes].filter(c => c.checked);
        bulkBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
        bulkCount.textContent = checked.length;
        container.innerHTML = checked.map(c =>
            `<input type="hidden" name="alert_ids[]" value="${c.value}">`
        ).join('');
    }

    checkboxes.forEach(cb => cb.addEventListener('change', syncBulk));
});
</script>
@endpush

@endsection
