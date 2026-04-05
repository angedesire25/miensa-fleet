@extends('layouts.dashboard')
@section('title', 'Modifier fiche #' . $inspection->id)
@section('page-title', 'Modifier la fiche de contrôle')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('inspections.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Contrôles</a>
    <span>›</span>
    <a href="{{ route('inspections.show', $inspection) }}" style="color:#10b981;text-decoration:none;font-weight:500;">Fiche #{{ $inspection->id }}</a>
    <span>›</span>
    <span style="color:#374151;">Modifier</span>
</div>

@if($inspection->isRejected() && $inspection->rejection_reason)
<div style="padding:.85rem 1.1rem;background:#fffbeb;border:1px solid #fcd34d;border-radius:.65rem;margin-bottom:1.25rem;display:flex;gap:.65rem;align-items:flex-start;">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:.1rem;"><path d="M12 9v4M12 17h.01" stroke="#d97706" stroke-width="2" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#d97706" stroke-width="2"/></svg>
    <div>
        <div style="font-size:.8rem;font-weight:700;color:#92400e;margin-bottom:.2rem;">Fiche renvoyée pour correction</div>
        <div style="font-size:.83rem;color:#78350f;">{{ $inspection->rejection_reason }}</div>
    </div>
</div>
@endif

<form method="POST" action="{{ route('inspections.update', $inspection) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('inspections._form', [
        'isEdit'     => true,
        'preVehicle' => null,
        'preDriver'  => null,
    ])
</form>

@endsection
