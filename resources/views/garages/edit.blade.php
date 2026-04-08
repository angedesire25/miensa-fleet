@extends('layouts.dashboard')

@section('title', 'Modifier ' . $garage->name)
@section('page-title', 'Garages — Modifier ' . $garage->name)

@section('content')
@include('garages._form', [
    'garage' => $garage,
    'action' => route('garages.update', $garage),
    'method' => 'PUT',
])
@endsection
