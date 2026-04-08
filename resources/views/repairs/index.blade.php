@extends('layouts.dashboard')

@section('title', 'Réparations')
@section('page-title', 'Réparations en garage')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.25rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.stat-card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;padding:1.1rem 1.25rem;display:flex;align-items:center;gap:1rem;}
.stat-icon{width:42px;height:42px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-val{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;}
.stat-lbl{font-size:.75rem;color:#64748b;margin-top:.2rem;}
.badge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .55rem;border-radius:99px;font-size:.7rem;font-weight:600;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-ghost{background:#f8fafc;color:#374151;border:1.5px solid #e2e8f0;}
.btn-ghost:hover{background:#f1f5f9;}
.filters-bar{display:flex;gap:.65rem;flex-wrap:wrap;align-items:flex-end;}
.filter-input{padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.825rem;background:#fff;color:#0f172a;outline:none;}
.filter-input:focus{border-color:#10b981;}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{font-size:.72rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;padding:.6rem 1rem;border-bottom:1.5px solid #f1f5f9;text-align:left;white-space:nowrap;}
td{padding:.7rem 1rem;border-bottom:1px solid #f8fafc;font-size:.855rem;color:#374151;vertical-align:middle;}
tr:hover td{background:#f8fafc;}
.overdue-row td{background:#fef2f2;}
.pagination-wrap{display:flex;justify-content:space-between;align-items:center;padding:.75rem 0;font-size:.82rem;color:#64748b;}
</style>

{{-- ── Statistiques ─────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.77 3.77z" stroke="#3b82f6" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val">{{ $stats['total'] }}</div><div class="stat-lbl">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="#f59e0b" stroke-width="1.8"/><path d="M12 8v4M12 16h.01" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#f59e0b;">{{ $stats['en_cours'] }}</div><div class="stat-lbl">En cours</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#10b981" stroke-width="1.8"/></svg>
        </div>
        <div><div class="stat-val" style="color:#10b981;">{{ $stats['terminees'] }}</div><div class="stat-lbl">Terminées</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#ef4444" stroke-width="1.8"/><path d="M12 9v4M12 17h.01" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div><div class="stat-val" style="color:#ef4444;">{{ $stats['recurrences'] }}</div><div class="stat-lbl">Récurrences</div></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Liste des réparations</span>
        @can('repairs.create')
        <a href="{{ route('repairs.create') }}" class="btn btn-primary">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            Nouveau bon de réparation
        </a>
        @endcan
    </div>

    {{-- Filtres --}}
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;">
        <form method="GET" class="filters-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Véhicule, garage, diagnostic…" class="filter-input" style="min-width:200px;">
            <select name="status" class="filter-input">
                <option value="all">Tous les statuts</option>
                <option value="sent"               @selected(request('status')==='sent')>Envoyé</option>
                <option value="diagnosing"         @selected(request('status')==='diagnosing')>Diagnostic</option>
                <option value="repairing"          @selected(request('status')==='repairing')>En réparation</option>
                <option value="waiting_parts"      @selected(request('status')==='waiting_parts')>Attente pièces</option>
                <option value="completed"          @selected(request('status')==='completed')>Terminé</option>
                <option value="returned"           @selected(request('status')==='returned')>Retourné</option>
                <option value="returned_with_issue"@selected(request('status')==='returned_with_issue')>Retour avec problème</option>
            </select>
            <select name="garage_id" class="filter-input">
                <option value="">Tous les garages</option>
                @foreach($garages as $garage)
                    <option value="{{ $garage->id }}" @selected(request('garage_id') == $garage->id)>{{ $garage->name }}</option>
                @endforeach
            </select>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;color:#374151;cursor:pointer;">
                <input type="checkbox" name="overdue" value="1" @checked(request('overdue')==='1')> En retard (+7j)
            </label>
            <button type="submit" class="btn btn-ghost">Filtrer</button>
            @if(request()->anyFilled(['q','status','garage_id','overdue']))
                <a href="{{ route('repairs.index') }}" class="btn btn-ghost" style="color:#ef4444;">Effacer</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Véhicule</th>
                    <th>Garage</th>
                    <th>Type</th>
                    <th>Envoyé le</th>
                    <th>Durée</th>
                    <th>Statut</th>
                    <th>Montant</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($repairs as $repair)
                @php
                    $rStatusColors = [
                        'sent'               => ['#eff6ff','#1e40af'],
                        'diagnosing'         => ['#fef3c7','#92400e'],
                        'repairing'          => ['#fff7ed','#9a3412'],
                        'waiting_parts'      => ['#ede9fe','#5b21b6'],
                        'completed'          => ['#f0fdf4','#166534'],
                        'returned'           => ['#f0fdf4','#166534'],
                        'returned_with_issue'=> ['#fee2e2','#991b1b'],
                    ];
                    $rStatusLabels = [
                        'sent'               => 'Envoyé',
                        'diagnosing'         => 'Diagnostic',
                        'repairing'          => 'En réparation',
                        'waiting_parts'      => 'Attente pièces',
                        'completed'          => 'Terminé',
                        'returned'           => 'Retourné',
                        'returned_with_issue'=> 'Retour avec problème',
                    ];
                    [$rBg,$rFg] = $rStatusColors[$repair->status] ?? ['#f8fafc','#64748b'];
                @endphp
                <tr class="{{ $repair->is_overdue ? 'overdue-row' : '' }}">
                    <td style="font-weight:600;color:#64748b;">#{{ $repair->id }}</td>
                    <td>
                        @if($repair->vehicle)
                            <a href="{{ route('vehicles.show', $repair->vehicle) }}" style="font-weight:600;color:#0f172a;text-decoration:none;">
                                {{ $repair->vehicle->plate }}
                            </a>
                            <div style="font-size:.75rem;color:#64748b;">{{ $repair->vehicle->brand }} {{ $repair->vehicle->model }}</div>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;">{{ $repair->garage?->name ?? '—' }}</td>
                    <td style="font-size:.8rem;">{{ ucfirst(str_replace('_',' ',$repair->repair_type)) }}</td>
                    <td style="font-size:.8rem;white-space:nowrap;">{{ $repair->datetime_sent?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-size:.8rem;">
                        {{ $repair->duration_days !== null ? $repair->duration_days . ' j' : '—' }}
                        @if($repair->is_overdue)
                            <span class="badge" style="background:#fee2e2;color:#991b1b;margin-left:.25rem;">En retard</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $rBg }};color:{{ $rFg }};">{{ $rStatusLabels[$repair->status] ?? $repair->status }}</span>
                        @if($repair->same_issue_recurrence)
                            <span class="badge" style="background:#fee2e2;color:#991b1b;">Récurrence</span>
                        @endif
                    </td>
                    <td style="font-size:.8rem;">
                        {{ $repair->invoice_amount ? number_format($repair->invoice_amount, 0, ',', ' ') . ' FCFA' : '—' }}
                    </td>
                    <td>
                        <a href="{{ route('repairs.show', $repair) }}" class="btn btn-ghost" style="padding:.3rem .65rem;font-size:.78rem;">Voir</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:2.5rem;color:#94a3b8;">Aucune réparation trouvée.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($repairs->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f1f5f9;" class="pagination-wrap">
        <span>{{ $repairs->firstItem() }}–{{ $repairs->lastItem() }} sur {{ $repairs->total() }}</span>
        {{ $repairs->links() }}
    </div>
    @endif
</div>
@endsection
