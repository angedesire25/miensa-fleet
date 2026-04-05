@extends('layouts.dashboard')

@section('title', 'Modifier — ' . $driver->full_name)
@section('page-title', 'Modifier le chauffeur')

@section('content')
<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('drivers.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Chauffeurs</a>
    <span>›</span>
    <a href="{{ route('drivers.show', $driver) }}" style="color:#10b981;text-decoration:none;font-weight:500;">{{ $driver->full_name }}</a>
    <span>›</span>
    <span style="color:#374151;">Modifier</span>
</div>

<form method="POST" action="{{ route('drivers.update', $driver) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('drivers._form')
</form>
@endsection
