@extends('layouts.dashboard')

@section('title', 'Modifier infraction #' . $infraction->id)

@section('content')
<div style="padding:1.5rem;max-width:900px;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem;font-size:.82rem;color:#64748b;">
        <a href="{{ route('infractions.index') }}" style="color:#94a3b8;text-decoration:none;">Infractions</a>
        <span>/</span>
        <a href="{{ route('infractions.show', $infraction) }}" style="color:#94a3b8;text-decoration:none;">#{{ $infraction->id }}</a>
        <span>/</span>
        <span style="color:#f1f5f9;">Modifier</span>
    </div>

    <div style="background:#1e293b;border:1px solid #334155;border-radius:.65rem;padding:1.5rem;">
        <h2 style="font-size:1rem;font-weight:700;color:#f1f5f9;margin:0 0 1.5rem;">
            Modifier l'infraction #{{ $infraction->id }}
        </h2>

        <form method="POST" action="{{ route('infractions.update', $infraction) }}">
            @csrf
            @method('PUT')

            @include('infractions._form')

            <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid #334155;">
                <a href="{{ route('infractions.show', $infraction) }}"
                   style="padding:.55rem 1.25rem;background:#334155;color:#94a3b8;border-radius:.45rem;text-decoration:none;font-size:.88rem;">
                    Annuler
                </a>
                <button type="submit"
                        style="padding:.55rem 1.25rem;background:#10b981;color:#fff;border:none;border-radius:.45rem;font-size:.88rem;font-weight:600;cursor:pointer;">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
