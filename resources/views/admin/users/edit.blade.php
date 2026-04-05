@extends('layouts.dashboard')

@section('title', 'Modifier — ' . $user->name)
@section('page-title', 'Administration — Modifier l\'utilisateur')

@section('content')
@include('admin.users._form', ['user' => $user, 'roles' => $roles, 'action' => route('admin.users.update', $user), 'method' => 'PUT'])
@endsection
