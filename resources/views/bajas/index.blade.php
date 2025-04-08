@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-arrow-circle-up me-2"></i>Bajas de Inventario
    </h4>

    <a href="{{ route('inventario.index') }}" class="btn btn-primary">Atrás</a>

    <!-- Formulario de búsqueda -->
    <form action="{{ route('bajas.buscar') }}" method="GET" class="d-flex mb-4">
        <input type="text" name="query" class="form-control" placeholder="Buscar por código, nombre o fecha">
        <button type="submit" class="btn btn-primary ms-2">Buscar</button>
    </form>

    <!-- Resultados de la búsqueda -->
    @if($resultados->isNotEmpty())
    <h4 class="mb-3">Resultados de Búsqueda</h4>
    <div class="table-responsive mb-5">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad</th>
                    <th>Almacén</th>
                    <th>Fecha Registro</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resultados as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->codigo_producto }}</td>
                    <td>{{ $producto->nombre_producto }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    <td>{{ $producto->almacen }}</td>
                    <td>{{ $producto->fecha_registro }}</td>
                    <td>
                        <!-- Botón de acción para registrar baja -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#bajaModal" data-id="{{ $producto->id }}" data-codigo="{{ $producto->codigo_producto }}" data-nombre="{{ $producto->nombre_producto }}" data-cantidad="{{ $producto->cantidad }}" data-almacen="{{ $producto->almacen }}">
                            Registrar Baja
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center mb-5">No se encontraron resultados de búsqueda.</div>
    @endif

    <!-- Tabla de bajas realizadas -->
    <h4 class="mb-3">Bajas Realizadas</h4>
    @if($bajas->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad</th>
                    <th>Almacén</th>
                    <th>Motivo</th>
                    <th>Fecha Baja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bajas as $baja)
                <tr>
                    <td>{{ $baja->id }}</td>
                    <td>{{ $baja->codigo_producto }}</td>
                    <td>{{ $baja->nombre_producto }}</td>
                    <td>{{ $baja->cantidad }}</td>
                    <td>{{ $baja->almacen }}</td>
                    <td>{{ $baja->motivo }}</td>
                    <td>{{ $baja->fecha_registro }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info text-center">No hay bajas registradas.</div>
    @endif
</div>

<!-- Modal para registrar bajas -->
<div class="modal fade" id="bajaModal" tabindex="-1" aria-labelledby="bajaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('bajas.registrar') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bajaModalLabel">Registrar Baja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="producto-id">
                    <div class="mb-3">
                        <label for="codigo-producto" class="form-label">Código Producto</label>
                        <input type="text" class="form-control" id="codigo-producto" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nombre-producto" class="form-label">Nombre Producto</label>
                        <input type="text" class="form-control" id="nombre-producto" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad a Dar de Baja</label>
                        <input type="number" class="form-control" name="cantidad" id="cantidad" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <textarea class="form-control" name="motivo" id="motivo" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="almacen" class="form-label">Almacén</label>
                        <input type="text" name="almacen" id="almacen" class="form-control" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Registrar Baja</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const bajaModal = document.getElementById('bajaModal');
        bajaModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const codigo = button.getAttribute('data-codigo');
            const nombre = button.getAttribute('data-nombre');
            const almacen = button.getAttribute('data-almacen');

            bajaModal.querySelector('#producto-id').value = id;
            bajaModal.querySelector('#codigo-producto').value = codigo;
            bajaModal.querySelector('#nombre-producto').value = nombre;
            bajaModal.querySelector('#almacen').value = almacen;
        });
    });
</script>

@endsection