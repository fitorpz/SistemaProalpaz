<!-- resources/views/reportes/ventas/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3 class="text-center mb-4">Reporte de Ventas</h3>

    <div class="row justify-content-center">
        <!-- Reporte Detallado -->
        <div class="col-md-4">
            <div class="card border-left-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-receipt me-1"></i> Reporte Detallado de Ventas
                </div>
                <div class="card-body">
                    Consulta cada venta realizada con detalle de productos.
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('reportes.ventas.detallado') }}" class="btn btn-danger">Ir al Reporte</a>
                </div>
            </div>
        </div>

        <!-- Reporte General -->
        <div class="col-md-4">
            <div class="card border-left-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-bar me-1"></i> Reporte General de Ventas
                </div>
                <div class="card-body">
                    Muestra el resumen general de ventas agrupadas.
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('reportes.ventas.general') }}" class="btn btn-primary">Ir al Reporte</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection