@extends('layouts.app')

@section('content')
<div class="container mt-5">
    @if(auth()->user() && (auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_operador'))
    <div class="row justify-content-center">
        <!-- Tarjeta para Ingresos -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center">
                    <div class="icon-container mb-3">
                        <i class="fas fa-arrow-circle-down fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title text-dark fw-bold">Ingresos</h5>
                    <p class="card-text text-muted">Gestiona los ingresos de nuevos productos al inventario.</p>
                    <a href="{{ route('ingresos.index') }}" class="btn btn-warning btn-lg btn-block">
                        <i class="fas fa-sign-in-alt"></i> Ir a Ingresos
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta para Bajas -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center">
                    <div class="icon-container mb-3">
                        <i class="fas fa-arrow-circle-up fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title text-dark fw-bold">Bajas</h5>
                    <p class="card-text text-muted">Gestiona las bajas de productos del inventario.</p>
                    <a href="{{ route('bajas.index') }}" class="btn btn-danger btn-lg btn-block">
                        <i class="fas fa-sign-out-alt"></i> Ir a Bajas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <p class="text-muted">Selecciona una de las opciones anteriores para comenzar a gestionar tu inventario.</p>
    </div>
    @else
    <div class="alert alert-danger text-center">
        <h4 class="alert-heading">Acceso Denegado</h4>
        <p>No tienes permisos para acceder a esta sección.</p>
    </div>
    @endif
</div>
@endsection