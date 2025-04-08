@extends('layouts.app')

@section('content')

<style>
    .card-opcion {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s;
    }


    .card-opcion h5 {
        font-weight: bold;
        margin-top: 10px;
    }

    .btn-opcion {
        width: 100%;
        font-weight: bold;
    }
</style>

<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-hand-holding-usd me-2"></i>Panel de Gestión de Cobranzas
    </h4>

    <div class="row justify-content-center g-4">
        <!-- Registrar Cuentas por Cobrar -->
        <div class="col-md-4">
            <div class="card text-center card-opcion">
                <div class="card-body">
                    <i class="fas fa-file-invoice-dollar fa-3x text-primary"></i>
                    <h5 class="mt-2">Registrar Cuentas por Cobrar</h5>
                    <p>Registra manualmente cuentas por cobrar de clientes.</p>
                    <a href="{{ route('contabilidad.cobranzas.registrarCredito') }}" class="btn btn-primary btn-opcion">
                        Ir a Registro
                    </a>
                </div>
            </div>
        </div>

        <!-- Registrar Abono -->
        <div class="col-md-4">
            <div class="card text-center card-opcion">
                <div class="card-body">
                    <i class="fas fa-hand-holding-usd fa-3x text-success"></i>
                    <h5 class="mt-2">Registrar Abono</h5>
                    <p>Ingresa pagos realizados por clientes a sus créditos.</p>
                    <a href="{{ route('contabilidad.cobranzas.registrarAbono') }}" class="btn btn-success btn-opcion">
                        Registrar Abono
                    </a>
                </div>
            </div>
        </div>

        <!-- Ver Estado de Cuentas -->
        <div class="col-md-4">
            <div class="card text-center card-opcion">
                <div class="card-body">
                    <i class="fas fa-chart-line fa-3x text-warning"></i>
                    <h5 class="mt-2">Estado de Cuentas</h5>
                    <p>Consulta el estado de cuentas por cobrar de los clientes.</p>
                    <a href="{{ route('contabilidad.cobranzas.estadoCuentas') }}" class="btn btn-warning btn-opcion text-white">
                        Ver Estado
                    </a>
                </div>
            </div>
        </div>

        <!-- Ver Cuentas por Cobrar Externas -->
        <div class="col-md-4">
            <div class="card text-center card-opcion">
                <div class="card-body">
                    <i class="fas fa-user-secret fa-3x text-dark"></i>
                    <h5 class="mt-2">Cuentas por Cobrar Externas</h5>
                    <p>Consulta deudas externas no asociadas a clientes del sistema.</p>
                    <a href="{{ route('contabilidad.cobranzas.externas.index') }}" class="btn btn-dark btn-opcion">
                        Ver Externas
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection