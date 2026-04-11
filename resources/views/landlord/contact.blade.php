@extends('landlord.layouts.app')

@section('title', 'Nous contacter')

@push('styles')
<style>
    .contact-wrap { max-width: 680px; margin: 4rem auto; padding: 0 1.5rem; }
    .contact-wrap h1 { font-size: 1.9rem; font-weight: 800; color: #0f172a; margin-bottom: .5rem; }
    .contact-wrap > p { color: #64748b; margin-bottom: 2rem; font-size: .95rem; line-height: 1.7; }

    .contact-card { background: white; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 2rem; margin-bottom: 1.5rem; }
    .contact-card h2 { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 1.25rem; }

    .form-group { margin-bottom: 1.1rem; }
    .form-label { display: block; font-size: .83rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }
    .form-control {
        display: block; width: 100%; padding: .65rem .9rem;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: .92rem; color: #0f172a; outline: none; font-family: inherit;
        transition: border-color .15s, box-shadow .15s;
    }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    textarea.form-control { resize: vertical; min-height: 120px; }

    .btn-send {
        background: #1d4ed8; color: white; border: none; border-radius: 8px;
        padding: .75rem 2rem; font-size: .95rem; font-weight: 700;
        cursor: pointer; font-family: inherit; transition: background .15s;
    }
    .btn-send:hover { background: #1e40af; }

    .contact-infos { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .contact-info-item {
        display: flex; align-items: flex-start; gap: .75rem;
        background: white; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 1rem;
        font-size: .88rem;
    }
    .cii-icon { width: 36px; height: 36px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cii-icon svg { color: #3b82f6; }
    .cii-label { font-size: .75rem; color: #94a3b8; margin-bottom: .15rem; }
    .cii-value { font-weight: 600; color: #0f172a; }

    @media (max-width: 600px) { .contact-infos { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="contact-wrap">
    <h1>Nous contacter</h1>
    <p>Une question sur les tarifs, un besoin spécifique ou une démonstration ? Notre équipe est disponible pour vous accompagner.</p>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:.875rem 1rem;margin-bottom:1.5rem;font-size:.9rem;color:#166534;">
        {{ session('success') }}
    </div>
    @endif

    <div class="contact-card">
        <h2>Envoyer un message</h2>
        <form>
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Votre nom</label>
                    <input type="text" class="form-control" placeholder="Jean Kouassi">
                </div>
                <div class="form-group">
                    <label class="form-label">Société</label>
                    <input type="text" class="form-control" placeholder="Geomatos SARL">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" placeholder="contact@geomatos.ci">
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea class="form-control" placeholder="Décrivez votre besoin…"></textarea>
            </div>
            <button type="submit" class="btn-send">Envoyer le message →</button>
        </form>
    </div>

    <div class="contact-infos">
        <div class="contact-info-item">
            <div class="cii-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
                <div class="cii-label">Email</div>
                <div class="cii-value">contact@miensafleet.ci</div>
            </div>
        </div>
        <div class="contact-info-item">
            <div class="cii-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.23h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.27a16 16 0 0 0 5.82 5.82l.91-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.23 16l-.31.92z"/></svg>
            </div>
            <div>
                <div class="cii-label">Téléphone / WhatsApp</div>
                <div class="cii-value">+225 27 00 00 00 00</div>
            </div>
        </div>
    </div>
</div>
@endsection
