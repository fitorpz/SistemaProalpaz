@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Gestión de Compras de Productos</h2>

    <div class="container d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('compras.create') }}" class="btn btn-success">Registrar Compra</a>
        <a href="{{ route('inventory.index') }}" class="btn btn-primary">Atras</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad</th>
                    <th>Fecha Vencimiento</th>
                    <th>Lote</th>
                    <th>Costo Compra (Bs.)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compras as $compra)
                <tr>
                    <td>{{ $compra->codigo_producto }}</td>
                    <td>{{ $compra->nombre_producto }}</td>
                    <td>{{ $compra->cantidad }}</td>
                    <td>{{ $compra->fecha_vencimiento }}</td>
                    <td>{{ $compra->lote }}</td>
                    <td>{{ $compra->costo_compra }}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editarCompraModal-{{ $compra->id }}">Editar</button>
                        <form action="{{ route('compras.destroy', $compra->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</button>
                        </form>
                    </td>
                </tr>

                <!-- Modal para editar compra -->
                <div class="modal fade" id="editarCompraModal-{{ $compra->id }}" tabindex="-1" aria-labelledby="editarCompraModalLabel-{{ $compra->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarCompraModalLabel-{{ $compra->id }}">Editar Compra</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-body">
                                    <!-- Código del Producto -->
                                    <div class="mb-3">
                                        <label for="codigo_producto-{{ $compra->id }}" class="form-label">Código del Producto</label>
                                        <input type="text" class="form-control" id="codigo_producto-{{ $compra->id }}" name="codigo_producto" value="{{ $compra->codigo_producto }}" required>
                                    </div>

                                    <!-- Nombre del Producto -->
                                    <div class="mb-3">
                                        <label for="nombre_producto-{{ $compra->id }}" class="form-label">Nombre del Producto</label>
                                        <input type="text" class="form-control" id="nombre_producto-{{ $compra->id }}" name="nombre_producto" value="{{ $compra->nombre_producto }}" required>
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="mb-3">
                                        <label for="cantidad-{{ $compra->id }}" class="form-label">Cantidad</label>
                                        <input type="number" class="form-control" id="cantidad-{{ $compra->id }}" name="cantidad" value="{{ $compra->cantidad }}" required>
                                    </div>

                                    <!-- Fecha de Vencimiento -->
                                    <div class="mb-3">
                                        <label for="fecha_vencimiento-{{ $compra->id }}" class="form-label">Fecha de Vencimiento</label>
                                        <input type="date" class="form-control" id="fecha_vencimiento-{{ $compra->id }}" name="fecha_vencimiento" value="{{ $compra->fecha_vencimiento }}" required>
                                    </div>

                                    <!-- Lote -->
                                    <div class="mb-3">
                                        <label for="lote-{{ $compra->id }}" class="form-label">Lote</label>
                                        <input type="text" class="form-control" id="lote-{{ $compra->id }}" name="lote" value="{{ $compra->lote }}" required>
                                    </div>

                                    <!-- Costo Compra -->
                                    <div class="mb-3">
                                        <label for="costo_compra-{{ $compra->id }}" class="form-label">Costo Compra (Bs.)</label>
                                        <input type="number" step="0.01" class="form-control" id="costo_compra-{{ $compra->id }}" name="costo_compra" value="{{ $compra->costo_compra }}" required>
                                    </div>

                                    <!-- Check de Factura -->
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="con_factura-{{ $compra->id }}" name="con_factura" {{ $compra->con_factura ? 'checked' : '' }}>
                                        <label class="form-check-label" for="con_factura-{{ $compra->id }}">¿Compra con Factura?</label>
                                    </div>

                                    <!-- Precios -->
                                    <div class="mb-3">
                                        <label for="precio_unidad_credito-{{ $compra->id }}" class="form-label">Precio Unidad Crédito (Bs.)</label>
                                        <input type="number" step="0.01" class="form-control" id="precio_unidad_credito-{{ $compra->id }}" name="precio_unidad_credito" value="{{ $compra->precio_unidad_credito }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="precio_unidad_contado-{{ $compra->id }}" class="form-label">Precio Unidad Contado (Bs.)</label>
                                        <input type="number" step="0.01" class="form-control" id="precio_unidad_contado-{{ $compra->id }}" name="precio_unidad_contado" value="{{ $compra->precio_unidad_contado }}" required>
                                    </div>

                                    <!-- Stock Crítico -->
                                    <div class="mb-3">
                                        <label for="stock_critico-{{ $compra->id }}" class="form-label">Stock Crítico</label>
                                        <input type="number" class="form-control" id="stock_critico-{{ $compra->id }}" name="stock_critico" value="{{ $compra->stock_critico }}" required>
                                    </div>

                                    <!-- Almacenes -->
                                    <div class="mb-3">
                                        <label for="almacen_producto_terminado-{{ $compra->id }}" class="form-label">Almacén Producto Terminado</label>
                                        <input type="number" class="form-control" id="almacen_producto_terminado-{{ $compra->id }}" name="almacen_producto_terminado" value="{{ $compra->almacen_producto_terminado }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="almacen_materia_prima-{{ $compra->id }}" class="form-label">Almacén Materia Prima</label>
                                        <input type="number" class="form-control" id="almacen_materia_prima-{{ $compra->id }}" name="almacen_materia_prima" value="{{ $compra->almacen_materia_prima }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="almacen_cosmeticos-{{ $compra->id }}" class="form-label">Almacén Cosméticos</label>
                                        <input type="number" class="form-control" id="almacen_cosmeticos-{{ $compra->id }}" name="almacen_cosmeticos" value="{{ $compra->almacen_cosmeticos }}" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection