@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Registrar Compra</h1><br><br>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <a href="{{ route('compras.index') }}" class="btn btn-primary">Atras</a><br><br>

    <form action="{{ route('compras.store') }}" method="POST" id="compra-form">
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
            <label for="costo_compra">Costo Compra</label>
            <input type="number" name="costo_compra" id="costo_compra" class="form-control" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="con_factura">Compra con Factura</label>
            <input type="checkbox" name="con_factura" id="con_factura" value="1" onchange="updateCosto()">
        </div>
        <div class="form-group">
            <label for="precio_unidad_credito">Precio Unidad Crédito</label>
            <input type="number" name="precio_unidad_credito" id="precio_unidad_credito" class="form-control" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="precio_unidad_contado">Precio Unidad Contado</label>
            <input type="number" name="precio_unidad_contado" id="precio_unidad_contado" class="form-control" step="0.01" required>
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

        <button type="submit" class="btn btn-primary">Registrar Compra</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateCosto() {
        const costoCompraInput = $('#costo_compra');
        let costoCompra = parseFloat(costoCompraInput.val());
        const isConFactura = $('#con_factura').is(':checked');

        if (isConFactura) {
            costoCompra = costoCompra * 0.87; // Aplicar el descuento del 13%
        }

        costoCompraInput.val(costoCompra.toFixed(2)); // Actualizar el valor en el campo de costo
    }

    $(document).ready(function() {
        $('#codigo_producto').change(function() {
            let codigo_producto = $(this).val();
            $.get('/compras/fetchProducto/' + codigo_producto, function(data) {
                if (data) {
                    $('#nombre_producto').val(data.nombre_producto);
                    // Aquí puedes agregar más campos que quieras llenar automáticamente
                } else {
                    alert('Producto no encontrado.');
                }
            });
        });
    });

    // Validar la suma de los almacenes antes de enviar el formulario
    $('#compra-form').on('submit', function(e) {
        const cantidadTotal = parseInt($('#cantidad').val());
        const almacenProductoTerminado = parseInt($('#almacen_producto_terminado').val()) || 0;
        const almacenMateriaPrima = parseInt($('#almacen_materia_prima').val()) || 0;
        const almacenCosmeticos = parseInt($('#almacen_cosmeticos').val()) || 0;

        const sumaAlmacenes = almacenProductoTerminado + almacenMateriaPrima + almacenCosmeticos;

        if (sumaAlmacenes !== cantidadTotal) {
            e.preventDefault(); // Prevenir el envío del formulario
            alert('La suma de los productos en los almacenes no coincide con la cantidad total.');
        }
    });

    $(document).ready(function() {
        $('#codigo_producto').change(function() {
            let codigo_producto = $(this).val();
            $.get('/compras/fetchProducto/' + codigo_producto, function(data) {
                if (data) {
                    $('#nombre_producto').val(data.nombre_producto);
                    // Aquí puedes agregar más campos que quieras llenar automáticamente
                } else {
                    alert('Producto no encontrado.');
                }
            });
        });
    });
</script>
@endsection