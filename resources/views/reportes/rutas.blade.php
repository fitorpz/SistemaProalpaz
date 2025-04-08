@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Reporte de Rutas</h2>

    <form method="GET" action="{{ route('reportes.rutas') }}" class="mb-4">
        <div class="row">
            <!-- Filtro por Fecha -->
            <div class="col-md-4">
                <input type="date" name="fecha_visita" class="form-control" value="{{ request('fecha_visita') }}">
            </div>

            <!-- Filtro por Preventista -->
            <div class="col-md-4">
                <select name="preventista" class="form-control">
                    <option value="">-- Seleccionar Preventista --</option>
                    @foreach($preventistas as $preventista)
                        <option value="{{ $preventista->id }}" {{ request('preventista') == $preventista->id ? 'selected' : '' }}>
                            {{ $preventista->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Botón de Filtrar -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Mostrar nombre del preventista si se ha seleccionado uno -->
    @if(request('preventista') && $rutas->isNotEmpty())
        <div class="alert alert-info text-center">
            <strong>Preventista:</strong> 
            {{ $preventistas->firstWhere('id', request('preventista'))->nombre ?? 'No especificado' }}
        </div>
    @endif

    <!-- Tabla de Rutas -->
    @if($rutas->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha Visita</th>
                        <th>Observación / Pedido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rutas as $ruta)
                    <tr>
                        <td>{{ $ruta->cliente->nombre_comercio ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($ruta->fecha_visita)->format('d/m/Y') }}</td>
                        <td>
                            @if($ruta->visita)
                                {{ $ruta->visita->observaciones ?? 'Sin observaciones' }}
                            @else
                                <span class="text-muted">No hay visita registrada</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Botón de Generar PDF -->
        <a href="{{ route('reportes.rutas.pdf', request()->all()) }}" class="btn btn-danger">Generar PDF</a>
    @else
        <div class="alert alert-warning text-center">No hay registros para la búsqueda seleccionada.</div>
    @endif
</div>
@endsection
