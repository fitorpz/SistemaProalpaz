@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Panel de Reportes</h2>
    <p class="text-center">Genera y descarga reportes en PDF de cada módulo.</p>

    <div class="row mt-4">
        <!-- Reporte de Clientes -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-warehouse me-2"></i> Reporte de Ingresos
                </div>
                <div class="card-body">
                    <p>Genera un reporte detallado de los Ingresos registrados.</p>
                    <a href="{{ route('reportes.ingresos') }}" class="btn btn-primary">Ir a Reporte</a>
                </div>
            </div>
        </div>
        <!-- Reporte de Preventas -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-users me-2"></i> Reporte de Clientes
                </div>
                <div class="card-body">
                    <p>Descarga un reporte de todas las Clientes registrados.</p>
                    <a href="{{ route('reportes.clientes') }}" class="btn btn-success">Ir a Reporte</a>
                </div>
            </div>
        </div>

        <!-- Reporte de Inventario -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-dolly me-2"></i> Reporte de Rutas
                </div>
                <div class="card-body">
                    <p>Consulta el estado actual de las Rutas</p>
                    <a href="{{ route('reportes.rutas') }}" class="btn btn-info">Ir a reporte</a>
                </div>
            </div>
        </div>

        <!-- Reporte de Picking & Packing -->
        <div class="col-md-6 col-lg-3 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-warning text-white">
                    <i class="fas fa-shopping-cart me-2"></i> Reporte de Preventas
                </div>
                <div class="card-body">
                    <p>Obtén un reporte de las Preventas</p>
                    <a href="{{ route('reportes.preventas') }}" class="btn btn-warning">Ir a reporte</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection