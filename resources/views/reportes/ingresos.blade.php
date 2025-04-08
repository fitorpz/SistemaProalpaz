@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Reporte de Ingresos</h2>
    <form method="GET" action="{{ route('reportes.ingresos') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="nombre_producto" class="form-control" placeholder="Nombre del Producto..." value="{{ request('nombre_producto') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="codigo_producto" class="form-control" placeholder="Código del Producto..." value="{{ request('codigo_producto') }}">
            </div>
            <div class="col-md-3">
                <select name="tipo_ingreso" class="form-control">
                    <option value="">-- Tipo de Ingreso --</option>
                    <option value="compra" {{ request('tipo_ingreso') == 'compra' ? 'selected' : '' }}>Compra</option>
                    <option value="produccion" {{ request('tipo_ingreso') == 'produccion' ? 'selected' : '' }}>Producción</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Fecha Vencimiento</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ingresos as $ingreso)
            <tr>
                <td>{{ $ingreso->id }}</td>
                <td>{{ $ingreso->codigo_producto }}</td>
                <td>{{ $ingreso->nombre_producto }}</td>
                <td>{{ $ingreso->cantidad }}</td>
                <td>{{ $ingreso->fecha_vencimiento ?? 'N/A' }}</td>
                <td>{{ ucfirst($ingreso->tipo_ingreso) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('reportes.ingresos.pdf', request()->all()) }}" class="btn btn-danger">Generar PDF</a>
</div>
@endsection
