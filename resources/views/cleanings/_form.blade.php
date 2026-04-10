@php $editing = isset($cleaning); @endphp
<style>
.form-section{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;margin-bottom:1.25rem;}
.form-section-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.55rem;}
.form-section-title{font-size:.875rem;font-weight:700;color:#0f172a;}
.form-body{padding:1.25rem;}
.form-grid{display:grid;gap:1rem;}
.form-grid-2{grid-template-columns:repeat(2,1fr);}
.form-group{display:flex;flex-direction:column;gap:.4rem;}
.form-label{font-size:.8rem;font-weight:600;color:#374151;}
.form-label span{color:#ef4444;}
.form-control{padding:.55rem .8rem;border:1.5px solid #e2e8f0;border-radius:.45rem;font-size:.875rem;color:#0f172a;background:#fff;outline:none;transition:border-color .2s;}
.form-control:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.1);}
.form-hint{font-size:.73rem;color:#94a3b8;}
.form-error{font-size:.75rem;color:#ef4444;}
.assignee-tabs{display:flex;border:1.5px solid #e2e8f0;border-radius:.5rem;overflow:hidden;margin-bottom:.75rem;}
.tab-btn{flex:1;padding:.5rem .75rem;font-size:.8rem;font-weight:600;border:none;cursor:pointer;transition:background .15s,color .15s;background:#f8fafc;color:#64748b;}
.tab-btn.active-driver{background:#f0fdf4;color:#16a34a;}
.tab-btn.active-collab{background:#f5f3ff;color:#7c3aed;}
.assignee-list{max-height:220px;overflow-y:auto;border:1.5px solid #e2e8f0;border-radius:.45rem;background:#fff;}
.assignee-item{display:flex;align-items:center;gap:.65rem;padding:.55rem .85rem;cursor:pointer;transition:background .12s;border-bottom:1px solid #f8fafc;}
.assignee-item:last-child{border-bottom:none;}
.assignee-item:hover{background:#f8fafc;}
.assignee-item.selected{background:#f0fdf4;border-left:3px solid #10b981;}
.assignee-item.selected.collab{background:#f5f3ff;border-left-color:#7c3aed;}
.assignee-avatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0;}
.assignee-search{width:100%;padding:.45rem .75rem;border:1.5px solid #e2e8f0;border-top:none;font-size:.82rem;outline:none;color:#0f172a;}
.assignee-search:focus{border-color:#10b981;}
.day-info{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.45rem;padding:.55rem .85rem;font-size:.8rem;color:#166534;display:flex;align-items:center;gap:.45rem;margin-top:.4rem;}
.sat-warning{background:#fff7ed;border:1px solid #fed7aa;border-radius:.45rem;padding:.55rem .85rem;font-size:.8rem;color:#c2410c;display:flex;align-items:center;gap:.45rem;margin-top:.4rem;}
.btn{padding:.45rem .9rem;border-radius:.45rem;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-primary:hover{opacity:.88;cursor:pointer;}
</style>

<form method="POST"
      action="{{ $editing ? route('cleanings.update', $cleaning) : route('cleanings.store') }}"
      id="cleaning-form">
    @csrf
    @if($editing) @method('PUT') @endif

    {{-- ── Véhicule + date + heure ──────────────────────────────────── --}}
    <div class="form-section">
        <div class="form-section-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M3 17h2l1-3h12l1 3h2" stroke="#10b981" stroke-width="2" stroke-linecap="round"/><circle cx="7.5" cy="18.5" r="1.5" stroke="#10b981" stroke-width="1.5"/><circle cx="16.5" cy="18.5" r="1.5" stroke="#10b981" stroke-width="1.5"/></svg>
            <span class="form-section-title">Véhicule & Planification</span>
        </div>
        <div class="form-body">
            <div class="form-grid form-grid-2">

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Véhicule <span>*</span></label>
                    <select name="vehicle_id" class="form-control" required>
                        <option value="">-- Sélectionner un véhicule --</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id', $editing ? $cleaning->vehicle_id : '') == $v->id)>
                            {{ $v->plate }} — {{ $v->brand }} {{ $v->model }}
                        </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Date du nettoyage <span>*</span></label>
                    <input type="date" name="scheduled_date" id="scheduled-date"
                           value="{{ old('scheduled_date', $editing ? $cleaning->scheduled_date->format('Y-m-d') : $nextSaturday) }}"
                           class="form-control" required onchange="checkSaturday(this.value)">
                    <div id="day-feedback"></div>
                    <div class="form-hint">Planifiés les samedis de préférence.</div>
                    @error('scheduled_date')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Heure prévue <span>*</span></label>
                    <input type="time" name="scheduled_time"
                           value="{{ old('scheduled_time', $editing ? $cleaning->scheduled_time : '08:00') }}"
                           class="form-control" required>
                    @error('scheduled_time')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Type de nettoyage <span>*</span></label>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;">
                        @foreach(['exterior'=>['Extérieur','Carrosserie, jantes, vitres','#3b82f6','#eff6ff'],'interior'=>['Intérieur','Sièges, tableau de bord, sols','#8b5cf6','#faf5ff'],'full'=>['Complet','Intérieur + Extérieur','#10b981','#f0fdf4']] as $val=>[$lbl,$desc,$clr,$bg])
                        @php $sel = old('cleaning_type', $editing ? $cleaning->cleaning_type : 'full') === $val; @endphp
                        <label style="cursor:pointer;">
                            <input type="radio" name="cleaning_type" value="{{ $val }}" {{ $sel ? 'checked' : '' }} style="display:none;" class="type-radio">
                            <div class="type-card" style="border:2px solid {{ $sel ? $clr : '#e2e8f0' }};border-radius:.55rem;padding:.75rem;background:{{ $sel ? $bg : '#fff' }};transition:all .15s;">
                                <div style="font-weight:700;color:{{ $sel ? $clr : '#374151' }};font-size:.85rem;margin-bottom:.2rem;">{{ $lbl }}</div>
                                <div style="font-size:.73rem;color:#94a3b8;">{{ $desc }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('cleaning_type')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ── Responsable ──────────────────────────────────────────────── --}}
    <div class="form-section">
        <div class="form-section-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="#10b981" stroke-width="2"/><circle cx="12" cy="7" r="4" stroke="#10b981" stroke-width="2"/></svg>
            <span class="form-section-title">Responsable du nettoyage</span>
        </div>
        <div class="form-body">

            <input type="hidden" name="driver_id" id="field-driver-id" value="{{ old('driver_id', $editing ? $cleaning->driver_id : '') }}">
            <input type="hidden" name="user_id"   id="field-user-id"   value="{{ old('user_id', $editing ? $cleaning->user_id : '') }}">

            <div class="assignee-tabs">
                <button type="button" class="tab-btn" id="tab-driver" onclick="switchTab('driver')">
                    🚗 Chauffeurs professionnels
                </button>
                <button type="button" class="tab-btn" id="tab-collab" onclick="switchTab('collab')">
                    👤 Collaborateurs
                </button>
            </div>

            {{-- Panel chauffeurs --}}
            <div id="panel-driver">
                <input type="text" class="assignee-search" placeholder="Rechercher un chauffeur…" oninput="filterList('driver', this.value)">
                <div class="assignee-list" id="list-driver">
                    @forelse($drivers as $d)
                    <div class="assignee-item {{ old('driver_id', $editing ? $cleaning->driver_id : '') == $d->id ? 'selected' : '' }}"
                         id="driver-{{ $d->id }}"
                         onclick="selectAssignee('driver', {{ $d->id }}, '{{ addslashes($d->full_name) }}')">
                        <div class="assignee-avatar" style="background:#f0fdf4;color:#16a34a;">
                            {{ strtoupper(substr($d->full_name, 0, 1)) }}
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:600;font-size:.85rem;color:#0f172a;">{{ $d->full_name }}</div>
                            <div style="font-size:.72rem;color:#94a3b8;">{{ $d->matricule }}</div>
                        </div>
                        <svg id="check-driver-{{ $d->id }}" width="16" height="16" fill="none" viewBox="0 0 24 24" style="{{ old('driver_id', $editing ? $cleaning->driver_id : '') == $d->id ? '' : 'display:none' }}"><path d="M9 12l2 2 4-4" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#16a34a" stroke-width="2"/></svg>
                    </div>
                    @empty
                    <div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:.82rem;">Aucun chauffeur disponible.</div>
                    @endforelse
                </div>
            </div>

            {{-- Panel collaborateurs --}}
            <div id="panel-collab" style="display:none;">
                <input type="text" class="assignee-search" placeholder="Rechercher un collaborateur…" oninput="filterList('collab', this.value)">
                <div class="assignee-list" id="list-collab">
                    @forelse($collaborators as $u)
                    <div class="assignee-item collab {{ old('user_id', $editing ? $cleaning->user_id : '') == $u->id ? 'selected' : '' }}"
                         id="collab-{{ $u->id }}"
                         onclick="selectAssignee('collab', {{ $u->id }}, '{{ addslashes($u->name) }}')">
                        <div class="assignee-avatar" style="background:#f5f3ff;color:#7c3aed;">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:600;font-size:.85rem;color:#0f172a;">{{ $u->name }}</div>
                            <div style="font-size:.72rem;color:#94a3b8;">{{ $u->email }}</div>
                        </div>
                        <svg id="check-collab-{{ $u->id }}" width="16" height="16" fill="none" viewBox="0 0 24 24" style="{{ old('user_id', $editing ? $cleaning->user_id : '') == $u->id ? '' : 'display:none' }}"><path d="M9 12l2 2 4-4" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#7c3aed" stroke-width="2"/></svg>
                    </div>
                    @empty
                    <div style="padding:1.5rem;text-align:center;color:#94a3b8;font-size:.82rem;">Aucun collaborateur disponible.</div>
                    @endforelse
                </div>
            </div>

            @error('driver_id')<div class="form-error" style="margin-top:.5rem;">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Instructions ─────────────────────────────────────────────── --}}
    <div class="form-section">
        <div class="form-section-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 12h6M9 8h6M9 16h4M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="form-section-title">Instructions / Notes</span>
        </div>
        <div class="form-body">
            <div class="form-group">
                <textarea name="notes" rows="3" class="form-control" placeholder="Instructions particulières pour ce nettoyage…">{{ old('notes', $editing ? $cleaning->notes : '') }}</textarea>
                <div class="form-hint">Optionnel. Ces instructions seront visibles par le responsable dans la notification.</div>
                @error('notes')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Actions ──────────────────────────────────────────────────── --}}
    <div style="display:flex;justify-content:flex-end;gap:.75rem;">
        <a href="{{ route('cleanings.index') }}" style="padding:.6rem 1.1rem;border-radius:.45rem;border:1.5px solid #e2e8f0;background:#fff;color:#374151;font-size:.875rem;font-weight:600;text-decoration:none;">
            Annuler
        </a>
        <button type="submit" class="btn btn-primary" style="padding:.6rem 1.4rem;font-size:.875rem;">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2"/></svg>
            {{ $editing ? 'Enregistrer les modifications' : 'Planifier le nettoyage' }}
        </button>
    </div>
</form>

<script>
// ── Tabs responsable ──────────────────────────────────────────────────────
let currentTab = '{{ old("user_id", $editing && $cleaning->user_id ? "collab" : "driver") }}' === 'collab' ? 'collab' : 'driver';

function switchTab(tab) {
    currentTab = tab;
    document.getElementById('panel-driver').style.display = tab === 'driver' ? 'block' : 'none';
    document.getElementById('panel-collab').style.display = tab === 'collab' ? 'block' : 'none';
    document.getElementById('tab-driver').className = 'tab-btn ' + (tab === 'driver' ? 'active-driver' : '');
    document.getElementById('tab-collab').className = 'tab-btn ' + (tab === 'collab' ? 'active-collab' : '');
}

function selectAssignee(type, id, name) {
    // Désélectionner tout
    document.querySelectorAll('.assignee-item').forEach(el => {
        el.classList.remove('selected');
        const svg = el.querySelector('svg');
        if (svg) svg.style.display = 'none';
    });

    if (type === 'driver') {
        document.getElementById('field-driver-id').value = id;
        document.getElementById('field-user-id').value = '';
    } else {
        document.getElementById('field-user-id').value = id;
        document.getElementById('field-driver-id').value = '';
    }

    const item = document.getElementById(type + '-' + id);
    if (item) item.classList.add('selected');
    const chk = document.getElementById('check-' + type + '-' + id);
    if (chk) chk.style.display = 'inline';
}

function filterList(type, query) {
    const list = document.getElementById('list-' + type);
    list.querySelectorAll('.assignee-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query.toLowerCase()) ? 'flex' : 'none';
    });
}

// ── Vérif samedi ──────────────────────────────────────────────────────────
function checkSaturday(val) {
    const fb = document.getElementById('day-feedback');
    if (!val) { fb.innerHTML = ''; return; }
    const d = new Date(val + 'T00:00:00');
    const day = d.getDay(); // 0=dim, 6=sam
    if (day === 6) {
        fb.innerHTML = '<div class="day-info"><svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4" stroke="#166534" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#166534" stroke-width="2"/></svg>Samedi — jour idéal pour le nettoyage ✓</div>';
    } else {
        const days = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        fb.innerHTML = '<div class="sat-warning"><svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01" stroke="#c2410c" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="#c2410c" stroke-width="1.8"/></svg>' + days[day] + ' — nettoyages planifiés de préférence le samedi</div>';
    }
}

// ── Type cards interactifs ────────────────────────────────────────────────
document.querySelectorAll('.type-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        const colors = {
            exterior: ['#3b82f6','#eff6ff'],
            interior: ['#8b5cf6','#faf5ff'],
            full:     ['#10b981','#f0fdf4'],
        };
        document.querySelectorAll('.type-radio').forEach(r => {
            const card = r.nextElementSibling;
            const c = colors[r.value] || ['#e2e8f0','#fff'];
            card.style.border = '2px solid ' + (r.checked ? c[0] : '#e2e8f0');
            card.style.background = r.checked ? c[1] : '#fff';
            card.querySelector('div').style.color = r.checked ? c[0] : '#374151';
        });
    });
});

// Init
switchTab(currentTab);
checkSaturday(document.getElementById('scheduled-date').value);
</script>
