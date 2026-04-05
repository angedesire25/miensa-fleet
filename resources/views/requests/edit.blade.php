@extends('layouts.dashboard')
@section('title', 'Modifier la demande #' . $vehicleRequest->id)
@section('page-title', 'Modifier la demande')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('requests.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Demandes</a>
    <span>›</span>
    <a href="{{ route('requests.show', $vehicleRequest) }}" style="color:#10b981;text-decoration:none;font-weight:500;">Demande #{{ $vehicleRequest->id }}</a>
    <span>›</span>
    <span style="color:#374151;">Modifier</span>
</div>

<form method="POST" action="{{ route('requests.update', $vehicleRequest) }}">
    @csrf @method('PUT')
    @include('requests._form')
</form>

@endsection
