@extends('layouts.dashboard')

@section('title', 'Déclarer un sinistre')
@section('page-title', 'Sinistres — Déclarer un incident')

@section('content')
@include('incidents._form', [
    'incident' => null,
    'vehicles' => $vehicles,
    'drivers'  => $drivers,
    'action'   => route('incidents.store'),
    'method'   => 'POST',
])
@endsection
