@extends('layouts.app')

@section('content')


<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card shadow-sm border">
                <form id="editarPreventaForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h5>Editar Preventa <span id="numeroPedidoEditar" class="text-primary"></span></h5>
                    </div>
                    <div class="card-body">
                        <!-- ID de la Preventa (Oculto) -->
                        <input type="hidden" id="preventa_id" name="preventa_id">

                        <!-- Cliente -->
                        <div class="mb-3">
                            <label for="editar_cliente_id" class="form-label">Cliente</label>
                            <select name="cliente_id" id="editar_cliente_id" class="form-control" disabled>
                                <option value="">Seleccione un cliente</option>
                                @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre_comercio }} ({{ $cliente->nombre_propietario }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="detalles-editar-container" class="mb-3">
                            <!-- Detalles dinámicos se cargarán aquí -->
                        </div>

                        <div class="mt-3">
                            <div class="row align-items-center">
                                <div class="col-md-auto">
                                    <button type="button" class="btn btn-primary" id="btnAgregarProductoEditar">
                                        <i class="fas fa-plus"></i> Agregar Producto
                                    </button>
                                </div>
                                <div class="col-md-auto">
                                    <button type="button" class="btn btn-success" id="btnAgregarBonificacionEditar">
                                        <i class="fas fa-gift"></i> Agregar Bonificación
                                    </button>
                                </div>
                                @if(auth()->user()->rol === 'administrador')
                                <div class="col-md-auto d-flex align-items-center">
                                    <label for="descuento" class="mb-0 me-2 fw-bold">Descuento (%)</label>
                                    <input type="number" id="descuento" name="descuento" class="form-control w-auto"
                                        placeholder="%" min="0" max="100" value="0" style="max-width: 100px;">
                                </div>
                                @endif

                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="editar_observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="editar_observaciones" class="form-control" rows="3" placeholder="Escribe las observaciones..."></textarea>
                        </div>

                        <!-- Fecha de Entrega -->
                        <div class="mb-3">
                            <label for="editar_fecha_entrega" class="form-label">Fecha de Entrega</label>
                            <input type="date" class="form-control" id="editar_fecha_entrega" name="fecha_entrega" required>
                        </div>

                        <div class="mb-3">
                            <label for="editar_tipo_venta" class="form-label">Tipo de Venta</label>
                            <select name="tipo_venta" id="editar_tipo_venta" class="form-control" required>
                                <option value="">Seleccione un tipo de venta</option>
                                @foreach ($tiposVentasPermitidos as $tipo_id)
                                @php
                                $tipoVenta = $tiposVentas->firstWhere('id', $tipo_id);
                                @endphp
                                @if ($tipoVenta)
                                <option value="{{ $tipoVenta->tipo_venta }}"
                                    {{ isset($preventa) && $preventa->tipo_venta == $tipoVenta->tipo_venta ? 'selected' : '' }}>
                                    {{ $tipoVenta->tipo_venta }}
                                </option>
                                @endif
                                @endforeach
                            </select>
                        </div>


                        <!-- Precio Total -->
                        <div class="mt-4">
                            <label class="form-label fw-bold text-primary" style="font-size: 1.2rem;">Total de la Preventa</label>
                            <input type="text" class="form-control bg-light text-dark fw-bold"
                                id="editar_total" value="0.00" readonly
                                style="font-size: 1.5rem; border: 2px solid #007bff; padding: 10px;">
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary" onclick="guardarPreventaEdicion()">Guardar Cambios</button>
                        <a href="{{ route('picking.index') }}" class="btn btn-secondary">Volver</a>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const preventaId = "{{ $preventa->id ?? null }}"; // Asegura que esta variable esté disponible en Blade
        if (preventaId) {
            console.log("🟢 Cargando preventa ID:", preventaId);
            cargarPreventa(preventaId);
        } else {
            console.warn("⚠ No se encontró ID de preventa para cargar.");
        }
    });
    // Función para cargar los datos de la preventa y los detalles
    function cargarPreventa(preventaId) {
        if (!preventaId || preventaId === "undefined") {
            console.error("❌ Error: preventaId es inválido:", preventaId);
            return;
        }

        fetch(`/preventas/${preventaId}/edit`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("📌 Datos cargados de la preventa:", data);

                // 🚨 Verifica si hay un error en la respuesta del servidor
                if (!data || data.error) {
                    alert("🚫 No se pudo obtener la preventa. Es posible que no tengas permisos.");
                    console.warn("⚠ Permiso denegado o preventa no encontrada:", data.error);
                    return;
                }

                // ✅ Función auxiliar para asignar valores solo si el elemento existe
                function setValue(id, value) {
                    let element = document.getElementById(id);
                    if (element) {
                        element.value = value ?? '';
                    } else {
                        console.warn(`⚠ Elemento con ID "${id}" no encontrado en el DOM.`);
                    }
                }

                // ✅ Asignar datos al modal de edición con verificación
                setValue('preventa_id', data.id);
                setValue('editar_cliente_id', data.cliente_id);
                setValue('editar_observaciones', data.observaciones);
                setValue('editar_fecha_entrega', data.fecha_entrega);

                // ✅ Establecer la acción del formulario dinámicamente
                let formElement = document.getElementById('editarPreventaForm');
                if (formElement) {
                    formElement.action = `/preventas/${data.id}`;
                }

                // ✅ Mostrar el número de pedido en el modal
                let numeroPedidoElement = document.getElementById("numeroPedidoEditar");
                if (numeroPedidoElement) {
                    numeroPedidoElement.textContent = data.numero_pedido ? `#${data.numero_pedido}` : "";
                }

                // ✅ Asignar el descuento y total correctamente con verificación
                setValue('descuento', parseFloat(data.descuento).toFixed(2) || 0);
                setValue('editar_total', parseFloat(data.precio_total).toFixed(2) || 0);

                console.log(`✅ Descuento cargado: ${document.getElementById('descuento')?.value}`);
                console.log(`✅ Total cargado: ${document.getElementById('editar_total')?.value}`);

                // ✅ Verificar si el contenedor de detalles existe
                const container = document.getElementById('detalles-editar-container');
                if (!container) {
                    console.error('❌ El contenedor "detalles-editar-container" no existe en el DOM.');
                    return;
                }

                // ✅ Limpiar y cargar los detalles
                container.innerHTML = '';
                cargarDetallesPreventa(preventaId)
                    .then(() => {
                        calcularTotalEdicion(); // ✅ Calcular el total después de cargar detalles
                    })
                    .catch(error => console.error('❌ Error al cargar los detalles de la preventa:', error));
            })
            .catch(error => {
                console.error('❌ Error al cargar la preventa:', error);
                alert('Hubo un error al cargar los datos de la preventa.');
            });
    }

    function guardarPreventaEdicion() {
        let preventaId = document.getElementById("preventa_id")?.value || null;
        let observaciones = document.getElementById("editar_observaciones")?.value || "";
        let fechaEntrega = document.getElementById("editar_fecha_entrega")?.value || "";
        let totalPreventa = document.getElementById("editar_total")?.value || "0.00";
        let descuentoInput = document.getElementById("descuento");
        let descuento = descuentoInput ? parseFloat(descuentoInput.value) || 0 : 0;
        let tipoVentaSelect = document.getElementById("editar_tipo_venta");
        let tipoVenta = tipoVentaSelect ? tipoVentaSelect.value.trim() : "";

        if (!preventaId) {
            console.error("❌ Error: preventaId no definido.");
            alert("⚠ Error: No se encontró el ID de la preventa.");
            return;
        }

        if (!tipoVenta) {
            tipoVentaSelect.classList.add("is-invalid");
            alert("⚠ Debes seleccionar un tipo de venta antes de guardar.");
            return;
        } else {
            tipoVentaSelect.classList.remove("is-invalid");
        }

        let detalles = [];
        let errores = [];

        document.querySelectorAll('#detalles-editar-container .detalle-editar').forEach((fila, filaIndex) => {
            let index = fila.getAttribute("data-index");

            let productoId = document.querySelector(`.producto-id-hidden-editar[data-index="${index}"]`)?.value?.trim() || null;
            let fechaVencimiento = document.querySelector(`.fecha-vencimiento-select-editar[data-index="${index}"]`)?.value?.trim() || null;
            let cantidad = parseInt(document.querySelector(`.cantidad-input-editar[data-index="${index}"]`)?.value) || 0;
            let tipoPrecio = document.querySelector(`.tipo-precio-select-editar[data-index="${index}"]`)?.value || "";
            let precioUnitario = parseFloat(document.querySelector(`.precio-unitario-editar[data-index="${index}"]`)?.value) || 0;
            let subtotal = parseFloat(document.querySelector(`.subtotal-input-editar[data-index="${index}"]`)?.value) || 0;

            // 📌 Si el tipo de precio es "bonificación", forzar valores a 0
            if (tipoPrecio.toLowerCase() === "bonificación") {
                precioUnitario = 0.00;
                subtotal = 0.00;
            }

            // 📌 Depuración en consola
            console.log(`🔍 Verificando fila ${filaIndex + 1}:`, {
                productoId,
                fechaVencimiento,
                cantidad,
                tipoPrecio,
                precioUnitario,
                subtotal
            });

            // 📌 Validaciones SOLO si el tipoPrecio NO es "bonificación"
            if (tipoPrecio.toLowerCase() !== "bonificación") {
                if (!productoId) {
                    errores.push(`⚠ Fila ${filaIndex + 1}: Producto no seleccionado.`);
                }
                if (!fechaVencimiento) {
                    errores.push(`⚠ Fila ${filaIndex + 1}: Falta la fecha de vencimiento.`);
                }
                if (precioUnitario <= 0) {
                    errores.push(`⚠ Fila ${filaIndex + 1}: Precio unitario no válido.`);
                }
                if (subtotal < 0) {
                    errores.push(`⚠ Fila ${filaIndex + 1}: Subtotal no válido.`);
                }
            }

            // 📌 Agregar el detalle corregido
            detalles.push({
                producto_id: productoId,
                fecha_vencimiento: fechaVencimiento,
                cantidad: cantidad,
                tipo_precio: tipoPrecio,
                precio_unitario: precioUnitario,
                subtotal: subtotal
            });
        });

        // 📌 Mostrar errores agrupados
        if (errores.length > 0) {
            alert(`⚠ No se pueden guardar los cambios debido a los siguientes errores:\n\n${errores.join("\n")}`);
            return;
        }

        let datosEnviar = {
            _method: "PUT",
            observaciones: observaciones,
            fecha_entrega: fechaEntrega,
            total: totalPreventa,
            descuento: descuento,
            tipo_venta: tipoVenta,
            detalles: detalles
        };

        console.log("📌 Datos enviados en la solicitud:", datosEnviar);

        fetch(`/preventas/${preventaId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(datosEnviar)
            })
            .then(response => response.json())
            .then(data => {
                console.log("📌 Respuesta del servidor:", data);
                if (data.success) {
                    alert("✅ Preventa actualizada correctamente.");
                    location.reload();
                } else {
                    alert("⚠ Error al actualizar la preventa. Verifica los datos.");
                    console.error("Error del servidor:", data);
                }
            })
            .catch(error => {
                console.error("❌ Error en la solicitud fetch:", error);
                alert("❌ Error al comunicarse con el servidor.");
            });
    }

    // Función para cargar los detalles de la preventa


    function cargarDetallesPreventa(preventaId) {
        return fetch(`/preventas/${preventaId}/detalles-preventa`)
            .then(response => response.json())
            .then(detalles => {
                const container = document.getElementById('detalles-editar-container');
                container.innerHTML = ''; // Limpiar el contenedor antes de agregar detalles

                detalles.forEach((detalle, index) => {
                    const fila = document.createElement('div');
                    fila.classList.add('row', 'align-items-center', 'mb-3', 'p-2', 'border', 'rounded', 'bg-light');
                    fila.setAttribute('data-index', index);
                    fila.setAttribute('data-detalle-id', detalle.id); // ✅ Agregar ID correcto del detalle

                    fila.innerHTML = `
                <div class="col-md-2">
                    <label class="form-label fw-bold">Producto</label>
                    <input type="text" class="form-control bg-white" value="${detalle.nombre_producto}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha Venc.</label>
                    <input type="text" class="form-control bg-white" value="${detalle.fecha_vencimiento || 'N/A'}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tipo Precio</label>
                    <input type="text" class="form-control bg-white" value="${detalle.tipo_precio}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Cantidad</label>
                    <input type="number" class="form-control text-center cantidad-editar" value="${detalle.cantidad}" data-detalle-id="${detalle.id}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Precio Unit.</label>
                    <input type="text" class="form-control text-end bg-white" value="${detalle.precio_unitario}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Subtotal</label>
                    <input type="text" class="form-control text-end bg-white subtotal-editar" value="${detalle.subtotal}" readonly>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm w-100 btnEliminarProductoEditar" data-index="${index}" data-detalle-id="${detalle.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;

                    container.appendChild(fila);
                });

                actualizarEventosEliminar(); // 📌 Asignar eventos después de cargar detalles
                calcularTotalEdicion(); // ✅ Calcular el total al cargar productos
            })
            .catch(error => {
                console.error('❌ Error al cargar los detalles:', error);
                alert('Hubo un error al cargar los detalles de la preventa.');
            });
    }

    // 📌 Función para asociar el evento de eliminación a los botones
    function actualizarEventosEliminar() {
        document.querySelectorAll(".btnEliminarProductoEditar").forEach(boton => {
            boton.addEventListener("click", eliminarProductoEditar);
        });
    }

    function actualizarEventosCantidadEdicion() {
        document.querySelectorAll('.cantidad-input-editar').forEach(input => {
            input.removeEventListener('input', actualizarSubtotalCantidad); // Evita múltiples eventos
            input.addEventListener('input', actualizarSubtotalCantidad);
        });
    }

    function actualizarSubtotalCantidad(event) {
        let index = event.target.getAttribute("data-index");
        let cantidad = parseInt(event.target.value) || 0;
        let precioUnitario = parseFloat(document.querySelector(`.precio-unitario-editar[data-index="${index}"]`).value) || 0;
        let subtotal = cantidad * precioUnitario;

        let subtotalInput = document.querySelector(`.subtotal-editar[data-index="${index}"]`);
        subtotalInput.value = subtotal.toFixed(2);

        console.log(`🔄 Subtotal actualizado: ${subtotal.toFixed(2)} para índice ${index}`);

        calcularTotalEdicion(); // ✅ Recalcula el total general de la preventa
    }

    // ✅ Función para manejar la adición de productos en la edición sin duplicaciones
    function manejarAgregarProducto(event) {
        event.preventDefault(); // 🔥 Evita que el botón haga otras acciones inesperadas

        console.log("🛒 Agregar Producto clickeado");

        if (window.agregandoProducto) return; // ❗ Evita ejecuciones múltiples rápidas
        window.agregandoProducto = true;

        agregarFilaEdicion(false); // ✅ Solo agrega UNA fila

        setTimeout(() => {
            window.agregandoProducto = false;
        }, 100); // Pequeño delay para evitar doble clic rápido
    }


    // ✅ Función para manejar la adición de bonificaciones sin duplicaciones
    function manejarAgregarBonificacion() {
        console.log("🎁 Agregar Bonificación clickeado");

        if (window.agregandoBonificacion) return; // ❗ Evita duplicaciones
        window.agregandoBonificacion = true;

        setTimeout(() => {
            agregarFilaEdicion(true);
            window.agregandoBonificacion = false;
        }, 50);
    }



    function inicializarEventos() {
        console.log("📌 Inicializando eventos de eliminación y delegación de eventos.");

        // ✅ Obtener y verificar el CSRF token
        const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : null;

        console.log("📌 CSRF Token detectado:", csrfToken);

        if (!csrfToken) {
            console.error("❌ Error: No se encontró el CSRF token.");
            alert("Hubo un problema con la seguridad. Recarga la página e intenta nuevamente.");
            return;
        }
    }

    function eliminarProductoEditar(event) {
        let boton = event.target.closest("button.btnEliminarProductoEditar");
        if (!boton) {
            console.error("❌ Error: No se encontró el botón de eliminación.");
            return;
        }

        let fila = boton.closest(".row"); // ✅ Buscar la fila contenedora correctamente
        if (!fila) {
            console.error("❌ Error: No se encontró la fila del producto.");
            return;
        }

        let detalleId = fila.getAttribute("data-detalle-id"); // ✅ Obtener ID correcto del detalle
        let preventaId = document.getElementById("preventa_id")?.value; // ✅ Obtener preventaId

        if (!detalleId || !preventaId) {
            console.warn("⚠ Advertencia: `detalleId` o `preventaId` no válidos.");
            return;
        }

        let confirmacion = confirm(`¿Seguro que quieres eliminar este producto?`);
        if (!confirmacion) return;

        console.log(`🛠 Intentando eliminar producto con detalleId: ${detalleId}, preventaId: ${preventaId}`);

        fetch(`/preventas/eliminar-producto/${detalleId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    preventa_id: preventaId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log("📌 Respuesta del servidor:", data);
                if (data.success) {
                    fila.remove(); // ✅ Eliminar la fila del DOM
                    calcularTotalEdicion(); // ✅ Recalcular total
                } else {
                    alert("⚠ No se pudo eliminar el producto.");
                    console.error("Error del servidor:", data);
                }
            })
            .catch(error => {
                console.error("❌ Error en la solicitud:", error);
                alert("Error al comunicarse con el servidor.");
            });
    }

    function calcularTotalEdicion() {
        let total = 0;

        // Seleccionar todos los subtotales de productos existentes y nuevos
        let subtotalInputs = document.querySelectorAll('.subtotal-editar, .subtotal-input-editar');

        if (subtotalInputs.length === 0) {
            // 🚀 Simplemente retorna sin mostrar advertencias en consola.
            return;
        }

        console.log(`📌 Recalculando total. Subtotales detectados: ${subtotalInputs.length}`);

        subtotalInputs.forEach(input => {
            if (input && input.value) {
                let subtotal = parseFloat(input.value.replace(',', '.')) || 0;
                total += subtotal;
            } else {
                console.warn("⚠ Se encontró un input subtotal nulo o no válido.");
            }
        });

        // Esperar a que el campo de descuento esté disponible antes de proceder
        let descuentoInput = document.getElementById('descuento');

        if (!descuentoInput) {
            console.warn("⚠ No se encontró el campo de descuento, omitiendo descuento.");
            aplicarTotalSinDescuento(total);
            return;
        }

        let descuento = parseFloat(descuentoInput.value) || 0;

        // Ajustar el descuento si está fuera de los límites permitidos
        if (descuento < 0) {
            descuento = 0;
            descuentoInput.value = 0;
        } else if (descuento > 100) {
            descuento = 100;
            descuentoInput.value = 100;
        }

        // Aplicar el descuento al total
        let montoDescuento = (total * descuento) / 100;
        let totalFinal = total - montoDescuento;

        // Aplicar el total calculado
        aplicarTotalFinal(totalFinal, montoDescuento);
    }

    // Función para aplicar el total sin descuento si el campo de descuento no está disponible
    function aplicarTotalSinDescuento(total) {
        let totalInput = document.getElementById('editar_total');
        if (totalInput) {
            totalInput.value = total.toFixed(2);
            console.log(`✅ Total actualizado sin descuento: ${total.toFixed(2)}`);
        } else {
            console.error("❌ No se encontró el campo de total.");
        }
    }

    // Función para aplicar el total con descuento si el campo de descuento está disponible
    function aplicarTotalFinal(totalFinal, montoDescuento) {
        let totalInput = document.getElementById('editar_total');
        if (totalInput) {
            totalInput.value = totalFinal.toFixed(2);
            console.log(`✅ Total actualizado: ${totalFinal.toFixed(2)} (Descuento aplicado: ${montoDescuento.toFixed(2)})`);
        } else {
            console.error("❌ No se encontró el campo de total.");
        }
    }

    // Esperar a que el DOM esté completamente cargado antes de ejecutar la función
    document.addEventListener("DOMContentLoaded", () => {
        console.log("🔄 Esperando a que la página cargue completamente para ejecutar calcularTotalEdicion()");
        setTimeout(() => {
            calcularTotalEdicion();
        }, 500);
    });

    // ✅ Asignar evento al input de descuento para que recalule el total en tiempo real
    let inputDescuento = document.getElementById('descuento');

    if (inputDescuento) {
        inputDescuento.addEventListener('input', calcularTotalEdicion);
    }

    let detalleIndexEdicion = 0; // Índice para los detalles en edición

    // Función para agregar producto en edición
    document.getElementById('btnAgregarProductoEditar').addEventListener('click', function() {
        agregarFilaEdicion(false);
    });

    // Función para agregar bonificación en edición
    document.getElementById('btnAgregarBonificacionEditar').addEventListener('click', function() {
        agregarFilaEdicion(true);
    });

    /**
     * 🔹 Carga las fechas de vencimiento y asigna `producto_id` correctamente.
     * 🔹 También garantiza que los precios se almacenen correctamente.
     */
    function cargarFechasVencimientoEdicion(index, codigoProducto) {
        let fechaVencimientoSelect = document.querySelector(`.fecha-vencimiento-select-editar[data-index="${index}"]`);
        let productoIdInput = document.querySelector(`.producto-id-hidden-editar[data-index="${index}"]`);

        if (!codigoProducto || !fechaVencimientoSelect) {
            console.warn("⚠ No se encontró código de producto o select de fechas.");
            return;
        }

        // Limpiar select antes de cargar nuevas opciones
        fechaVencimientoSelect.innerHTML = '<option value="">Seleccione una fecha</option>';
        productoIdInput.value = ""; // Limpia el ID hasta seleccionar una fecha
        productoIdInput.removeAttribute("data-precios"); // Elimina precios previos

        fetch(`/preventas/ingresos/${codigoProducto}/fechas-vencimiento`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(producto => {
                        let option = document.createElement("option");
                        option.value = producto.fecha_vencimiento;
                        option.setAttribute("data-producto-id", producto.id);

                        // **🔍 Validación de precios antes de asignar**
                        let preciosJSON = "{}";
                        if (producto.precios && typeof producto.precios === "object" && Object.keys(producto.precios).length > 0) {
                            preciosJSON = JSON.stringify(producto.precios);
                        }
                        option.setAttribute("data-precios", preciosJSON);

                        option.textContent = `${producto.fecha_vencimiento} - Stock: ${producto.cantidad}`;
                        fechaVencimientoSelect.appendChild(option);
                    });

                    console.log(`✅ Fechas de vencimiento cargadas para el producto ${codigoProducto}:`, data);
                } else {
                    console.warn("⚠ No hay productos disponibles con este código.");
                }

                // ✅ Evento para actualizar `producto_id` y precios al seleccionar una fecha
                fechaVencimientoSelect.addEventListener("change", function() {
                    let selectedOption = fechaVencimientoSelect.options[fechaVencimientoSelect.selectedIndex];
                    let productoId = selectedOption.getAttribute("data-producto-id");
                    let preciosAttr = selectedOption.getAttribute("data-precios");

                    let precios = {}; // Iniciamos un objeto vacío por seguridad
                    if (preciosAttr && preciosAttr !== "undefined" && preciosAttr !== "{}") {
                        try {
                            precios = JSON.parse(preciosAttr);
                        } catch (error) {
                            console.error("❌ Error al convertir precios de JSON:", error);
                        }
                    }

                    if (productoId) {
                        productoIdInput.value = productoId;
                        productoIdInput.setAttribute("value", productoId);
                        console.log(`✅ Producto ID asignado correctamente en edición [${index}]:`, productoId);
                    }

                    if (Object.keys(precios).length > 0) {
                        productoIdInput.data("precios", precios);
                        productoIdInput.setAttribute("data-precios", JSON.stringify(precios));
                        console.log(`✅ Precios actualizados en edición [${index}]:`, precios);
                    }

                });

            })
            .catch(error => console.error('❌ Error al cargar fechas de vencimiento:', error));
    }



    function agregarFilaEdicion(esBonificacion) {
        const container = document.getElementById('detalles-editar-container');
        let detalleIndex = document.querySelectorAll('.detalle-editar').length;

        console.log(`🛠 Intentando agregar nueva fila con índice ${detalleIndex}...`);

        // ✅ Verificar si ya existe una fila con el mismo índice antes de agregar
        if (document.querySelector(`.detalle-editar[data-index="${detalleIndex}"]`)) {
            console.warn(`⚠ Advertencia: Ya existe una fila con índice ${detalleIndex}, no se agregará otra.`);
            return;
        }

        const detalle = document.createElement('div');
        detalle.classList.add('detalle-editar', 'mb-3', 'p-2', 'border', 'rounded', 'bg-light');
        detalle.setAttribute('data-index', detalleIndex);

        detalle.innerHTML = `
        <div class="row gx-2 gy-2 align-items-center">
            <div class="col-12 col-md-3">
                <label class="form-label">Producto</label>
                <input type="text" name="detalles[${detalleIndex}][producto_nombre]" 
                    class="form-control producto-input-editar"
                    id="producto-input-editar-${detalleIndex}"
                    data-index="${detalleIndex}"
                    placeholder="Escribe para buscar un producto..." required>
                
                <input type="hidden" name="detalles[${detalleIndex}][producto_id]" 
                    class="producto-id-hidden-editar"
                    id="producto-id-hidden-editar-${detalleIndex}"
                    data-index="${detalleIndex}">

                <input type="hidden" name="detalles[${detalleIndex}][codigo_producto]" 
                    class="producto-codigo-hidden-editar"
                    id="producto-codigo-hidden-editar-${detalleIndex}"
                    data-index="${detalleIndex}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">Fecha de Vencimiento</label>
                <select name="detalles[${detalleIndex}][fecha_vencimiento]" 
                        class="form-control fecha-vencimiento-select-editar" 
                        data-index="${detalleIndex}" required>
                    <option value="">Seleccione una fecha de vencimiento</option>
                </select>
                <input type="hidden" name="detalles[${detalleIndex}][fecha_vencimiento]" 
                   class="fecha-vencimiento-hidden-editar"
                   data-index="${detalleIndex}">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Tipo de Precio</label>
                <select name="detalles[${detalleIndex}][tipo_precio]" 
                        class="form-control tipo-precio-select-editar" 
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
                       class="form-control cantidad-input-editar" 
                       data-index="${detalleIndex}" min="1" value="1" required>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Precio Unitario</label>
                <input type="number" step="0.01" 
                       name="detalles[${detalleIndex}][precio_unitario]" 
                       class="form-control precio-unitario-editar" 
                       data-index="${detalleIndex}" 
                       value="${esBonificacion ? '0' : ''}" 
                       ${esBonificacion ? 'readonly' : ''}>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label">Subtotal</label>
                <input type="number" step="0.01" 
                       name="detalles[${detalleIndex}][subtotal]" 
                       class="form-control subtotal-input-editar" 
                       data-index="${detalleIndex}" 
                       value="${esBonificacion ? '0' : ''}" 
                       readonly>
            </div>
            <div class="col-12 col-md-1 text-end">
                <button type="button" class="btn btn-danger w-100 btnEliminarProductoEditar" 
                        data-index="${detalleIndex}">X</button>
            </div>
        </div>
    `;

        container.appendChild(detalle);

        // Verificar y ejecutar funciones adicionales si existen
        if (typeof inicializarAutocompleteEdicion === "function") {
            inicializarAutocompleteEdicion();
        } else {
            console.warn("⚠ Advertencia: La función 'inicializarAutocompleteEdicion' no está definida.");
        }

        if (typeof actualizarEventosTipoPrecioEdicion === "function") {
            actualizarEventosTipoPrecioEdicion();
        } else {
            console.warn("⚠ Advertencia: La función 'actualizarEventosTipoPrecioEdicion' no está definida.");
        }

        if (typeof actualizarEventosCantidadEdicion === "function") {
            actualizarEventosCantidadEdicion();
        } else {
            console.warn("⚠ Advertencia: La función 'actualizarEventosCantidadEdicion' no está definida.");
        }

        console.log(`✅ Producto agregado en EDICIÓN con índice ${detalleIndex}:`, {
            producto_id: document.getElementById(`producto-id-hidden-editar-${detalleIndex}`)?.value || "N/A",
            producto_nombre: document.getElementById(`producto-input-editar-${detalleIndex}`)?.value || "N/A",
            codigo_producto: document.getElementById(`producto-codigo-hidden-editar-${detalleIndex}`)?.value || "N/A",
        });
    }


    function inicializarAutocompleteEdicion() {
        $(".producto-input-editar").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "/buscar-productos",
                    dataType: "json",
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        console.log("🔍 Datos de la API recibidos (Edición):", data);
                        response($.map(data, function(item) {
                            return {
                                label: `${item.nombre} (Stock: ${item.stock_total})`,
                                value: item.nombre,
                                codigo_producto: item.codigo_producto,
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

                let codigoProductoInput = $(`.producto-codigo-hidden-editar[data-index="${index}"]`);
                let fechaVencimientoSelect = $(`.fecha-vencimiento-select-editar[data-index="${index}"]`);
                let productoIdInput = $(`.producto-id-hidden-editar[data-index="${index}"]`);

                if (!codigoProductoInput.length || !fechaVencimientoSelect.length || !productoIdInput.length) {
                    console.error(`❌ ERROR: No se encontraron los inputs ocultos para el índice ${index} en edición.`);
                    return;
                }

                // ✅ Asignar solo el código de producto
                codigoProductoInput.val(ui.item.codigo_producto).attr("value", ui.item.codigo_producto).trigger("change");

                // ✅ Limpiar opciones previas del select de fechas de vencimiento
                fechaVencimientoSelect.html('<option value="">Seleccione una fecha</option>');
                productoIdInput.val(""); // Limpiar ID hasta seleccionar fecha

                // ✅ Guardar precios en `data-precios`
                productoIdInput.data("precios", ui.item.precios);
                productoIdInput.attr("data-precios", JSON.stringify(ui.item.precios));

                console.log(`✅ Código del producto almacenado en edición [${index}]:`, ui.item.codigo_producto);
                console.log(`✅ Precios guardados en edición [${index}]:`, ui.item.precios);

                // ✅ Cargar fechas de vencimiento y asignar `producto_id`
                cargarFechasVencimientoEdicion(index, ui.item.codigo_producto);
            }
        });
    }



    function actualizarEventosTipoPrecioEdicion() {
        $(document).on("change", ".tipo-precio-select-editar", function() {
            let index = $(this).data("index");
            let tipoPrecio = $(this).val();
            let productoIdInput = $(`.producto-id-hidden-editar[data-index="${index}"]`);

            if (!productoIdInput.length || !tipoPrecio) {
                console.error("❌ Error: Producto o tipo de precio no seleccionados.");
                return;
            }

            let precios = productoIdInput.data("precios"); // 🔥 Obtener precios guardados en .data()

            if (!precios) {
                console.error("❌ Error: No hay precios almacenados para este producto.");
                return;
            }

            let precioUnitario = precios[tipoPrecio] || 0;
            let cantidadInput = $(`.cantidad-input-editar[data-index="${index}"]`);
            let cantidad = parseFloat(cantidadInput.val()) || 0;
            let subtotal = cantidad * precioUnitario;

            // Actualizar campos
            $(`.precio-unitario-editar[data-index="${index}"]`).val(precioUnitario.toFixed(2));
            $(`.subtotal-input-editar[data-index="${index}"]`).val(subtotal.toFixed(2));

            console.log(`✅ Precio actualizado: ${precioUnitario.toFixed(2)}, Subtotal: ${subtotal.toFixed(2)}`);

            // Recalcular el total general
            calcularTotalEdicion();
        });
    }

    function actualizarEventosCantidadEdicion() {
        $(document).on("input", ".cantidad-input-editar", function() {
            let index = $(this).data("index");
            let cantidad = parseFloat($(this).val()) || 0;
            let precioUnitario = parseFloat($(`.precio-unitario-editar[data-index="${index}"]`).val()) || 0;
            let subtotal = cantidad * precioUnitario;

            // Actualizar el subtotal
            let subtotalInput = $(`.subtotal-input-editar[data-index="${index}"]`);
            subtotalInput.val(subtotal.toFixed(2));

            console.log(`🔄 Subtotal actualizado: ${subtotal.toFixed(2)}`);

            // Recalcular el total general
            calcularTotalEdicion();
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        console.log("📌 Documento cargado. Inicializando eventos...");

        let btnAgregarProducto = document.getElementById("btnAgregarProductoEditar");
        let btnAgregarBonificacion = document.getElementById("btnAgregarBonificacionEditar");

        // ✅ Remover eventos previos antes de asignarlos
        if (btnAgregarProducto) {
            btnAgregarProducto.replaceWith(btnAgregarProducto.cloneNode(true));
            btnAgregarProducto = document.getElementById("btnAgregarProductoEditar");
            btnAgregarProducto.addEventListener("click", () => agregarFilaEdicion(false));
        }

        if (btnAgregarBonificacion) {
            btnAgregarBonificacion.replaceWith(btnAgregarBonificacion.cloneNode(true));
            btnAgregarBonificacion = document.getElementById("btnAgregarBonificacionEditar");
            btnAgregarBonificacion.addEventListener("click", () => agregarFilaEdicion(true));
        }

        let descuentoInput = document.getElementById("descuento");
        if (descuentoInput) {
            descuentoInput.removeEventListener("input", calcularTotalEdicion);
            descuentoInput.addEventListener("input", calcularTotalEdicion);
        }

        inicializarEventos();
        actualizarEventosEliminar();

        setTimeout(() => {
            let subtotalInputs = document.querySelectorAll('.subtotal-editar, .subtotal-input-editar');
            if (subtotalInputs.length > 0) {
                calcularTotalEdicion();
            }
        }, 500);
    });


    // ✅ Ejecutar eventos cuando el DOM esté listo
    document.addEventListener("DOMContentLoaded", function() {
        // ID desde la vista (oculto)
        const preventaId = document.getElementById("preventa_id")?.value;

        if (preventaId) {
            console.log("🔄 Llamando cargarPreventa con ID:", preventaId);
            cargarPreventa(preventaId);
        } else {
            console.warn("⚠ No se encontró preventa_id al cargar la vista.");
        }
    });
</script>
@endsection