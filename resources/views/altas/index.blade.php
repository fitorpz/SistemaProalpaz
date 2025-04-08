@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Gestión de Altas de Productos</h2>

    <div class="container d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('altas.create') }}" class="btn btn-success">Registrar Alta</a>
        <a href="{{ route('inventory.index') }}" class="btn btn-primary">Atras</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad</th>
                    <th>Fecha Vencimiento</th>
                    <th>Lote</th>
                    <th>Costo Ingreso (Bs.)</th>
                    <th>Precio Unidad Crédito (Bs.)</th>
                    <th>Precio Unidad Contado (Bs.)</th>
                    <th>Precio Caja Crédito (Bs.)</th>
                    <th>Precio Caja Contado (Bs.)</th>
                    <th>Precio Cajón Crédito (Bs.)</th>
                    <th>Precio Cajón Contado (Bs.)</th>
                    <th>Stock Crítico</th>
                    <th>Almacén Producto Terminado</th>
                    <th>Almacén Materia Prima</th>
                    <th>Almacén Cosméticos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($altas as $alta)
                    <tr>
                        <td>{{ $alta->codigo_producto }}</td>
                        <td>{{ $alta->nombre_producto }}</td>
                        <td>{{ $alta->cantidad }}</td>
                        <td>{{ $alta->fecha_vencimiento }}</td>
                        <td>{{ $alta->lote }}</td>
                        <td>{{ $alta->costo_ingreso }}</td>
                        <td>{{ $alta->precio_unidad_credito }}</td>
                        <td>{{ $alta->precio_unidad_contado }}</td>
                        <td>{{ $alta->precio_caja_credito }}</td>
                        <td>{{ $alta->precio_caja_contado }}</td>
                        <td>{{ $alta->precio_cajon_credito }}</td>
                        <td>{{ $alta->precio_cajon_contado }}</td>
                        <td>{{ $alta->stock_critico }}</td>
                        <td>{{ $alta->almacen_producto_terminado }}</td>
                        <td>{{ $alta->almacen_materia_prima }}</td>
                        <td>{{ $alta->almacen_cosmeticos }}</td>
                        <td>
                            <a href="{{ route('altas.edit', $alta->id) }}" class="btn btn-primary btn-sm">Editar</a>

                            <form action="{{ route('altas.destroy', $alta->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta alta?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
