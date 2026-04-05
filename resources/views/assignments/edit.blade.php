@extends('layouts.dashboard')
@section('title', 'Modifier affectation #' . $assignment->id)
@section('page-title', 'Modifier l\'affectation')

@section('content')

<div style="display:flex;align-items:center;gap:.5rem;font-size:.825rem;color:#94a3b8;margin-bottom:1.25rem;">
    <a href="{{ route('assignments.index') }}" style="color:#10b981;text-decoration:none;font-weight:500;">Affectations</a>
    <span>›</span>
    <a href="{{ route('assignments.show', $assignment) }}" style="color:#10b981;text-decoration:none;font-weight:500;">Affectation #{{ $assignment->id }}</a>
    <span>›</span>
    <span style="color:#374151;">Modifier</span>
</div>

<form method="POST" action="{{ route('assignments.update', $assignment) }}">
    @csrf @method('PUT')
    @include('assignments._form', [
        'isEdit'     => true,
        'preVehicle' => null,
        'preDriver'  => null,
    ])
</form>

@endsection
