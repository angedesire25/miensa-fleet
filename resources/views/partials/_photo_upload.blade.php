{{--
    Composant upload de photos réutilisable.
    Variables attendues :
      $contextOptions  — tableau associatif ['valeur' => 'Libellé'] des contextes disponibles
      $defaultContext  — valeur par défaut du contexte
      $existingPhotos  — Collection VehiclePhoto (photos déjà sauvegardées, mode édition)
      $deleteRoute     — nom de la route pour supprimer une photo existante (ex: 'incidents.delete-photo')
      $deleteRouteParam— instance du modèle parent (ex: $incident ou $repair)
--}}
@php
    $existingPhotos  = $existingPhotos  ?? collect();
    $contextOptions  = $contextOptions  ?? [];
    $defaultContext  = $defaultContext  ?? array_key_first($contextOptions);
    $deleteRoute     = $deleteRoute     ?? null;
    $deleteRouteParam= $deleteRouteParam ?? null;
    $uploadId        = 'photo-upload-' . uniqid(); // Identifiant unique si plusieurs composants sur la page
@endphp

<div class="card" id="{{ $uploadId }}-card">
    <div class="card-head">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#10b981" stroke-width="1.8"/><circle cx="8.5" cy="8.5" r="1.5" fill="#10b981"/><polyline points="21,15 16,10 5,21" stroke="#10b981" stroke-width="1.8" stroke-linecap="round"/></svg>
        <span class="card-title">Photos</span>
        <span id="{{ $uploadId }}-count" style="font-size:.75rem;color:#94a3b8;margin-left:.25rem;">(0 sélectionnée(s))</span>
    </div>
    <div style="padding:1.25rem;">

        {{-- Photos existantes (mode édition) --}}
        @if($existingPhotos->isNotEmpty())
        <div style="margin-bottom:1.25rem;">
            <div style="font-size:.78rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.6rem;">Photos enregistrées</div>
            <div style="display:flex;flex-wrap:wrap;gap:.6rem;" id="{{ $uploadId }}-existing">
                @foreach($existingPhotos as $photo)
                <div style="position:relative;width:100px;flex-shrink:0;" id="existing-{{ $photo->id }}">
                    <a href="{{ asset('storage/' . $photo->file_path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $photo->file_path) }}" alt=""
                             style="width:100px;height:80px;object-fit:cover;border-radius:.45rem;border:1.5px solid #e2e8f0;display:block;">
                    </a>
                    <div style="font-size:.65rem;color:#64748b;margin-top:.2rem;text-align:center;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ $photo->context === 'incident_before' ? 'Avant' :
                          ($photo->context === 'incident_damage' ? 'Dégâts' :
                          ($photo->context === 'repair_in_progress' ? 'En cours' : 'Après')) }}
                    </div>
                    @if($deleteRoute && $deleteRouteParam)
                    <form method="POST" action="{{ route($deleteRoute, $deleteRouteParam) }}" style="position:absolute;top:4px;right:4px;"
                          onsubmit="return confirm('Supprimer cette photo ?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="photo_id" value="{{ $photo->id }}">
                        <button type="submit" style="background:rgba(239,68,68,.9);border:none;border-radius:50%;width:20px;height:20px;color:#fff;cursor:pointer;font-size:.75rem;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Zone drag-drop --}}
        <div id="{{ $uploadId }}-zone"
             style="border:2px dashed #e2e8f0;border-radius:.6rem;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .15s;background:#fafbfc;"
             ondragover="event.preventDefault();this.style.borderColor='#10b981';"
             ondragleave="this.style.borderColor='#e2e8f0';"
             ondrop="handlePhotoDrop_{{ $uploadId }}(event)"
             onclick="document.getElementById('{{ $uploadId }}-input').click()">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" style="color:#94a3b8;margin-bottom:.5rem;"><rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><polyline points="21,15 16,10 5,21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <div style="font-size:.855rem;color:#374151;font-weight:500;">Glissez vos photos ici ou <span style="color:#10b981;text-decoration:underline;">parcourir</span></div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.25rem;">JPEG, PNG, WebP · Max 5 Mo par photo · 10 max</div>
        </div>
        <input type="file" id="{{ $uploadId }}-input" name="photos[]" multiple accept="image/jpeg,image/png,image/webp"
               style="display:none;" onchange="previewPhotos_{{ $uploadId }}(this.files)">

        {{-- Prévisualisation --}}
        <div id="{{ $uploadId }}-preview" style="display:flex;flex-wrap:wrap;gap:.6rem;margin-top:.85rem;"></div>

        {{-- Contextes (si plusieurs options) --}}
        @if(count($contextOptions) > 1)
        <div style="margin-top:.85rem;font-size:.8rem;color:#64748b;">
            <strong>Contexte par défaut des nouvelles photos :</strong>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.35rem;">
                @foreach($contextOptions as $val => $lbl)
                <label style="display:flex;align-items:center;gap:.3rem;cursor:pointer;">
                    <input type="radio" name="photo_default_context" value="{{ $val }}" id="{{ $uploadId }}-ctx-{{ $val }}"
                           @checked($val === $defaultContext) style="accent-color:#10b981;">
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
            <p style="font-size:.72rem;color:#94a3b8;margin:.35rem 0 0;">
                Vous pouvez changer le contexte individuellement sous chaque aperçu.
            </p>
        </div>
        @else
        {{-- Contexte unique caché --}}
        @foreach($contextOptions as $val => $lbl)
        <input type="hidden" name="photo_default_context" value="{{ $val }}">
        @endforeach
        @endif
    </div>
</div>

<script>
(function() {
    // Fichiers sélectionnés (accumulation)
    let selectedFiles_{{ $uploadId }} = [];

    // Prévisualiser les fichiers sélectionnés via input
    window.previewPhotos_{{ $uploadId }} = function(files) {
        addFiles_{{ $uploadId }}(Array.from(files));
        // Réinitialiser l'input pour permettre de re-sélectionner les mêmes fichiers
        document.getElementById('{{ $uploadId }}-input').value = '';
    };

    // Gérer le drag & drop
    window.handlePhotoDrop_{{ $uploadId }} = function(event) {
        event.preventDefault();
        document.getElementById('{{ $uploadId }}-zone').style.borderColor = '#e2e8f0';
        const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
        addFiles_{{ $uploadId }}(files);
    };

    function addFiles_{{ $uploadId }}(files) {
        files.forEach(file => {
            if (selectedFiles_{{ $uploadId }}.length >= 10) return;
            selectedFiles_{{ $uploadId }}.push(file);
            renderPreview_{{ $uploadId }}();
        });
        syncInputs_{{ $uploadId }}();
        updateCount_{{ $uploadId }}();
    }

    function renderPreview_{{ $uploadId }}() {
        const container = document.getElementById('{{ $uploadId }}-preview');
        container.innerHTML = '';

        selectedFiles_{{ $uploadId }}.forEach((file, idx) => {
            const url = URL.createObjectURL(file);
            const div = document.createElement('div');
            div.style.cssText = 'position:relative;width:100px;flex-shrink:0;';

            // Image
            const img = document.createElement('img');
            img.src = url;
            img.style.cssText = 'width:100px;height:80px;object-fit:cover;border-radius:.45rem;border:1.5px solid #e2e8f0;display:block;';
            div.appendChild(img);

            // Sélecteur de contexte si plusieurs options
            @if(count($contextOptions) > 1)
            const sel = document.createElement('select');
            sel.name = 'photo_contexts[' + idx + ']';
            sel.style.cssText = 'width:100%;font-size:.65rem;margin-top:.2rem;border:1px solid #e2e8f0;border-radius:.3rem;padding:.15rem .3rem;';
            @foreach($contextOptions as $val => $lbl)
            const o{{ $loop->index }} = document.createElement('option');
            o{{ $loop->index }}.value = '{{ $val }}';
            o{{ $loop->index }}.text  = '{{ $lbl }}';
            // Sélectionner le contexte par défaut choisi
            const defaultCtxEl = document.querySelector('input[name="photo_default_context"]:checked');
            if (defaultCtxEl && defaultCtxEl.value === '{{ $val }}') o{{ $loop->index }}.selected = true;
            sel.appendChild(o{{ $loop->index }});
            @endforeach
            div.appendChild(sel);
            @else
            // Contexte unique : champ caché
            const hidCtx = document.createElement('input');
            hidCtx.type  = 'hidden';
            hidCtx.name  = 'photo_contexts[' + idx + ']';
            hidCtx.value = '{{ $defaultContext }}';
            div.appendChild(hidCtx);
            // Label discret
            const lbl = document.createElement('div');
            lbl.style.cssText = 'font-size:.65rem;color:#64748b;margin-top:.2rem;text-align:center;';
            lbl.textContent   = '{{ reset($contextOptions) }}';
            div.appendChild(lbl);
            @endif

            // Bouton supprimer
            const btn = document.createElement('button');
            btn.type  = 'button';
            btn.textContent = '×';
            btn.style.cssText = 'position:absolute;top:4px;right:4px;background:rgba(239,68,68,.9);border:none;border-radius:50%;width:20px;height:20px;color:#fff;cursor:pointer;font-size:.8rem;line-height:1;display:flex;align-items:center;justify-content:center;';
            btn.onclick = () => {
                selectedFiles_{{ $uploadId }}.splice(idx, 1);
                renderPreview_{{ $uploadId }}();
                syncInputs_{{ $uploadId }}();
                updateCount_{{ $uploadId }}();
            };
            div.appendChild(btn);

            container.appendChild(div);
        });
    }

    function syncInputs_{{ $uploadId }}() {
        // Recréer un FileList synthétique dans un input hidden pour chaque fichier
        const dt = new DataTransfer();
        selectedFiles_{{ $uploadId }}.forEach(f => dt.items.add(f));
        document.getElementById('{{ $uploadId }}-input').files = dt.files;
    }

    function updateCount_{{ $uploadId }}() {
        document.getElementById('{{ $uploadId }}-count').textContent =
            '(' + selectedFiles_{{ $uploadId }}.length + ' sélectionnée(s))';
    }
})();
</script>
