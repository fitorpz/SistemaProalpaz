@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <!-- Módulo de Ventas -->
        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white fs-4">
                    <i class="fas fa-shopping-cart me-2"></i> Ventas
                </div>
                <div class="card-body">
                    <p><i class="fas fa-file-invoice-dollar me-2"></i>Acceso a la gestión de ventas.</p>
                    <a href="{{ route('sales.dashboard') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-right me-2"></i> Ir a Ventas
                    </a>
                </div>
            </div>
        </div>

        <!-- Módulo de Inventarios -->
        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white fs-4">
                    <i class="fas fa-boxes me-2"></i> Inventarios
                </div>
                <div class="card-body">
                    <p><i class="fas fa-warehouse me-2"></i>Acceso a la gestión de inventarios.</p>
                    <a href="{{ route('inventario.index') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-right me-2"></i> Ir a Inventarios
                    </a>
                </div>
            </div>
        </div>
        @if(Auth::user()->rol == 'administrador')
        <!-- Módulo de Gestión de Usuarios -->
        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white fs-4">
                    <i class="fas fa-users-cog me-2"></i> Gestión de Usuarios
                </div>
                <div class="card-body">
                    <p><i class="fas fa-user-shield me-2"></i>Acceso a la gestión de usuarios.</p>
                    <a href="{{ route('users.index') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-right me-2"></i> Ir a Gestión de Usuarios
                    </a>
                </div>
            </div>
        </div>
        @endif
        <!-- Módulo de Reportes -->
        @auth
        @if(auth()->user()->rol === 'administrador')
        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white fs-4">
                    <i class="fas fa-chart-line me-2"></i> Reportes e Información
                </div>
                <div class="card-body">
                    <p><i class="fas fa-chart-pie me-2"></i>Acceso a los reportes.</p>
                    <a href="{{ route('reportes.index') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-right me-2"></i> Ver Reportes
                    </a>
                </div>
            </div>
        </div>
        @endif
        @endauth

        <!-- Módulo de Contabilidad -->
        @if(auth()->user()->rol == 'administrador' || auth()->user()->rol == 'usuario_operador')
        <div class="col-12 col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white fs-4">
                    <i class="fas fa-calculator me-2"></i> Contabilidad
                </div>
                <div class="card-body">
                    <p><i class="fas fa-money-bill-wave me-2"></i>Acceso a la gestión de cobranzas y finanzas.</p>
                    <a href="{{ route('contabilidad.index') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-right me-2"></i> Ir a Contabilidad
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection