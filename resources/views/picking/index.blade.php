@extends('layouts.app')

@section('content')

<style>
    .custom-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .custom-list li {
        padding: 5px 0;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
    }

    /* 📌 Contenedor de la tabla con scroll */
    .table-responsive {
        max-height: 500px;
        /* Ajusta según necesidad */
        overflow-y: auto;
        border: 1px solid #ddd;
    }

    /* 📌 Fijar encabezado de la tabla */
    .table thead {
        position: sticky;
        top: 0;
        background: #343a40;
        /* Color del encabezado */
        color: white;
        z-index: 100;
    }

    /* 📌 Espaciado adecuado en encabezados */
    .table thead th {
        padding: 12px;
        text-align: center;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
        /* Evita que los títulos se dividan */
    }

    /* 📌 Mejor apariencia para la tabla */
    .table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
    }

    /* 📌 Filas alternas con fondo distinto */
    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    /* 📌 Resaltar fila al pasar el mouse */
    .table tbody tr:hover {
        background-color: #e9ecef;
    }

    /* 📌 Alinear contenido de celdas */
    .table td,
    .table th {
        padding: 10px;
        text-align: center;
        vertical-align: middle;
    }

    /* 📌 Estilo de botones en la tabla */
    .btn-sm {
        padding: 5px 10px;
        font-size: 14px;
    }

    /* 📌 Badges para los estados */
    .badge {
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 8px;
    }

    /* 📌 Estilos para la lista de productos dentro de la tabla */
    .custom-list {
        padding-left: 0;
        list-style-type: none;
        font-size: 14px;
    }

    .custom-list li {
        padding: 2px 0;
    }

    /* 📌 Responsive: Ajustar para pantallas pequeñas */
    @media (max-width: 768px) {
        .table-responsive {
            max-height: 400px;
        }

        .table thead th {
            font-size: 14px;
            padding: 8px;
        }

        .table td {
            font-size: 12px;
            padding: 6px;
        }

        .btn-sm {
            font-size: 12px;
            padding: 4px 8px;
        }
    }

    /* 📌 Contenedor del formulario */
    .filtro-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        /* Espaciado entre elementos */
        background: #f8f9fa;
        /* Fondo ligero */
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    /* 📌 Etiquetas de los filtros */
    .filtro-label {
        font-weight: bold;
        color: #343a40;
    }

    /* 📌 Inputs y selects */
    .filtro-input,
    .filtro-select {
        flex: 1;
        /* Hace que los inputs ocupen el espacio disponible */
        min-width: 180px;
        max-width: 250px;
    }

    /* 📌 Botones de acción */
    .filtro-boton {
        white-space: nowrap;
        /* Evita que el texto se divida en dos líneas */
        font-weight: bold;
        padding: 8px 15px;
    }

    /* 📌 Ajustes para pantallas pequeñas */
    @media (max-width: 768px) {
        .filtro-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filtro-input,
        .filtro-select,
        .filtro-boton {
            width: 100%;
        }
    }
</style>

<div class="container mt-4">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-dolly me-2"></i>Picking & Packing
    </h4>


    <!-- 📌 Formulario de Filtros Mejorado -->
    <form method="GET" action="{{ route('picking.index') }}" class="filtro-form">
        <!-- Filtro por Fecha -->
        <label for="fecha" class="filtro-label">Fecha:</label>
        <input type="date" name="fecha" id="fecha" class="form-control filtro-input" value="{{ $fechaSeleccionada }}">

        <!-- Botón de Filtrar -->
        <button type="submit" class="btn btn-primary filtro-boton">
            <i class="fas fa-filter"></i> Filtrar
        </button>

        <!-- Botón de Generar PDF -->
        <a href="{{ route('picking.exportarPDF', ['fecha' => $fechaSeleccionada]) }}" class="btn btn-danger filtro-boton">
            <i class="fas fa-file-pdf"></i> Generar PDF
        </a>
    </form>

    <!-- Listado de preventas -->
    @if($preventas->isEmpty())
    <p class="text-center">No hay preventas para la fecha seleccionada.</p>
    @else
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th># Pedido</th>
                    <th>Cliente</th>
                    <th>Nombre Comercio</th>
                    <th>Dirección</th>
                    <th>Productos</th>
                    <th>Estado</th>
                    <th>Acción</th>
                    <th>Nota de Remisión</th>
                </tr>
            </thead>
            <tbody>
                @foreach($preventas as $preventa)
                <tr>
                    <td>{{ $preventa->numero_pedido }}</td>
                    <td>{{ $preventa->cliente ? ($preventa->cliente->nombre_propietario ?? $preventa->cliente->nombre_comercio) : 'Sin Cliente' }}</td>
                    <td>{{ $preventa->cliente ? $preventa->cliente->nombre_comercio : 'No disponible' }}</td>
                    <td>{{ $preventa->cliente ? $preventa->cliente->direccion : 'No disponible' }}</td>

                    <td>
                        @if($preventa->detalles->isEmpty())
                        <span class="text-muted">Sin Productos</span>
                        @else
                        <ul class="custom-list">
                            @foreach($preventa->detalles as $detalle)
                            <li>{{ $detalle->producto ? $detalle->producto->nombre_producto : 'Producto no encontrado' }} ({{ $detalle->cantidad }})</li>
                            @endforeach
                        </ul>
                        @endif
                    </td>

                    <!-- Estado -->
                    <td>
                        <span class="badge 
                            {{ $preventa->estado === 'Pendiente' ? 'bg-warning' : 
                            ($preventa->estado === 'Preparado' ? 'bg-info' : 
                            ($preventa->estado === 'Entregado' ? 'bg-success' : 'bg-danger')) }}">
                            {{ $preventa->estado }}
                        </span>

                    </td>

                    <!-- Acciones -->
                    <td>
                        @if($preventa->estado === 'Pendiente')
                        <form method="POST" action="{{ route('picking.preparar', $preventa->id) }}" class="mb-1">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm w-100">Marcar como Preparado</button>
                        </form>
                        @endif

                        @if($preventa->estado === 'Preparado')
                        <form method="POST" action="{{ route('picking.entregado', $preventa->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">Entregado</button>
                        </form>

                        <button class="btn btn-danger btn-sm w-100 mt-1" data-bs-toggle="modal" data-bs-target="#noEntregadoModal-{{ $preventa->id }}">
                            No Entregado
                        </button>

                        @endif
                        @if(in_array($preventa->estado, ['Pendiente', 'Preparado']))
                        <a href="{{ route('picking.editarPreventa', $preventa->id) }}" class="btn btn-primary btn-sm w-100 mt-1">
                            <i class="fas fa-edit"></i> Editar Preventa
                        </a>
                        @endif





                        @if($preventa->observacion_entrega)
                        <br>
                        <small class="text-muted"><strong>Obs: {{ $preventa->observacion_entrega }}</strong></small>
                        @endif
                    </td>

                    <!-- Nota de Remisión -->
                    <td>
                        @if (in_array(Auth::user()->rol, ['usuario_operador', 'administrador']))
                        <a href="{{ route('preventas.nota-remision', $preventa->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                            <i class="fas fa-file-alt"></i> Nota de Remisión
                        </a>
                        @endif
                    </td>
                </tr>

                <!-- Modal para ingresar observación de entrega -->
                <div class="modal fade" id="noEntregadoModal-{{ $preventa->id }}" tabindex="-1" aria-labelledby="noEntregadoModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Observación de No Entrega</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="{{ route('picking.noEntregado', $preventa->id) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="observacion_entrega" class="form-label">Observación:</label>
                                        <textarea class="form-control" name="observacion_entrega" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Marcar como No Entregado</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection