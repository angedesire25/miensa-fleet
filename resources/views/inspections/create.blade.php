@extends('layouts.dashboard')
@section('title', 'Nouvelle fiche de contrôle')
@section('page-title', 'Nouvelle fiche de contrôle')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('inspections.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Contrôles</a>
    <span>›</span>
    <span style="color:#374151;">Nouvelle fiche</span>
</div>

<form method="POST" action="{{ route('inspections.store') }}" enctype="multipart/form-data">
    @csrf
    @include('inspections._form', [
        'isEdit'     => false,
        'inspection' => null,
        'preVehicle' => $preVehicle ?? null,
        'preDriver'  => $preDriver  ?? null,
    ])
</form>

@endsection
