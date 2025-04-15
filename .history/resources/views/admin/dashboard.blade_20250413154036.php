@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Panel de Administración') }}
    </h2>
@endsection

@section('content')
    <div class="bg-white p-6 rounded-lg shadow">
        <p>Bienvenido, administrador. Aquí podrás gestionar los usuarios, roles y cursos.</p>
    </div>
@endsection
