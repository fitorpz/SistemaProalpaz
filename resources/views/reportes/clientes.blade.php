@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Reporte de Clientes</h2>
    <form method="GET" action="{{ route('reportes.clientes') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="nombre" class="form-control" placeholder="Buscar por Nombre..." value="{{ request('nombre') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="codigo_cliente" class="form-control" placeholder="Código de Cliente..." value="{{ request('codigo_cliente') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="nit" class="form-control" placeholder="NIT..." value="{{ request('nit') }}">
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
                <th>Comercio</th>
                <th>NIT</th>
                <th>Teléfono</th>
                <th>Dirección</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td>{{ $cliente->id }}</td>
                <td>{{ $cliente->codigo_cliente }}</td>
                <td>{{ $cliente->nombre_propietario }}</td>
                <td>{{ $cliente->nombre_comercio }}</td>
                <td>{{ $cliente->nit ?? 'N/A' }}</td>
                <td>{{ $cliente->telefono ?? 'N/A' }}</td>
                <td>{{ $cliente->direccion ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('reportes.clientes.pdf', request()->all()) }}" class="btn btn-danger">Generar PDF</a>
</div>
@endsection
