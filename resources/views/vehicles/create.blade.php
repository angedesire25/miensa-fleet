@extends('layouts.dashboard')

@section('title', 'Nouveau véhicule')
@section('page-title', 'Ajouter un véhicule')

@section('content')
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('vehicles.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Véhicules</a>
    <span>›</span>
    <span style="color:#374151;">Nouveau véhicule</span>
</div>

<form method="POST" action="{{ route('vehicles.store') }}" enctype="multipart/form-data">
    @csrf
    @include('vehicles._form')
</form>
@endsection
