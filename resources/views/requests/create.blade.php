@extends('layouts.dashboard')
@section('title', 'Nouvelle demande de véhicule')
@section('page-title', 'Nouvelle demande de véhicule')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('requests.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Demandes</a>
    <span>›</span>
    <span style="color:#374151;">Nouvelle demande</span>
</div>

<form method="POST" action="{{ route('requests.store') }}">
    @csrf
    @include('requests._form')
</form>

@endsection
