@extends('layouts.dashboard')

@section('title', 'Nouvelle station')
@section('page-title', 'Carburant')
@section('breadcrumb', 'Nouvelle station')

@section('content')
<div class="page-content" style="max-width:640px;">

    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;">
        <a href="{{ route('fuel.admin.stations') }}"
           style="display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;background:#fff;border:1px solid #e2e8f0;border-radius:.45rem;color:#64748b;text-decoration:none;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </a>
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;color:#0f172a;margin:0;">Ajouter une station carburant</h1>
        </div>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.65rem;padding:.9rem 1.1rem;margin-bottom:1.25rem;">
        <ul style="margin:0;padding-left:1.25rem;color:#b91c1c;font-size:.85rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('fuel.admin.station-store') }}"
          style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.75rem;">
        @csrf
        @include('fuel.admin._station-form', ['station' => null])
        <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;">
            <a href="{{ route('fuel.admin.stations') }}"
               style="padding:.6rem 1.2rem;border:1px solid #e2e8f0;border-radius:.45rem;font-size:.875rem;color:#64748b;text-decoration:none;">
                Annuler
            </a>
            <button type="submit"
                    style="padding:.6rem 1.4rem;background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:.45rem;font-size:.875rem;font-weight:600;cursor:pointer;">
                Enregistrer
            </button>
        </div>
    </form>

</div>
@endsection
