@extends('layouts.app')

@include('layouts.header')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Traspasos de Productos</h2>

    <a href="{{ route('inventory.index') }}" class="btn btn-primary">Atrás</a><br><br>

    <!-- Formulario para buscar un producto -->
    <form method="POST" action="{{ url('traspasos') }}" class="mb-4">
        @csrf
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Buscar por código, nombre, lote o fecha de vencimiento" name="search_query" required>
            <button class="btn btn-primary" type="submit" name="buscar">Buscar</button>
        </div>
    </form>

    @if (isset($result) && count($result) > 0)
    <!-- Mostrar los resultados de la búsqueda -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad Disponible</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Lote</th>
                    <th>Almacén Producto Terminado</th>
                    <th>Almacén Insumos</th>
                    <th>Almacén Cosméticos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $traspaso)
                <tr>
                    <td>{{ $traspaso->codigo_producto }}</td>
                    <td>{{ $traspaso->nombre_producto }}</td>
                    <td>{{ $traspaso->cantidad }}</td>
                    <td>{{ $traspaso->fecha_vencimiento }}</td>
                    <td>{{ $traspaso->lote }}</td>
                    <td>{{ $traspaso->almacen_producto_terminado }}</td>
                    <td>{{ $traspaso->almacen_insumos }}</td>
                    <td>{{ $traspaso->almacen_cosmeticos }}</td>
                    <td>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#traspasoModal-{{ $traspaso->codigo_producto }}">
                            Realizar Traspaso
                        </button>
                    </td>
                </tr>
                <!-- Modal para realizar el traspaso -->
                <div class="modal fade" id="traspasoModal-{{ $traspaso->codigo_producto }}" tabindex="-1" aria-labelledby="traspasoModalLabel-{{ $traspaso->codigo_producto }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="traspasoModalLabel-{{ $traspaso->codigo_producto }}">Realizar Traspaso de Producto</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ url('traspasos/registrar') }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <!-- Campos del producto -->
                                    <input type="hidden" name="codigo_producto" value="{{ $traspaso->codigo_producto }}">
                                    <input type="hidden" name="nombre_producto" value="{{ $traspaso->nombre_producto }}">
                                    <input type="hidden" name="lote" value="{{ $traspaso->lote }}">
                                    <input type="hidden" name="fecha_vencimiento" value="{{ $traspaso->fecha_vencimiento }}">

                                    <div class="mb-3">
                                        <label for="cantidad" class="form-label">Cantidad a Traspasar</label>
                                        <input type="number" class="form-control" id="cantidad" name="cantidad" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fecha_traspaso" class="form-label">Fecha del Traspaso</label>
                                        <input type="date" class="form-control" id="fecha_traspaso" name="fecha_traspaso" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="almacen_origen">Almacén de Origen</label>
                                        <select name="almacen_origen" id="almacen_origen" class="form-select">
                                            <option value="Almacén Producto Terminado">Almacén Producto Terminado</option>
                                            <option value="Almacén Insumos">Almacén Insumos</option>
                                            <option value="Almacén Cosméticos">Almacén Cosméticos</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="almacen_destino">Almacén de Destino</label>
                                        <select name="almacen_destino" id="almacen_destino" class="form-select">
                                            <option value="Almacén Producto Terminado">Almacén Producto Terminado</option>
                                            <option value="Almacén Insumos">Almacén Insumos</option>
                                            <option value="Almacén Cosméticos">Almacén Cosméticos</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-primary">Registrar Traspaso</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
    @elseif (request()->isMethod('post'))
    <div class="alert alert-warning">No se encontraron productos con ese criterio de búsqueda.</div>
    @endif

    <!-- Mostrar traspasos realizados -->
    <h3 class="mt-5">Traspasos Realizados</h3>
    @if (count($traspasos) > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Código Producto</th>
                    <th>Nombre Producto</th>
                    <th>Cantidad Traspasada</th>
                    <th>Fecha del Traspaso</th>
                    <th>Almacén Origen</th>
                    <th>Almacén Destino</th>
                    <th>Almacén Producto Terminado</th>
                    <th>Almacén Insumos</th>
                    <th>Almacén Cosméticos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($traspasos as $traspaso)
                <tr>
                    <td>{{ $traspaso->codigo_producto }}</td>
                    <td>{{ $traspaso->nombre_producto }}</td>
                    <td>{{ $traspaso->cantidad }}</td>
                    <td>{{ $traspaso->fecha_traspaso }}</td>
                    <td>{{ $traspaso->almacen_origen }}</td>
                    <td>{{ $traspaso->almacen_destino }}</td>
                    <td>{{ $traspaso->almacen_producto_terminado }}</td>
                    <td>{{ $traspaso->almacen_insumos }}</td>
                    <td>{{ $traspaso->almacen_cosmeticos }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info">No se han registrado traspasos aún.</div>
    @endif
</div>
@endsection
