<!-- resources/views/operator/dashboard.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Bienvenido al Panel de Control del Operador</h1>
    <p>Este es el dashboard del operador. Aquí se listan los mismos módulos que el administrador.</p>

    <!-- Módulo de Ingresos -->
    <h2>Módulo de Ingresos</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad</th>
                    <th>Tipo de Ingreso</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Lote</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ingresos as $ingreso)
                <tr>
                    <td>{{ $ingreso->codigo_producto }}</td>
                    <td>{{ $ingreso->nombre_producto }}</td>
                    <td>{{ $ingreso->cantidad }}</td>
                    <td>{{ ucfirst($ingreso->tipo_ingreso) }}</td>
                    <td>{{ $ingreso->fecha_vencimiento }}</td>
                    <td>{{ $ingreso->lote }}</td>
                    <td>
                        <!-- Botón para ver detalles, siempre permitido -->
                        <a href="{{ route('ingresos.show', $ingreso->id) }}" class="btn btn-info btn-sm">Ver</a>

                        <!-- Botón para editar, restringido si el rol es operador -->
                        @if (auth()->user()->rol != 'operador')
                            <a href="{{ route('ingresos.edit', $ingreso->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        @endif

                        <!-- Botón para eliminar, restringido si el rol es operador -->
                        @if (auth()->user()->rol != 'operador')
                            <form action="{{ route('ingresos.destroy', $ingreso->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este ingreso?');">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

