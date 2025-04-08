@extends('layouts.app')

@section('content')
<style>
    .table-container {
        max-height: 500px;
        overflow-y: auto;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }
    .table-custom th {
        background-color: rgb(77, 83, 89);
        color: white;
        text-align: center;
        padding: 10px;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .table-custom td {
        text-align: center;
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }
    .table-custom tr:hover {
        background-color: #f1f1f1;
    }
    .btn-custom {
        padding: 4px 8px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-custom i {
        margin-right: 5px;
    }
</style>

<div class="container">
    <h2 class="text-center">Estado de cuenta externa - {{ $cuenta->nombre }}</h2>
    <h2 class="text-center">(En Bolivianos.)</h2>
    <p>Detalle del crédito registrado externamente:</p>

    <div class="table-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Concepto</th>
                    <th>Monto Total</th>
                    <th>Fecha Registro</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $cuenta->categoria ?? '-' }}</td>
                    <td>{{ $cuenta->concepto }}</td>
                    <td>{{ number_format($cuenta->monto_total, 2) }} Bs</td>
                    <td>{{ \Carbon\Carbon::parse($cuenta->fecha_registro)->format('d/m/Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($cuenta->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge bg-{{ $cuenta->estado == 'Activo' ? 'success' : 'secondary' }}">
                            {{ $cuenta->estado }}
                        </span>
                    </td>
                    <td>{{ $cuenta->observaciones ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('contabilidad.cobranzas.externas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
        <a href="{{ route('contabilidad.cobranzas.externas.historialPDF', $cuenta->id) }}" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Exportar PDF
        </a>
    </div>
</div>
@endsection
