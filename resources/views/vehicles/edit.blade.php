@extends('layouts.dashboard')

@section('title', 'Modifier — ' . $vehicle->plate)
@section('page-title', 'Modifier le véhicule')

@section('content')
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('vehicles.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Véhicules</a>
    <span>›</span>
    <a href="{{ route('vehicles.show', $vehicle) }}" style="color:#10b981;text-decoration:none;font-weight:500;">{{ $vehicle->plate }}</a>
    <span>›</span>
    <span style="color:#374151;">Modifier</span>
</div>

<form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('vehicles._form')
</form>
@endsection
