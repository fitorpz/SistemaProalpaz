@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="text-left text-secondary fw-bold">
        <i class="fas fa-chart-line me-2"></i>Panel de Gestión de Ventas
    </h4>

    <div class="row mt-4">
        <!-- Registrar Clientes -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-plus me-2"></i>Registrar Clientes
                </div>
                <div class="card-body">
                    <p>Registra nuevos clientes en el sistema.</p>
                    <a href="{{ route('clientes.index') }}" class="btn btn-primary">Ir a Clientes</a>
                </div>
            </div>
        </div>

        <!-- Realizar Preventas -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-shopping-cart me-2"></i>Realizar Preventas
                </div>
                <div class="card-body">
                    <p>Gestiona y registra preventas.</p>
                    <a href="{{ route('preventas.index') }}" class="btn btn-success">Ir a Preventas</a>
                </div>
            </div>
        </div>

        <!-- Registrar Rutas -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-route me-2"></i>Rutas Programadas
                </div>
                <div class="card-body">
                    <p>Consulta las rutas programadas.</p>
                    <a href="{{ route('rutas.index') }}" class="btn btn-info">Ver Rutas</a>
                </div>
            </div>
        </div>

        @auth
        @if(auth()->user()->rol === 'usuario_operador' || auth()->user()->rol === 'administrador')
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-warning text-white">
                    <i class="fas fa-dolly me-2"></i> Picking & Packing
                </div>
                <div class="card-body">
                    <p>Prepara los productos de preventas según la fecha de entrega.</p>
                    <a href="{{ route('picking.index') }}" class="btn btn-warning">Gestionar Picking & Packing</a>
                </div>
            </div>
        </div>
        @endif
        @endauth



        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-box-open me-2"></i>Productos Asignados
                </div>
                <div class="card-body">
                    <p>Consulta los productos que te han sido asignados.</p>
                    <a href="{{ route('ingresos.asignados') }}" class="btn btn-success">Ver Productos</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection