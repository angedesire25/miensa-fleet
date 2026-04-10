@extends('layouts.dashboard')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres de l\'application')

@section('content')
<style>
.card{background:#fff;border-radius:.75rem;border:1px solid #e2e8f0;overflow:hidden;margin-bottom:1.5rem;}
.card-head{padding:.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;}
.card-title{font-size:.9rem;font-weight:700;color:#0f172a;}
.card-body{padding:1.25rem;}
.form-group{margin-bottom:1.25rem;}
.form-label{display:block;font-size:.82rem;color:#64748b;font-weight:600;margin-bottom:.45rem;}
.form-hint{font-size:.73rem;color:#94a3b8;margin-top:.25rem;}
.btn{padding:.5rem 1rem;border-radius:.45rem;font-size:.83rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;transition:opacity .15s;}
.btn-primary{background:linear-gradient(135deg,#10b981,#059669);color:#fff;}
.btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
.btn-danger:hover{background:#fee2e2;}
.preview-img{width:100%;height:160px;object-fit:cover;border-radius:.5rem;border:1px solid #e2e8f0;display:block;}
.preview-placeholder{width:100%;height:160px;background:#f8fafc;border:2px dashed #e2e8f0;border-radius:.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;color:#94a3b8;font-size:.8rem;}
.upload-area{border:2px dashed #cbd5e1;border-radius:.5rem;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;}
.upload-area:hover{border-color:#10b981;background:#f0fdf4;}
.upload-area input[type=file]{display:none;}
.badge-new{display:inline-block;background:#dcfce7;color:#166534;font-size:.65rem;font-weight:700;padding:.1rem .45rem;border-radius:99px;margin-left:.4rem;}
</style>

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- ── Logo (favicon + onglet navigateur) ─────────────────────────── --}}
        <div class="card">
            <div class="card-head">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg>
                <span class="card-title">Logo de l'application</span>
                <span class="badge-new">Favicon + onglet</span>
            </div>
            <div class="card-body">
                <p style="font-size:.82rem;color:#64748b;margin-bottom:1rem;">
                    Ce logo apparaît dans l'onglet du navigateur (favicon) et dans la barre latérale de l'application.
                    Format recommandé : PNG ou SVG carré, 64×64 px minimum.
                </p>

                {{-- Aperçu actuel --}}
                <div style="margin-bottom:1rem;">
                    @if($settings['logo'])
                        <div style="display:flex;align-items:center;gap:1rem;padding:.85rem;background:#f8fafc;border-radius:.5rem;border:1px solid #e2e8f0;margin-bottom:.75rem;">
                            <img src="{{ Storage::url($settings['logo']) }}" style="width:48px;height:48px;object-fit:contain;border-radius:.4rem;background:#fff;padding:4px;border:1px solid #e2e8f0;" alt="Logo actuel">
                            <div style="flex:1;">
                                <div style="font-size:.82rem;font-weight:600;color:#0f172a;">Logo actuel</div>
                                <div style="font-size:.72rem;color:#94a3b8;">Cliquez sur "Nouveau logo" pour le remplacer</div>
                            </div>
                            <input type="checkbox" id="delete-logo-cb" name="delete_logo" value="1" style="display:none;">
                            <button type="button" class="btn btn-danger" style="font-size:.76rem;padding:.35rem .7rem;"
                                    onclick="confirmDeleteSetting('Supprimer le logo actuel ?', 'delete-logo-cb')">
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2"/><path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2"/></svg>
                                Supprimer
                            </button>
                        </div>
                    @endif

                    <div class="upload-area" onclick="document.getElementById('logo-input').click();">
                        <input type="file" id="logo-input" name="logo" accept="image/*" onchange="previewLogo(this)">
                        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" style="color:#94a3b8;margin-bottom:.4rem;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="1.8"/><polyline points="17,8 12,3 7,8" stroke="currentColor" stroke-width="1.8"/><line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="1.8"/></svg>
                        <div style="font-size:.82rem;color:#64748b;font-weight:500;">Cliquez pour uploader un logo</div>
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:.2rem;">PNG, JPG, SVG — max 2 Mo</div>
                        <img id="logo-preview" style="display:none;max-width:80px;max-height:80px;object-fit:contain;margin-top:.75rem;border-radius:.4rem;" alt="">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Info --}}
        <div class="card" style="border:none;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;">
            <div class="card-body" style="height:100%;display:flex;flex-direction:column;justify-content:center;gap:1rem;">
                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;">
                    <div style="width:36px;height:36px;background:rgba(16,185,129,.2);border-radius:.5rem;display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <span style="font-size:.95rem;font-weight:700;">Comment ça marche</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:.75rem;">
                    <div style="display:flex;gap:.65rem;align-items:flex-start;">
                        <div style="width:22px;height:22px;background:#10b981;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.7rem;font-weight:700;">1</div>
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:#f1f5f9;">Logo</div>
                            <div style="font-size:.75rem;color:rgba(255,255,255,.5);line-height:1.5;">Affiché dans l'onglet navigateur (favicon) et dans la barre latérale à la place de l'icône voiture.</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:.65rem;align-items:flex-start;">
                        <div style="width:22px;height:22px;background:#10b981;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.7rem;font-weight:700;">2</div>
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:#f1f5f9;">Images carousel</div>
                            <div style="font-size:.75rem;color:rgba(255,255,255,.5);line-height:1.5;">3 images max qui défilent sur le panneau gauche de la page de connexion. Format paysage recommandé (16:9).</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:.65rem;align-items:flex-start;">
                        <div style="width:22px;height:22px;background:#10b981;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.7rem;font-weight:700;">3</div>
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:#f1f5f9;">Légendes</div>
                            <div style="font-size:.75rem;color:rgba(255,255,255,.5);line-height:1.5;">Texte affiché sous chaque image du carousel. Facultatif.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Images Carousel ──────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" stroke="#10b981" stroke-width="2"/><path d="M16 3l-4-1-4 1" stroke="#10b981" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span class="card-title">Images du carousel (page de connexion)</span>
        </div>
        <div class="card-body">
            <p style="font-size:.82rem;color:#64748b;margin-bottom:1.25rem;">
                Ces images défilent automatiquement sur le panneau gauche de la page de connexion. Ajoutez jusqu'à 3 images.
                Format recommandé : 800×600 px ou plus grand, ratio 4:3 ou 16:9.
            </p>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;">
                @foreach([1, 2, 3] as $i)
                @php $img = $settings["carousel_image_{$i}"]; $cap = $settings["carousel_caption_{$i}"]; @endphp
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.6rem;overflow:hidden;">

                    {{-- Header --}}
                    <div style="padding:.6rem .85rem;background:#fff;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:.78rem;font-weight:700;color:#475569;">Image {{ $i }}</span>
                        @if($img)
                            <input type="checkbox" id="del-carousel-{{ $i }}" name="delete_carousel_{{ $i }}" value="1" style="display:none;">
                            <button type="button" class="btn btn-danger" style="padding:.25rem .55rem;font-size:.72rem;"
                                    onclick="confirmDeleteSetting('Supprimer l\'image {{ $i }} du carousel ?', 'del-carousel-{{ $i }}')">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24"><polyline points="3,6 5,6 21,6" stroke="currentColor" stroke-width="2.5"/><path d="M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2"/></svg>
                                Supprimer
                            </button>
                        @endif
                    </div>

                    {{-- Aperçu --}}
                    <div style="padding:.85rem;">
                        @if($img)
                            <img src="{{ Storage::url($img) }}" class="preview-img" alt="Carousel {{ $i }}" id="preview-{{ $i }}">
                        @else
                            <div class="preview-placeholder" id="preview-{{ $i }}">
                                <svg width="30" height="30" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#cbd5e1" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" stroke="#cbd5e1" stroke-width="1.5"/><path d="M21 15l-5-5L5 21" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <span>Aucune image</span>
                            </div>
                        @endif

                        {{-- Upload --}}
                        <input type="file" id="carousel-input-{{ $i }}" name="carousel_image_{{ $i }}" accept="image/*"
                               style="display:none;" onchange="previewCarousel(this, {{ $i }})">
                        <div class="upload-area" style="padding:.75rem;margin-top:.75rem;" onclick="document.getElementById('carousel-input-{{ $i }}').click();">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="color:#94a3b8;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="1.8"/><polyline points="17,8 12,3 7,8" stroke="currentColor" stroke-width="1.8"/><line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="1.8"/></svg>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:.3rem;">{{ $img ? 'Remplacer' : 'Uploader' }}</div>
                        </div>

                        {{-- Légende --}}
                        <div style="margin-top:.75rem;">
                            <label style="font-size:.73rem;color:#64748b;font-weight:600;display:block;margin-bottom:.3rem;">Légende (facultatif)</label>
                            <input type="text" name="carousel_caption_{{ $i }}"
                                   value="{{ old('carousel_caption_'.$i, $cap) }}"
                                   placeholder="Ex : Gestion de flotte simplifiée"
                                   style="width:100%;padding:.45rem .65rem;border:1.5px solid #e2e8f0;border-radius:.4rem;font-size:.78rem;background:#fff;color:#0f172a;outline:none;box-sizing:border-box;">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Actions ───────────────────────────────────────────────────────────── --}}
    <div style="display:flex;justify-content:flex-end;gap:.75rem;">
        <a href="{{ route('dashboard') }}" class="btn" style="background:#f1f5f9;color:#374151;">Annuler</a>
        <button type="submit" class="btn btn-primary" style="padding:.6rem 1.5rem;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" stroke="currentColor" stroke-width="2"/><polyline points="17,21 17,13 7,13 7,21" stroke="currentColor" stroke-width="2"/><polyline points="7,3 7,8 15,8" stroke="currentColor" stroke-width="2"/></svg>
            Enregistrer les paramètres
        </button>
    </div>

</form>

<script>
function confirmDeleteSetting(message, checkboxId) {
    SwalConfirm.fire({
        title: 'Confirmer la suppression',
        text: message,
        icon: 'warning',
        confirmButtonText: 'Supprimer',
        confirmButtonColor: '#dc2626',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(checkboxId).checked = true;
            document.getElementById(checkboxId).closest('form').submit();
        }
    });
}

function previewLogo(input) {
    const preview = document.getElementById('logo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewCarousel(input, i) {
    const container = document.getElementById('preview-' + i);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            container.outerHTML = `<img src="${e.target.result}" class="preview-img" id="preview-${i}" alt="">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
