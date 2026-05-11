@extends('layouts.dashboard')
@section('title', 'Nouvelle fiche de contrôle')
@section('page-title', 'Nouvelle fiche de contrôle')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('inspections.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Contrôles</a>
    <span>›</span>
    <span style="color:#374151;">Nouvelle fiche</span>
</div>

<form id="inspection-form" method="POST" action="{{ route('inspections.store') }}" enctype="multipart/form-data">
    @csrf
    @include('inspections._form', [
        'isEdit'     => false,
        'inspection' => null,
        'preVehicle' => $preVehicle ?? null,
        'preDriver'  => $preDriver  ?? null,
    ])
</form>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var form  = document.getElementById('inspection-form');
    var csrf  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (!form) return;

    // ── Intercepter le submit si hors ligne ──────────────────────────────────
    form.addEventListener('submit', function (e) {
        if (navigator.onLine) return; // en ligne → soumission normale

        e.preventDefault();

        var data = collectFormData(form);
        data._csrf     = csrf;
        data._saved_at = new Date().toISOString();
        data._url      = form.action;

        // Sauvegarder dans IndexedDB + enregistrer Background Sync
        window.MiensaFleetOffline.save('pending_inspections', data)
            .then(function () {
                showOfflineConfirmation();
                requestNotificationPermission();
            })
            .catch(function (err) {
                console.error('[Offline] Échec sauvegarde :', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de sauvegarde',
                    text:  'Impossible de sauvegarder la fiche hors ligne.',
                    confirmButtonColor: '#10b981',
                });
            });
    });

    // ── Collecte des données du formulaire (sans fichiers) ───────────────────
    function collectFormData(formEl) {
        var result = {};
        var fd     = new FormData(formEl);

        fd.forEach(function (value, key) {
            // Ignorer les fichiers et le token CSRF
            if (value instanceof File || key === '_token') return;
            if (result.hasOwnProperty(key)) {
                // Champs multiples (checkboxes, selects multiples)
                if (!Array.isArray(result[key])) result[key] = [result[key]];
                result[key].push(value);
            } else {
                result[key] = value;
            }
        });

        return result;
    }

    // ── Modale de confirmation hors ligne ────────────────────────────────────
    function showOfflineConfirmation() {
        if (typeof Swal === 'undefined') {
            alert('✅ Fiche sauvegardée localement.\nElle sera envoyée au retour du réseau.');
            return;
        }

        Swal.fire({
            html: '<div style="text-align:center;">'
                + '<div style="font-size:48px;margin-bottom:12px;">✅</div>'
                + '<div style="font-size:17px;font-weight:700;color:#0f172a;margin-bottom:8px;">'
                + 'Fiche sauvegardée localement'
                + '</div>'
                + '<div style="font-size:14px;color:#64748b;line-height:1.6;">'
                + 'Elle sera envoyée automatiquement<br>au retour du réseau.'
                + '</div>'
                + '</div>',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#10b981',
            showCancelButton: true,
            cancelButtonText: 'Voir mes fiches',
            cancelButtonColor: '#f8fafc',
            customClass: { cancelButton: 'swal-cancel-muted' },
        }).then(function (result) {
            if (result.isDismissed) {
                window.location.href = '{{ route("inspections.index") }}';
            }
        });
    }

    // ── Permission notifications ──────────────────────────────────────────────
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

})();
</script>
@endpush
