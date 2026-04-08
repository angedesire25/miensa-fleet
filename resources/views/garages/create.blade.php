@extends('layouts.dashboard')

@section('title', 'Nouveau garage')
@section('page-title', 'Garages — Ajouter un garage')

@section('content')
@include('garages._form', [
    'garage' => null,
    'action' => route('garages.store'),
    'method' => 'POST',
])
@endsection
