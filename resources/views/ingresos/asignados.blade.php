@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="text-secondary fw-semibold mb-3">
        <i class="fas fa-warehouse me-2 text-primary"></i>Productos Asignados
    </h4>

    @if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if($ingresos->isEmpty())
    <div class="alert alert-warning">No tienes productos asignados a tus almacenes.</div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ingresos->groupBy('codigo_producto') as $codigoProducto => $productos)
                <!-- Fila principal -->
                <tr class="table-primary" data-bs-toggle="collapse" data-bs-target="#details-{{ $codigoProducto }}" aria-expanded="false" aria-controls="details-{{ $codigoProducto }}">
                    <td>{{ $codigoProducto }}</td>
                    <td>{{ $productos->first()->nombre_producto }}</td>
                    <td>{{ $productos->sum('cantidad') }}</td>
                    <td>
                        <button class="btn btn-info btn-sm">Ver Detalles</button>
                    </td>
                </tr>
                <!-- Fila desplegable -->
                <tr class="collapse" id="details-{{ $codigoProducto }}">
                    <td colspan="4">
                        <table class="table table-sm table-bordered" style="font-size: 16px; text-align: center;">
                            <thead>
                                <tr>
                                    <th>Lote</th>
                                    <th>Fecha de Vencimiento</th>
                                    <th>Cantidad</th>
                                    <th>Almacén</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($productos as $producto)
                                <tr>
                                    <td>{{ $producto->lote }}</td>
                                    <td>{{ $producto->fecha_vencimiento ?? 'Sin Fecha' }}</td>
                                    <td>{{ $producto->cantidad }}</td>
                                    <td>{{ $producto->almacen->nombre ?? 'No especificado' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection