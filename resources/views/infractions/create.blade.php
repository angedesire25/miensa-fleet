@extends('layouts.dashboard')

@section('title', 'Nouvelle infraction')

@section('content')
<div style="padding:1.5rem;max-width:900px;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('infractions.index') }}" style="color:#94a3b8;text-decoration:none;">Infractions</a>
        <span>/</span>
        <span style="color:#f1f5f9;">Nouvelle infraction</span>
    </div>

    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.5rem;">
        <h2 style="font-size:1rem;font-weight:700;color:#f1f5f9;margin:0 0 1.5rem;">Enregistrer une infraction</h2>

        <form method="POST" action="{{ route('infractions.store') }}">
            @csrf

            @include('infractions._form')

            <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid #334155;">
                <a href="{{ route('infractions.index') }}"
                   style="padding:.55rem 1.25rem;background:#334155;color:#94a3b8;border-radius:.45rem;text-decoration:none;font-size:.88rem;">
                    Annuler
                </a>
                <button type="submit"
                        style="padding:.55rem 1.25rem;background:#10b981;color:#fff;border:none;border-radius:.45rem;font-size:.88rem;font-weight:600;cursor:pointer;">
                    Enregistrer l'infraction
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
