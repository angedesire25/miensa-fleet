@extends('layouts.dashboard')

@section('title', 'Modifier le sinistre #' . $incident->id)
@section('page-title', 'Sinistres — Modifier #' . $incident->id)

@section('content')
@include('incidents._form', [
    'incident' => $incident,
    'vehicles' => $vehicles,
    'drivers'  => $drivers,
    'action'   => route('incidents.update', $incident),
    'method'   => 'PUT',
])
@endsection
