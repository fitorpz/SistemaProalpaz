<!-- resources/views/admin/dashboard.blade.php -->
@extends('layouts.app')

@include('layouts.header')


@section('content')
<div class="container mt-5">
    <h2 class="text-center">Panel de Control Administrativo <img src="{{ asset('assets/img/logoHeader.png') }}" alt=""></h2>

    <!-- Centrar las tarjetas usando utilidades de Bootstrap -->
    <div class="row mt-5 justify-content-center">
        <!-- Opción 1: Gestionar Inventario -->
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Gestionar Inventario</h5>
                    <p class="card-text">Administra los productos del inventario.</p>
                    <a href="{{ route('inventory.index') }}" class="btn btn-success">Ir a inventarios</a>
                </div>
            </div>
        </div>

        <!-- Opción 2: Crear Usuario (solo para Administradores) -->
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Crear Usuario</h5>
                    <p class="card-text">Registra un nuevo usuario con rol en el sistema.</p>
                    <a href="{{ route('users.index') }}" class="btn btn-success">Crear Usuario</a>
                </div>
            </div>
        </div>

        <!-- Opción 3: Generar reportes (Solo para administradores) -->
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Reportes</h5>
                    <p class="card-text">Genera reportes en PDF</p>
                    <a href="#" class="btn btn-success">Reportes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
