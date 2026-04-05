@extends('layouts.dashboard')
@section('title', 'Nouvelle affectation')
@section('page-title', 'Nouvelle affectation')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('assignments.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Affectations</a>
    <span>›</span>
    <span style="color:#374151;">Nouvelle affectation</span>
</div>

<form method="POST" action="{{ route('assignments.store') }}">
    @csrf
    @include('assignments._form', [
        'isEdit'     => false,
        'assignment' => null,
    ])
</form>

@endsection
