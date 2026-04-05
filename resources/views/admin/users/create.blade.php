@extends('layouts.dashboard')

@section('title', 'Nouvel utilisateur')
@section('page-title', 'Administration — Nouvel utilisateur')

@section('content')
@include('admin.users._form', ['user' => null, 'roles' => $roles, 'action' => route('admin.users.store'), 'method' => 'POST'])
@endsection
