@extends('layouts.app')

@section('content')

@if($errors->has('codigo_fecha_error'))
<div class="alert alert-danger">
    <strong>{{ $errors->first('codigo_fecha_error') }}</strong>
</div>
@endif

@if($errors->has('lote_error'))
<div class="alert alert-danger">
    <strong>{{ $errors->first('lote_error') }}</strong>
</div>
@endif
<style>
    /* Transición para un despliegue suave */
    .collapse {
        transition: max-height 0.3s ease-in-out;
        overflow: hidden;
    }

    .collapse:not(.show) {
        max-height: 0;
    }

    .collapse.show {
        max-height: 1000px;
        /* Ajusta según el contenido */
    }

    /* Estilo para las filas principales */
    .table-primary {
        background-color: #f2f8fc !important;
        cursor: pointer;
    }

    .btn-sm {
        font-size: 12px;
        padding: 2px 8px;
    }

    /* Centrar texto */
    .table td,
    .table th {
        vertical-align: middle;
        text-align: center;
    }
</style>
<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-arrow-circle-down me-2"></i>Ingresos
    </h4>

    <div class="container d-flex justify-content-between align-items-center mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ingresoModal">
            Registrar Ingreso
        </button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#almacenModal">
            Agregar Almacén
        </button>
        <a href="{{ route('inventario.index') }}" class="btn btn-primary">Atrás</a>
    </div>

    <!-- Modal para agregar almacén -->
    <div class="modal fade" id="almacenModal" tabindex="-1" aria-labelledby="almacenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="almacenModalLabel">Agregar Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('almacenes.store') }}" method="POST" id="almacenForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nombre_almacen">Nombre del Almacén</label>
                            <input type="text" class="form-control" id="nombre_almacen" name="nombre" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Almacén</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="ingresoModal" tabindex="-1" aria-labelledby="ingresoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ingresoModalLabel">Nuevo Ingreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('ingresos.store') }}" method="POST" id="ingresoForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Código Producto -->
                                <div class="form-group">
                                    <label for="codigo_producto">Código Producto</label>
                                    <input type="text" class="form-control" id="codigo_producto" name="codigo_producto" required>
                                </div>

                                <!-- Nombre Producto -->
                                <div class="form-group">
                                    <label for="nombre_producto">Nombre Producto</label>
                                    <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
                                </div>

                                <!-- Cantidad -->
                                <div class="form-group">
                                    <label for="cantidad">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                                </div>

                                <!-- Fecha de Vencimiento -->
                                <div class="form-group">
                                    <label for="fecha_vencimiento">Fecha de Vencimiento</label>
                                    <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                                </div>

                                <!-- Lote -->
                                <div class="form-group">
                                    <label for="lote">Lote</label>
                                    <input type="text" class="form-control" id="lote" name="lote" required>
                                </div>

                                <!-- Costo Producción/Compra (Decimal) -->
                                <div class="form-group">
                                    <label for="costo_produccion_compra">Costo Producción/Compra</label>
                                    <input type="number" step="0.01" class="form-control" id="costo_produccion_compra" name="costo_produccion_compra" required>
                                </div>
                                <!-- Almacén -->
                                <div class="form-group">
                                    <label for="almacen_id">Seleccionar Almacén</label>
                                    <select class="form-control" id="almacen_id" name="almacen_id" required>
                                        @if ($almacenes->isEmpty())
                                        <option value="" disabled selected>No tienes almacenes asignados</option>
                                        @else
                                        <option value="" disabled selected>Seleccione un almacén</option>
                                        @foreach ($almacenes as $almacen)
                                        <option value="{{ $almacen->id }}">{{ $almacen->nombre }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Compra con Factura -->
                                <div class="form-group">
                                    <label for="compra_con_factura">Compra con factura</label>
                                    <select class="form-control" id="compra_con_factura" name="compra_con_factura" required>
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Tipo de Ingreso -->
                                <div class="form-group">
                                    <label for="tipo_ingreso">Tipo de Ingreso</label>
                                    <select name="tipo_ingreso" id="tipo_ingreso" class="form-control" required>
                                        <option value="compra">Compra</option>
                                        <option value="produccion">Producción</option>
                                    </select>
                                </div>
                                <!-- Precio Unidad Crédito -->
                                <div class="form-group">
                                    <label for="precio_unidad_credito">Precio Unidad Crédito</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_unidad_credito" name="precio_unidad_credito" required>
                                </div>

                                <!-- Precio Unidad Contado -->
                                <div class="form-group">
                                    <label for="precio_unidad_contado">Precio Unidad Contado</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_unidad_contado" name="precio_unidad_contado" required>
                                </div>

                                <!-- Precio Caja Crédito -->
                                <div class="form-group">
                                    <label for="precio_caja_credito">Precio Caja Crédito</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_caja_credito" name="precio_caja_credito" required>
                                </div>

                                <!-- Precio Caja Contado -->
                                <div class="form-group">
                                    <label for="precio_caja_contado">Precio Caja Contado</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_caja_contado" name="precio_caja_contado" required>
                                </div>

                                <!-- Precio Cajon Crédito -->
                                <div class="form-group">
                                    <label for="precio_cajon_credito">Precio Cajon Crédito</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_cajon_credito" name="precio_cajon_credito" required>
                                </div>

                                <!-- Precio Cajon Contado -->
                                <div class="form-group">
                                    <label for="precio_cajon_contado">Precio Cajon Contado</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_cajon_contado" name="precio_cajon_contado" required>
                                </div>

                                <!-- Precio Promoción -->
                                <div class="form-group">
                                    <label for="precio_promocion">Precio Promoción</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_promocion" name="precio_promocion" required>
                                </div>


                                <!-- Stock Crítico -->
                                <div class="form-group">
                                    <label for="stock_critico">Stock Crítico</label>
                                    <input type="number" class="form-control" id="stock_critico" name="stock_critico" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Ingreso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Tabla de almacenes -->
    <div class="container">
        <div class="row">
            <!-- Columna izquierda: Tabla de Almacenes -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Almacenes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nro.</th>
                                        <th>Nombre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($almacenes as $almacen)
                                    <tr onclick="filtrarPorAlmacen('{{ $almacen->id }}')" style="cursor: pointer;">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $almacen->nombre }}</td>
                                        <td>
                                            <!-- Mostrar botón eliminar solo si el usuario es administrador -->
                                            @if(auth()->user()->rol !== 'usuario_operador')
                                            <form action="{{ route('almacenes.destroy', $almacen->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este almacén?');">
                                                    Eliminar
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No hay almacenes registrados.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Tabla de Ingresos -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Lista de Ingresos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="font-size: 12px;">
                                <thead class="table-success">
                                    <tr>
                                        <th>Código Producto</th>
                                        <th>Nombre Producto</th>
                                        <th>Cantidad Total</th>
                                        <th>Costo Producción/Compra</th>
                                        <th>Total</th>
                                        <th>Precio Unidad Crédito</th>
                                        <th>Precio Unidad Contado</th>
                                        <th>Precio Caja Crédito</th>
                                        <th>Precio Caja Contado</th>
                                        <th>Precio Cajón Crédito</th>
                                        <th>Precio Cajón Contado</th>
                                        <th>Precio Promoción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ingresos->groupBy('codigo_producto') as $codigoProducto => $productos)
                                    @php
                                    $cantidadTotal = $productos->sum('cantidad');
                                    $stockCritico = $productos->first()->stock_critico ?? 0;
                                    $resaltarFila = $cantidadTotal <= $stockCritico ? 'table-danger' : '' ;
                                        @endphp
                                        <tr class="table-primary {{ $resaltarFila }}" data-bs-toggle="collapse" data-bs-target="#details-{{ $codigoProducto }}" aria-expanded="false" aria-controls="details-{{ $codigoProducto }}">
                                        <td>{{ $codigoProducto }}</td>
                                        <td>{{ $productos->first()->nombre_producto }}</td>
                                        <td class="{{ $cantidadTotal <= $stockCritico ? 'bg-danger text-white' : '' }}">{{ $cantidadTotal }}</td>
                                        <td>{{ number_format($productos->first()->costo_produccion_compra, 2) }}</td>
                                        <td>{{ number_format($productos->sum(fn($p) => $p->cantidad * $p->costo_produccion_compra), 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_unidad_credito, 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_unidad_contado, 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_caja_credito, 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_caja_contado, 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_cajon_credito, 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_cajon_contado, 2) }}</td>
                                        <td>{{ number_format($productos->first()->precio_promocion, 2) }}</td>
                                        <td>
                                            <button class="btn btn-info btn-sm">Ver Detalles</button>
                                        </td>
                                        </tr>
                                        <tr class="collapse" id="details-{{ $codigoProducto }}">
                                            <td colspan="12">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr class="table-light">
                                                            <th>Lote</th>
                                                            <th>Fecha de Vencimiento</th>
                                                            <th>Cantidad</th>
                                                            @if(auth()->user()->rol === 'administrador')
                                                            <th>Cantidad Inicial</th>
                                                            @endif
                                                            <th>Almacén</th>
                                                            <th>Fecha de Registro</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($productos as $producto)
                                                        @php
                                                        $resaltarDetalle = $producto->cantidad <= $producto->stock_critico ? 'bg-danger text-white' : '';
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $producto->lote }}</td>
                                                                <td>{{ $producto->fecha_vencimiento ?? 'Sin Fecha' }}</td>
                                                                <td class="{{ $resaltarDetalle }}">{{ $producto->cantidad }}</td>
                                                                @if(auth()->user()->rol === 'administrador')
                                                                <td>{{ $producto->cantidad_inicial }}</td>
                                                                @endif

                                                                <td>{{ $producto->almacen->nombre ?? 'No especificado' }}</td>
                                                                <td>{{ $producto->created_at->format('d-m-Y') }}</td>
                                                                <td>
                                                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editIngresoModal{{ $producto->id }}">
                                                                        Editar
                                                                    </button>
                                                                    <form action="{{ route('ingresos.destroy', $producto->id) }}" method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar ingreso?');">
                                                                            Eliminar
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                            <!-- Modal de Edición -->
                                                            <div class="modal fade" id="editIngresoModal{{ $producto->id }}" tabindex="-1" aria-labelledby="editIngresoModalLabel{{ $producto->id }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="editIngresoModalLabel{{ $producto->id }}">Editar Ingreso</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <form action="{{ route('ingresos.update', $producto->id) }}" method="POST">
                                                                            @csrf
                                                                            @method('PUT')
                                                                            <div class="modal-body">
                                                                                <div class="row">
                                                                                    <div class="col-md-6">
                                                                                        <!-- Código Producto -->
                                                                                        <div class="form-group">
                                                                                            <label for="codigo_producto_{{ $producto->id }}">Código Producto</label>
                                                                                            <input type="text" class="form-control" id="codigo_producto_{{ $producto->id }}" name="codigo_producto" value="{{ $producto->codigo_producto }}" required>
                                                                                        </div>

                                                                                        <!-- Nombre Producto -->
                                                                                        <div class="form-group">
                                                                                            <label for="nombre_producto_{{ $producto->id }}">Nombre Producto</label>
                                                                                            <input type="text" class="form-control" id="nombre_producto_{{ $producto->id }}" name="nombre_producto" value="{{ $producto->nombre_producto }}" required>
                                                                                        </div>

                                                                                        <!-- Cantidad -->
                                                                                        <div class="form-group">
                                                                                            <label for="cantidad_{{ $producto->id }}">Cantidad</label>
                                                                                            <input type="number" class="form-control" id="cantidad_{{ $producto->id }}" name="cantidad" value="{{ $producto->cantidad }}" required>
                                                                                        </div>

                                                                                        <!-- Fecha de Vencimiento -->
                                                                                        <div class="form-group">
                                                                                            <label for="fecha_vencimiento_{{ $producto->id }}">Fecha de Vencimiento</label>
                                                                                            <input type="date" class="form-control" id="fecha_vencimiento_{{ $producto->id }}" name="fecha_vencimiento" value="{{ $producto->fecha_vencimiento }}">
                                                                                        </div>

                                                                                        <!-- Lote -->
                                                                                        <div class="form-group">
                                                                                            <label for="lote_{{ $producto->id }}">Lote</label>
                                                                                            <input type="text" class="form-control" id="lote_{{ $producto->id }}" name="lote" value="{{ $producto->lote }}" required>
                                                                                        </div>

                                                                                        <!-- Costo Producción/Compra -->
                                                                                        <div class="form-group">
                                                                                            <label for="costo_produccion_compra_{{ $producto->id }}">Costo Producción/Compra</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="costo_produccion_compra_{{ $producto->id }}" name="costo_produccion_compra" value="{{ $producto->costo_produccion_compra }}" required>
                                                                                        </div>

                                                                                        <!-- Seleccionar Almacén -->
                                                                                        <div class="form-group">
                                                                                            <label for="almacen_id_{{ $producto->id }}">Seleccionar Almacén</label>
                                                                                            <select class="form-control" id="almacen_id_{{ $producto->id }}" name="almacen_id" required>
                                                                                                @if ($almacenes->isEmpty())
                                                                                                <option value="" disabled>No tienes almacenes asignados</option>
                                                                                                @else
                                                                                                @foreach ($almacenes as $almacen)
                                                                                                <option value="{{ $almacen->id }}" {{ $almacen->id == $producto->almacen_id ? 'selected' : '' }}>
                                                                                                    {{ $almacen->nombre }}
                                                                                                </option>
                                                                                                @endforeach
                                                                                                @endif
                                                                                            </select>
                                                                                        </div>


                                                                                        <!-- Compra con Factura -->
                                                                                        <div class="form-group">
                                                                                            <label for="compra_con_factura_{{ $producto->id }}">Compra con Factura</label>
                                                                                            <select class="form-control" id="compra_con_factura_{{ $producto->id }}" name="compra_con_factura" required>
                                                                                                <option value="0" {{ $producto->compra_con_factura == 0 ? 'selected' : '' }}>No</option>
                                                                                                <option value="1" {{ $producto->compra_con_factura == 1 ? 'selected' : '' }}>Sí</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="col-md-6">
                                                                                        <!-- Tipo de Ingreso -->
                                                                                        <div class="form-group">
                                                                                            <label for="tipo_ingreso_{{ $producto->id }}">Tipo de Ingreso</label>
                                                                                            <select class="form-control" id="tipo_ingreso_{{ $producto->id }}" name="tipo_ingreso" required>
                                                                                                <option value="compra" {{ $producto->tipo_ingreso == 'compra' ? 'selected' : '' }}>Compra</option>
                                                                                                <option value="produccion" {{ $producto->tipo_ingreso == 'produccion' ? 'selected' : '' }}>Producción</option>
                                                                                            </select>
                                                                                        </div>

                                                                                        <!-- Precio Unidad Crédito -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_unidad_credito_{{ $producto->id }}">Precio Unidad Crédito</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_unidad_credito_{{ $producto->id }}" name="precio_unidad_credito" value="{{ $producto->precio_unidad_credito }}" required>
                                                                                        </div>

                                                                                        <!-- Precio Unidad Contado -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_unidad_contado_{{ $producto->id }}">Precio Unidad Contado</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_unidad_contado_{{ $producto->id }}" name="precio_unidad_contado" value="{{ $producto->precio_unidad_contado }}" required>
                                                                                        </div>

                                                                                        <!-- Precio Caja Crédito -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_caja_credito_{{ $producto->id }}">Precio Caja Crédito</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_caja_credito_{{ $producto->id }}" name="precio_caja_credito" value="{{ $producto->precio_caja_credito }}" required>
                                                                                        </div>

                                                                                        <!-- Precio Caja Contado -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_caja_contado_{{ $producto->id }}">Precio Caja Contado</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_caja_contado_{{ $producto->id }}" name="precio_caja_contado" value="{{ $producto->precio_caja_contado }}" required>
                                                                                        </div>

                                                                                        <!-- Precio Cajon Crédito -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_cajon_credito_{{ $producto->id }}">Precio Cajon Crédito</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_cajon_credito_{{ $producto->id }}" name="precio_cajon_credito" value="{{ $producto->precio_cajon_credito }}" required>
                                                                                        </div>

                                                                                        <!-- Precio Cajon Contado -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_cajon_contado_{{ $producto->id }}">Precio Cajon Contado</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_cajon_contado_{{ $producto->id }}" name="precio_cajon_contado" value="{{ $producto->precio_cajon_contado }}" required>
                                                                                        </div>

                                                                                        <!-- Precio Promoción -->
                                                                                        <div class="form-group">
                                                                                            <label for="precio_promocion_{{ $producto->id }}">Precio Promoción</label>
                                                                                            <input type="number" step="0.01" class="form-control" id="precio_promocion_{{ $producto->id }}" name="precio_promocion" value="{{ $producto->precio_promocion }}">
                                                                                        </div>


                                                                                        <!-- Stock Crítico -->
                                                                                        <div class="form-group">
                                                                                            <label for="stock_critico_{{ $producto->id }}">Stock Crítico</label>
                                                                                            <input type="number" class="form-control" id="stock_critico_{{ $producto->id }}" name="stock_critico" value="{{ $producto->stock_critico }}" required>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ingresoModal = document.getElementById('ingresoModal');
        const ingresoForm = document.getElementById('ingresoForm');

        // Escuchar el evento cuando el modal se cierra
        ingresoModal.addEventListener('hidden.bs.modal', function() {
            ingresoForm.reset(); // Restablece todos los campos del formulario
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('ingresoForm'); // Formulario principal
        const loteInput = document.getElementById('lote');
        const cantidadInput = document.getElementById('cantidad'); // Input de cantidad
        const fechaVencimientoInput = document.getElementById('fecha_vencimiento'); // Input de fecha de vencimiento
        const codigoProductoInput = document.getElementById('codigo_producto'); // Input de código de producto

        // Validación del formulario antes de enviar
        form.addEventListener('submit', async function(event) {
            let isValid = true; // Variable para verificar si el formulario es válido

            // Verificar duplicados
            if (loteInput && fechaVencimientoInput) {
                const loteProducto = loteInput.value.trim();
                const fechaVencimiento = fechaVencimientoInput.value.trim();

                if (loteProducto || fechaVencimiento) {
                    try {
                        const response = await fetch('/ingresos/check-duplicate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                lote: loteProducto,
                                fecha_vencimiento: fechaVencimiento,
                            }),
                        });

                        const data = await response.json();

                        if (data.exists) {
                            alert('Ya existe un producto con este lote y la misma fecha de vencimiento.');
                            isValid = false; // Detener el envío
                        }
                    } catch (error) {
                        console.error('Error verificando duplicado:', error);
                        alert('Hubo un error al verificar el duplicado.');
                        isValid = false; // Detener el envío
                    }
                }
            }

            // Si el formulario no es válido, evitar el envío
            if (!isValid) {
                event.preventDefault();
            }
        });

        // Autocompletar datos del producto al cambiar el código
        if (codigoProductoInput) {
            codigoProductoInput.addEventListener('change', async function() {
                const codigoProducto = this.value.trim();

                if (codigoProducto) {
                    try {
                        const response = await fetch('/ingresos/get-product', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                codigo_producto: codigoProducto,
                            }),
                        });

                        const data = await response.json();

                        if (data) {
                            document.getElementById('nombre_producto').value = data.nombre_producto || '';
                            document.getElementById('costo_produccion_compra').value = data.costo_produccion_compra || '';
                            document.getElementById('precio_unidad_credito').value = data.precio_unidad_credito || '';
                            document.getElementById('precio_unidad_contado').value = data.precio_unidad_contado || '';
                            document.getElementById('precio_caja_credito').value = data.precio_caja_credito || '';
                            document.getElementById('precio_caja_contado').value = data.precio_caja_contado || '';
                            document.getElementById('precio_cajon_credito').value = data.precio_cajon_credito || '';
                            document.getElementById('precio_cajon_contado').value = data.precio_cajon_contado || '';
                            document.getElementById('precio_promocion').value = data.precio_promocion || '';
                            document.getElementById('stock_critico').value = data.stock_critico || '';
                            fechaVencimientoInput.value = data.fecha_vencimiento || '';
                        }
                    } catch (error) {
                        console.error('Error cargando datos del producto:', error);
                    }
                }
            });
        }
    });


    document.addEventListener('DOMContentLoaded', function() {
        const ingresoModal = document.getElementById('ingresoModal');
        const ingresoForm = document.getElementById('ingresoForm');

        // Escuchar el evento cuando el modal de ingreso se cierra
        ingresoModal.addEventListener('hidden.bs.modal', function() {
            // Restablecer todos los campos del formulario
            ingresoForm.reset();

            // Si hay campos específicos que requieren limpieza adicional, límpialos aquí
            const selects = ingresoForm.querySelectorAll('select');
            selects.forEach(select => {
                select.selectedIndex = 0; // Restablecer selects al primer valor
            });

            // También limpia valores calculados o autocompletados
            const codigoProductoInput = ingresoForm.querySelector('#codigo_producto');
            if (codigoProductoInput) codigoProductoInput.value = '';

            const nombreProductoInput = ingresoForm.querySelector('#nombre_producto');
            if (nombreProductoInput) nombreProductoInput.value = '';
        });
    });
</script>

<script>
    function filtrarPorAlmacen(almacenId) {
        window.location.href = "{{ route('ingresos.index') }}?almacen_id=" + almacenId;
    }
</script>

@endsection