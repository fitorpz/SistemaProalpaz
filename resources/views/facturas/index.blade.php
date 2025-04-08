@extends('layouts.app')
@include('layouts.header')

@section('content')
<div class="container">
    <h1>Facturas Registradas</h1>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Botón para abrir el modal de agregar factura -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createFacturaModal">
        Agregar Nueva Factura
    </button>

    <!-- Tabla para listar facturas -->
    <table class="table">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>NIT</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facturas as $factura)
            <tr>
                <td>{{ $factura->nombre_proveedor }}</td>
                <td>{{ $factura->nit_proveedor }}</td>
                <td>{{ $factura->total }}</td>
                <td>
                    <a href="{{ route('facturas.show', $factura->id) }}" class="btn btn-info">Ver Detalles</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal para agregar una nueva factura -->
<div class="modal fade" id="createFacturaModal" tabindex="-1" aria-labelledby="createFacturaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createFacturaModalLabel">Agregar Nueva Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('facturas.store') }}" method="POST" id="createFacturaForm">
                    @csrf
                    <div class="mb-3">
                        <label for="nombre_proveedor" class="form-label">Nombre del Proveedor</label>
                        <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor" required>
                    </div>

                    <div class="mb-3">
                        <label for="nit_proveedor" class="form-label">NIT del Proveedor</label>
                        <input type="text" class="form-control" id="nit_proveedor" name="nit_proveedor" required>
                    </div>

                    <!-- Productos -->
                    <div id="productos-container">
                        <h4>Productos</h4>
                        <div class="producto mb-3">
                            <input type="text" class="form-control mb-2" name="productos[0][nombre]" placeholder="Nombre del Producto" required>
                            <input type="number" class="form-control mb-2" name="productos[0][cantidad]" placeholder="Cantidad" required>
                            <input type="number" class="form-control mb-2" name="productos[0][precio_compra]" placeholder="Precio Compra" step="0.01" required>
                        </div>
                    </div>

                    <button type="button" id="add-producto" class="btn btn-secondary mb-3">Agregar Producto</button>

                    <button type="submit" class="btn btn-primary">Registrar Factura</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para agregar más productos dinámicamente -->
<script>
    let productoIndex = 1;
    document.getElementById('add-producto').addEventListener('click', function() {
        const container = document.getElementById('productos-container');
        const newProducto = `
            <div class="producto mb-3">
                <input type="text" class="form-control mb-2" name="productos[${productoIndex}][nombre]" placeholder="Nombre del Producto" required>
                <input type="number" class="form-control mb-2" name="productos[${productoIndex}][cantidad]" placeholder="Cantidad" required>
                <input type="number" class="form-control mb-2" name="productos[${productoIndex}][precio_compra]" placeholder="Precio Compra" step="0.01" required>
            </div>`;
        container.insertAdjacentHTML('beforeend', newProducto);
        productoIndex++;
    });
</script>
@endsection
