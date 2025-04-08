@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Detalles de la Ruta</h3>
    <p><strong>Cliente:</strong> {{ $ruta->cliente->nombre_comercio ?? 'N/A' }}</p>
    <p><strong>Preventista:</strong> {{ $ruta->preventista->nombre ?? 'N/A' }}</p>
    <p>
        <strong>Ultima Fecha de Visita:</strong>
        @if($ruta->visita)
        {{ \Carbon\Carbon::parse($ruta->visita->created_at)->format('d-m-Y') }}
        @else
        {{ \Carbon\Carbon::parse($ruta->fecha_visita)->format('d-m-Y') }}
        @endif
    </p>


    <h3 class="mt-4">Historial de Visitas</h3>
    @if($visitas->isEmpty())
    <div class="alert alert-warning">No se encontraron visitas registradas.</div>
    @else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Fecha de Registro</th>
                <th>Ubicación</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($visitas as $visita)
            <tr>
                <td>{{ \Carbon\Carbon::parse($visita->created_at)->format('d-m-Y H:i') }}</td>
                <td><a href="{{ $visita->ubicacion }}" target="_blank">Ver Ubicación</a></td>
                <td>{{ $visita->observaciones ?? 'Sin observaciones' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection