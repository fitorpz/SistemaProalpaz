@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Reporte de Preventas</h2>
    <form method="GET" action="{{ route('reportes.preventas') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="numero_pedido" class="form-control" placeholder="Número de Pedido..." value="{{ request('numero_pedido') }}">
            </div>
            <div class="col-md-4">
                <input type="text" name="cliente" class="form-control" placeholder="Nombre del Cliente..." value="{{ request('cliente') }}">
            </div>
            <div class="col-md-3">
                <select name="estado" class="form-control">
                    <option value="">-- Estado --</option>
                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Preparado" {{ request('estado') == 'Preparado' ? 'selected' : '' }}>Preparado</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Cliente</th>
                <th>Fecha Entrega</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preventas as $preventa)
            <tr>
                <td>{{ $preventa->numero_pedido }}</td>
                <td>{{ $preventa->cliente->nombre_comercio ?? 'Sin Cliente' }}</td>
                <td>{{ \Carbon\Carbon::parse($preventa->fecha_entrega)->format('d/m/Y') }}</td>
                <td>{{ $preventa->estado }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('reportes.preventas.pdf', request()->all()) }}" class="btn btn-danger">Generar PDF</a>
</div>
@endsection
