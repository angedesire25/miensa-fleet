@extends('layouts.dashboard')

@section('title', 'Nouveau chauffeur')
@section('page-title', 'Ajouter un chauffeur')

@section('content')
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('drivers.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Chauffeurs</a>
    <span>›</span>
    <span style="color:#374151;">Nouveau chauffeur</span>
</div>

<form method="POST" action="{{ route('drivers.store') }}" enctype="multipart/form-data">
    @csrf
    @include('drivers._form')
</form>
@endsection
