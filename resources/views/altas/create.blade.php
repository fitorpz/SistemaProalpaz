<!-- resources/views/altas/create.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Registrar Alta</h1>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            @endforeach
        </ul>
    </div>
    @endif

    <br><a href="{{ route('altas.index') }}" class="btn btn-primary">Atras</a><br><br>

    <form action="{{ route('altas.store') }}" method="POST" id="alta-form">
        @csrf
        <div class="form-group">
            <label for="codigo_producto">Código Producto</label>
            <input type="text" name="codigo_producto" id="codigo_producto" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="nombre_producto">Nombre Producto</label>
            <input type="text" name="nombre_producto" id="nombre_producto" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="cantidad">Cantidad</label>
            <input type="number" name="cantidad" id="cantidad" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="fecha_vencimiento">Fecha Vencimiento</label>
            <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="lote">Lote</label>
            <input type="text" name="lote" id="lote" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="precio_unidad_credito">Costo produccion</label>
            <input type="number" name="precio_unidad_credito" id="precio_unidad_credito" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="precio_unidad_credito">Precio Unidad Crédito</label>
            <input type="number" name="precio_unidad_credito" id="precio_unidad_credito" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="precio_unidad_contado">Precio Unidad Contado</label>
            <input type="number" name="precio_unidad_contado" id="precio_unidad_contado" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="precio_caja_credito">Precio Caja Crédito</label>
            <input type="number" name="precio_caja_credito" id="precio_caja_credito" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="precio_caja_contado">Precio Caja Contado</label>
            <input type="number" name="precio_caja_contado" id="precio_caja_contado" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="precio_cajon_credito">Precio Cajón Crédito</label>
            <input type="number" name="precio_cajon_credito" id="precio_cajon_credito" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="precio_cajon_contado">Precio Cajón Contado</label>
            <input type="number" name="precio_cajon_contado" id="precio_cajon_contado" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="stock_critico">Stock Crítico</label>
            <input type="number" name="stock_critico" id="stock_critico" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="almacen_producto_terminado">Almacén Producto Terminado</label>
            <input type="number" name="almacen_producto_terminado" id="almacen_producto_terminado" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="almacen_materia_prima">Almacén Materia Prima</label>
            <input type="number" name="almacen_materia_prima" id="almacen_materia_prima" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="almacen_cosmeticos">Almacén Cosméticos</label>
            <input type="number" name="almacen_cosmeticos" id="almacen_cosmeticos" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Registrar Alta</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#codigo_producto').change(function() {
            let codigo_producto = $(this).val();
            $.get('/altas/fetchProducto/' + codigo_producto, function(data) {
                if (data) {
                    $('#nombre_producto').val(data.nombre_producto);
                } else {
                    alert('Producto no encontrado.');
                }
            });
        });
    });
</script>
@endsection
