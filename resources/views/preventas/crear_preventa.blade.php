@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <h4 class="text-left fw-bold text-secondary mb-4">
                        <i class="fas fa-cart-plus me-2"></i>Registrar Preventa desde Ruta
                    </h4>

                    <form action="{{ route('preventas.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ubicacion" id="ubicacion-actual">
                        <input type="hidden" name="desde_ruta" value="1">
                        <input type="hidden" name="registrar_visita" value="1">
                        <input type="hidden" name="fecha_visita" value="{{ $fecha }}">

                        {{-- Cliente --}}
                        <div class="mb-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" value="{{ $cliente->nombre_comercio }} - {{ $cliente->nombre_propietario }}" disabled>
                            <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                        </div>

                        {{-- Detalles --}}
                        <h5>Detalles de la Preventa</h5>
                        <div id="detalles-container"></div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="btnAgregarProducto">Agregar Producto</button>
                            <button type="button" class="btn btn-success" id="btnAgregarBonificacion">Agregar Bonificación</button>
                        </div>

                        {{-- Precio Total --}}
                        <div class="mt-4">
                            <label for="precio_total" class="form-label">Precio Total</label>
                            <input type="text" id="precio_total" class="form-control" readonly>
                        </div>

                        {{-- Observaciones --}}
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3" placeholder="Escribe las observaciones..."></textarea>
                        </div>

                        {{-- Fecha entrega --}}
                        <div class="mb-3">
                            <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                            <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required>
                        </div>

                        {{-- Tipo de venta --}}
                        <div class="mb-3">
                            <label for="tipo_venta" class="form-label">Tipo de Venta</label>
                            <select name="tipo_venta" id="tipo_venta" class="form-control" required
                                {{ count($tiposVentasPermitidos) == 1 ? 'disabled' : '' }}>

                                @if (count($tiposVentasPermitidos) > 1)
                                    <option value="">Seleccione un tipo de venta</option>
                                @endif

                                @foreach ($tiposVentasPermitidos as $tipo_id)
                                    @php
                                        $tipoVenta = $tiposVentas->firstWhere('id', $tipo_id);
                                    @endphp
                                    @if ($tipoVenta)
                                        <option value="{{ $tipoVenta->tipo_venta }}"
                                            {{ count($tiposVentasPermitidos) == 1 ? 'selected' : (auth()->user()->tipo_venta == $tipoVenta->tipo_venta ? 'selected' : '') }}>
                                            {{ $tipoVenta->tipo_venta }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                            @if (count($tiposVentasPermitidos) == 1)
                                <input type="hidden" name="tipo_venta" value="{{ $tiposVentas->firstWhere('id', $tiposVentasPermitidos[0])->tipo_venta }}">
                            @endif
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Guardar Preventa</button>
                        </div>
                    </form>

                </div> {{-- card-body --}}
            </div> {{-- card --}}
        </div>
    </div>
</div>

<script>
    // Obtener ubicación actual
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            document.getElementById('ubicacion-actual').value = `https://www.google.com/maps?q=${lat},${lon}`;
        }, function(error) {
            console.warn("No se pudo obtener ubicación actual:", error.message);
        });
    }

    let detalleIndex = 0;

    // Función para agregar producto
    document.getElementById('btnAgregarProducto').addEventListener('click', function() {
        agregarFila(false);
    });

    // Función para agregar bonificación
    document.getElementById('btnAgregarBonificacion').addEventListener('click', function() {
        agregarFila(true);
    });

    function cargarFechasVencimiento(index) {
        const codigoProducto = $(`.producto-codigo-hidden[data-index="${index}"]`).val();
        const fechaVencimientoSelect = $(`.fecha-vencimiento-select[data-index="${index}"]`);
        const productoHiddenInput = $(`.producto-id-hidden[data-index="${index}"]`);

        if (codigoProducto) {
            fetch(`/preventas/ingresos/${codigoProducto}/fechas-vencimiento`)
                .then(response => response.json())
                .then(data => {
                    fechaVencimientoSelect.html('<option value="">Seleccione una fecha de vencimiento</option>');

                    if (data.length > 0) {
                        data.forEach(producto => {
                            fechaVencimientoSelect.append(
                                `<option value="${producto.fecha_vencimiento}" data-producto-id="${producto.id}">${producto.fecha_vencimiento} - Stock: ${producto.cantidad}</option>`
                            );
                        });

                        // ✅ Cambiar el evento cuando se seleccione una fecha de vencimiento
                        fechaVencimientoSelect.off('change').on('change', function() {
                            const selectedOption = $(this).find(":selected");
                            const selectedFecha = selectedOption.val(); // ✅ Capturamos la fecha seleccionada
                            const selectedProductId = selectedOption.data("producto-id");

                            if (selectedProductId) {
                                productoHiddenInput.val(selectedProductId);
                                console.log(`✅ Producto ID actualizado para el índice ${index}:`, selectedProductId);
                            }

                            // ✅ Guardamos la fecha seleccionada en un input hidden
                            $(`.fecha-vencimiento-hidden[data-index="${index}"]`).val(selectedFecha);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al cargar fechas de vencimiento:', error);
                });
        } else {
            fechaVencimientoSelect.html('<option value="">Seleccione una fecha de vencimiento</option>');
            productoHiddenInput.val("");
        }
    }


    function agregarFila(esBonificacion) {
        const container = document.getElementById('detalles-container');
        let detalleIndex = document.querySelectorAll('.detalle').length;
        const detalle = document.createElement('div');
        detalle.classList.add('detalle', 'mb-3');
        detalle.setAttribute('data-index', detalleIndex);

        detalle.innerHTML = `
        <div class="row gx-2 gy-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label">Producto</label>
                <input type="text" name="detalles[${detalleIndex}][producto_nombre]" 
                    class="form-control producto-input"
                    id="producto-input-${detalleIndex}"
                    data-index="${detalleIndex}"
                    placeholder="Escribe para buscar un producto..." required>
           
                 <!-- Input oculto para el ID de ingresos (para la base de datos) -->
                <input type="hidden" name="detalles[${detalleIndex}][producto_id]" 
                    class="producto-id-hidden"
                    id="producto-id-hidden-${detalleIndex}"
                    data-index="${detalleIndex}">

                <!-- Input oculto para el código del producto (para fechas de vencimiento) -->
                <input type="hidden" name="detalles[${detalleIndex}][codigo_producto]" 
                    class="producto-codigo-hidden"
                    id="producto-codigo-hidden-${detalleIndex}"
                    data-index="${detalleIndex}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Fecha de Vencimiento</label>
                <select name="detalles[${detalleIndex}][fecha_vencimiento]" 
                        class="form-control fecha-vencimiento-select" 
                        data-index="${detalleIndex}" required>
                    <option value="">Seleccione una fecha de vencimiento</option>
                </select>
                <input type="hidden" name="detalles[${detalleIndex}][fecha_vencimiento]" 
                   class="fecha-vencimiento-hidden"
                   data-index="${detalleIndex}">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Tipo de Precio</label>
                <select name="detalles[${detalleIndex}][tipo_precio]" 
                        class="form-control tipo-precio-select" 
                        data-index="${detalleIndex}" 
                        ${esBonificacion ? 'disabled' : 'required'}>
                    ${esBonificacion ? 
                        '<option value="bonificacion" selected>Bonificación</option>' : `
                        <option value="">Seleccione tipo</option>
                        <option value="precio_unidad_credito">Unidad Crédito</option>
                        <option value="precio_unidad_contado">Unidad Contado</option>
                        <option value="precio_caja_credito">Caja Crédito</option>
                        <option value="precio_caja_contado">Caja Contado</option>
                        <option value="precio_cajon_credito">Cajón Crédito</option>
                        <option value="precio_cajon_contado">Cajón Contado</option>
                        <option value="precio_promocion">Promoción</option>`}
                </select>

                ${esBonificacion ? `<input type="hidden" name="detalles[${detalleIndex}][tipo_precio]" value="bonificacion">` : ''}
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" name="detalles[${detalleIndex}][cantidad]" 
                       class="form-control cantidad-input" 
                       data-index="${detalleIndex}" min="1" value="1" required>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Precio Unitario</label>
                <input type="number" step="0.01" 
                       name="detalles[${detalleIndex}][precio_unitario]" 
                       class="form-control precio-unitario" 
                       data-index="${detalleIndex}" 
                       value="${esBonificacion ? '0' : ''}" 
                       ${esBonificacion ? 'readonly' : ''} readonly>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Subtotal</label>
                <input type="number" step="0.01" 
                       name="detalles[${detalleIndex}][subtotal]" 
                       class="form-control subtotal-input" 
                       data-index="${detalleIndex}" 
                       value="${esBonificacion ? '0' : ''}" 
                       readonly>
            </div>
            <div class="col-12 col-md-1 text-end">
                <button type="button" class="btn btn-danger w-100 btnEliminarProducto" 
                        data-index="${detalleIndex}">X</button>
            </div>
        </div>
    `;

        container.appendChild(detalle);
        inicializarAutocomplete();
        actualizarEventosTipoPrecio();
    }


    function inicializarAutocomplete() {
        $(".producto-input").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "/buscar-productos",
                    dataType: "json",
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        console.log("🔍 Datos de la API recibidos:", data);
                        response($.map(data, function(item) {
                            return {
                                label: `${item.nombre} (Stock Total: ${item.stock_total})`,
                                value: item.nombre,
                                id: item.id, // ✅ ID de la tabla ingresos (para la base de datos)
                                codigo_producto: item.codigo_producto, // ✅ Código de producto (para fechas de vencimiento)
                                precios: {
                                    precio_unidad_credito: parseFloat(item.precio_unidad_credito) || 0,
                                    precio_unidad_contado: parseFloat(item.precio_unidad_contado) || 0,
                                    precio_caja_credito: parseFloat(item.precio_caja_credito) || 0,
                                    precio_caja_contado: parseFloat(item.precio_caja_contado) || 0,
                                    precio_cajon_credito: parseFloat(item.precio_cajon_credito) || 0,
                                    precio_cajon_contado: parseFloat(item.precio_cajon_contado) || 0,
                                    precio_promocion: parseFloat(item.precio_promocion) || 0
                                }
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                let index = $(this).data("index");
                $(this).val(ui.item.value);

                let productoIdInput = $(`.producto-id-hidden[data-index="${index}"]`);
                let codigoProductoInput = $(`.producto-codigo-hidden[data-index="${index}"]`);

                if (!productoIdInput.length || !codigoProductoInput.length) {
                    console.error(`❌ ERROR: No se encontraron los inputs ocultos para el índice ${index}.`);
                    return;
                }

                // ✅ Guardar el ID correcto de ingresos para la base de datos
                productoIdInput.val(ui.item.id);

                // ✅ Guardar el código de producto para cargar fechas de vencimiento
                codigoProductoInput.val(ui.item.codigo_producto);

                // ✅ También guardamos los precios
                productoIdInput.data("precios", ui.item.precios);
                productoIdInput.attr("data-precios", JSON.stringify(ui.item.precios));

                console.log("✅ ID del producto (ingresos.id) almacenado:", productoIdInput.val());
                console.log("✅ Código del producto almacenado:", codigoProductoInput.val());
                console.log("✅ Precios guardados en .data():", productoIdInput.data("precios"));

                // Cargar fechas de vencimiento usando el código del producto
                cargarFechasVencimiento(index);
            }
        });

    }

    function actualizarEventosTipoPrecio() {
        $(document).off('change', '.tipo-precio-select').on('change', '.tipo-precio-select', function() {
            let index = $(this).data("index");
            let tipoPrecio = $(this).val();
            let precioUnitarioInput = $(`.precio-unitario[data-index="${index}"]`);
            let productoHiddenInput = $(`.producto-id-hidden[data-index="${index}"]`);

            let precios = productoHiddenInput.data("precios");

            if (!precios) {
                return console.error("❌ ERROR: No se encontraron precios en `.data()` del producto seleccionado.");
            }

            if (precios.hasOwnProperty(tipoPrecio)) {
                let precioSeleccionado = parseFloat(precios[tipoPrecio]) || 0;

                precioUnitarioInput.val(precioSeleccionado.toFixed(2));
                console.log("✅ Precio unitario actualizado:", precioSeleccionado.toFixed(2));

                calcularSubtotal(index);
            } else {
                console.error("❌ ERROR: No se encontró precio para este tipo de precio.");
            }
        });

        // 🔥 Actualizar subtotal cuando cambia la cantidad
        $(document).off('input', '.cantidad-input').on('input', '.cantidad-input', function() {
            let index = $(this).data("index");
            calcularSubtotal(index);
        });

        // 🔥 Eliminar fila y actualizar total
        $(document).off('click', '.btnEliminarProducto').on('click', '.btnEliminarProducto', function() {
            let index = $(this).data("index");
            $(`.detalle[data-index="${index}"]`).remove();
            calcularPrecioTotal();
        });
    }

    function calcularSubtotal(index) {
        let cantidad = parseFloat($(`.cantidad-input[data-index="${index}"]`).val()) || 0;
        let precioUnitario = parseFloat($(`.precio-unitario[data-index="${index}"]`).val()) || 0;
        let subtotal = cantidad * precioUnitario;
        $(`.subtotal-input[data-index="${index}"]`).val(subtotal.toFixed(2));

        calcularPrecioTotal();
    }

    function calcularPrecioTotal() {
        let total = 0;
        $('.subtotal-input').each(function() {
            total += parseFloat($(this).val()) || 0;
        });

        $('#precio_total').val(total.toFixed(2));
        console.log("💰 Total de la preventa:", total.toFixed(2));
    }

    function inicializarSelect2() {
        $('.select-producto').select2({
            placeholder: 'Escribe para buscar un producto...',
            allowClear: true,
            minimumInputLength: 2, // 🔥 Comienza a buscar después de 2 caracteres
            width: '100%', // 🔥 Asegura que el ancho sea correcto
            theme: "bootstrap-5", // 🔥 Opcional: Aplica estilos de Bootstrap 5
            ajax: {
                url: '/buscar-productos',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(producto => ({
                            id: producto.id,
                            text: `${producto.nombre} (Stock: ${producto.cantidad})`
                        }))
                    };
                },
                cache: true
            }
        });
    }
</script>
@endsection