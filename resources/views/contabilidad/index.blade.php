@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-file-invoice-dollar me-2"></i>Módulo de Contabilidad
    </h4>

    <div class="row mt-4">
        <!-- Cuentas por Cobrar -->
        <div class="col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Cuentas por Cobrar
                </div>
                <div class="card-body">
                    <p>Consulta los créditos activos de los clientes.</p>
                    <a href="{{ route('contabilidad.cobranzas.index') }}" class="btn btn-danger">Ver Cuentas por Cobrar</a>
                </div>
            </div>
        </div>
        <!-- Submódulo: Registrar Cuenta por Cobrar Externa -->
        <div class="col-md-6 col-lg-4 mb-4 d-flex">
            <div class="card text-center shadow-sm flex-fill">
                <div class="card-header bg-warning text-white">
                    <i class="fas fa-user-secret me-2"></i> Cuenta por Cobrar Externa
                </div>
                <div class="card-body">
                    <p>Registrar una cuenta por cobrar no vinculada a clientes del sistema.</p>
                    <a href="{{ route('contabilidad.cobranzas.crearExterna') }}" class="btn btn-warning text-white">
                        <i class="fas fa-plus"></i> Registrar
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection