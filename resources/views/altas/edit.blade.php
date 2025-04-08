@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="text-center">Editar Alta de Producto</h2>
    <a href="{{ route('altas.index') }}" class="btn btn-primary">Atras</a><br><br>

    <!-- Mostrar mensajes de error -->
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('altas.update', $alta->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="codigo_producto" class="form-label">Código del Producto</label>
            <input type="text" class="form-control" id="codigo_producto" name="codigo_producto" value="{{ $alta->codigo_producto }}" required>
        </div>

        <div class="mb-3">
            <label for="nombre_producto" class="form-label">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" value="{{ $alta->nombre_producto }}" required>
        </div>

        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad Total</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" value="{{ $alta->cantidad }}" required>
        </div>

        <div class="mb-3">
            <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ $alta->fecha_vencimiento }}" required>
        </div>

        <div class="mb-3">
            <label for="lote" class="form-label">Lote</label>
            <input type="text" class="form-control" id="lote" name="lote" value="{{ $alta->lote }}" required>
        </div>

        <div class="mb-3">
            <label for="costo_ingreso" class="form-label">Costo de Ingreso (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="costo_ingreso" name="costo_ingreso" value="{{ $alta->costo_ingreso }}" required>
        </div>

        <!-- Precios -->
        <div class="mb-3">
            <label for="precio_unidad_credito" class="form-label">Precio Unidad Crédito (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="precio_unidad_credito" name="precio_unidad_credito" value="{{ $alta->precio_unidad_credito }}" required>
        </div>

        <div class="mb-3">
            <label for="precio_unidad_contado" class="form-label">Precio Unidad Contado (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="precio_unidad_contado" name="precio_unidad_contado" value="{{ $alta->precio_unidad_contado }}" required>
        </div>

        <!-- Otros precios y campos de almacenes -->
        <div class="mb-3">
            <label for="precio_caja_credito" class="form-label">Precio Caja Crédito (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="precio_caja_credito" name="precio_caja_credito" value="{{ $alta->precio_caja_credito }}" required>
        </div>

        <div class="mb-3">
            <label for="precio_caja_contado" class="form-label">Precio Caja Contado (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="precio_caja_contado" name="precio_caja_contado" value="{{ $alta->precio_caja_contado }}" required>
        </div>

        <div class="mb-3">
            <label for="precio_cajon_credito" class="form-label">Precio Cajón Crédito (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="precio_cajon_credito" name="precio_cajon_credito" value="{{ $alta->precio_cajon_credito }}" required>
        </div>

        <div class="mb-3">
            <label for="precio_cajon_contado" class="form-label">Precio Cajón Contado (Bs.)</label>
            <input type="number" step="0.01" class="form-control" id="precio_cajon_contado" name="precio_cajon_contado" value="{{ $alta->precio_cajon_contado }}" required>
        </div>

        <div class="mb-3">
            <label for="stock_critico" class="form-label">Stock Crítico</label>
            <input type="number" class="form-control" id="stock_critico" name="stock_critico" value="{{ $alta->stock_critico }}" required>
        </div>

        <!-- Almacenes -->
        <div class="mb-3">
            <label for="almacen_producto_terminado" class="form-label">Almacén Producto Terminado</label>
            <input type="number" class="form-control" id="almacen_producto_terminado" name="almacen_producto_terminado" value="{{ $alta->almacen_producto_terminado }}" required>
        </div>

        <div class="mb-3">
            <label for="almacen_materia_prima" class="form-label">Almacén Materia Prima</label>
            <input type="number" class="form-control" id="almacen_materia_prima" name="almacen_materia_prima" value="{{ $alta->almacen_materia_prima }}" required>
        </div>

        <div class="mb-3">
            <label for="almacen_cosmeticos" class="form-label">Almacén Cosméticos</label>
            <input type="number" class="form-control" id="almacen_cosmeticos" name="almacen_cosmeticos" value="{{ $alta->almacen_cosmeticos }}" required>
        </div>

        <div id="error-message" class="alert alert-danger d-none"></div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="{{ route('altas.index') }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
    document.getElementById('cantidad').addEventListener('input', validateSum);
    document.getElementById('almacen_producto_terminado').addEventListener('input', validateSum);
    document.getElementById('almacen_materia_prima').addEventListener('input', validateSum);
    document.getElementById('almacen_cosmeticos').addEventListener('input', validateSum);

    function validateSum() {
        const cantidadTotal = parseInt(document.getElementById('cantidad').value) || 0;
        const almacenTerminado = parseInt(document.getElementById('almacen_producto_terminado').value) || 0;
        const almacenMateriaPrima = parseInt(document.getElementById('almacen_materia_prima').value) || 0;
        const almacenCosmeticos = parseInt(document.getElementById('almacen_cosmeticos').value) || 0;

        const sumaAlmacenes = almacenTerminado + almacenMateriaPrima + almacenCosmeticos;

        const errorMessage = document.getElementById('error-message');

        if (sumaAlmacenes !== cantidadTotal) {
            errorMessage.classList.remove('d-none');
            errorMessage.textContent = 'La suma de los productos en los almacenes no coincide con la cantidad total.';
        } else {
            errorMessage.classList.add('d-none');
        }
    }
</script>

@endsection
