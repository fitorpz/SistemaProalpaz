@php
// Obtener los tipos de venta permitidos sin decodificar si ya es un array
$tiposVentasPermitidos = auth()->user()->tipos_ventas_permitidos;

// Si es un string JSON, decodificarlo
if (is_string($tiposVentasPermitidos)) {
$tiposVentasPermitidos = json_decode($tiposVentasPermitidos, true);
}

// Asegurar que siempre sea un array
$tiposVentasPermitidos = is_array($tiposVentasPermitidos) ? $tiposVentasPermitidos : [];
@endphp
@extends('layouts.app')

@section('content')

<style>
    .fecha-entrega {
        white-space: nowrap;
        /* Evita que el texto se divida en varias líneas */
        width: 100px;
        /* Ajusta el ancho de la celda según tu necesidad */
        text-align: center;
        /* Centra el texto dentro de la celda (opcional) */
    }

    .nro-pedido {
        white-space: nowrap;
        /* Evita que el texto se divida en varias líneas */
        width: 100px;
        /* Ajusta el ancho de la celda según tu necesidad */
        text-align: center;
    }

    .ui-autocomplete {
        z-index: 9999 !important;
        /* Bootstrap modal usa 1050, así que lo ponemos en 1051 */
        position: absolute;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        max-height: 200px;
        overflow-y: auto;
    }


    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        /* Ajusta altura */
        padding: 0.375rem 0.75rem;
    }

    .select2-container--bootstrap-5 .select2-results__option {
        font-size: 14px;
    }

    .select2-dropdown {
        z-index: 9999;
        /* Asegura que se vea sobre otros elementos */
    }

    .table-responsive {
        max-height: 400px;
        /* Ajusta la altura máxima */
        overflow-y: auto;
        /* Habilita el scroll vertical */
        border: 1px solid #ccc;
        /* Borde opcional */
    }

    .table thead tr {
        position: sticky;
        top: 0;
        background: #fff;
        /* Fondo fijo para evitar transparencia */
        z-index: 100;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        /* Sombra para resaltar */
    }
</style>


<div class="container">
    <h4 class="text-left text-secondary fw-bold mt-0 mb-4">
        <i class="fas fa-shopping-cart me-2 text-primary"></i>Gestión de Preventas
    </h4>

    <!-- Botón para abrir el modal de creación -->
    <button type="button" class="btn btn-primary w-20 w-md-auto" data-bs-toggle="modal" data-bs-target="#crearPreventaModal">
        Crear Preventa
    </button>

    <div class="d-flex justify-content-end mb-3">
        <h4 class="fw-bold">Total Generado al momento: <span id="total-generado">{{ number_format($totalGenerado, 2) }}</span> Bs.</h4>
    </div>

    <!-- 📅 Filtros: Fecha, Almacén, Cliente y Preventista -->
    <!-- 📅 Filtros Mejorados y Responsivos -->
    <form method="GET" action="{{ route('preventas.index') }}" class="mb-3">
        <div class="row g-3">

            <!-- 📌 Filtro por Fecha -->
            <div class="col-12 col-md-3">
                <label for="fecha_filtro" class="form-label fw-bold">Fecha</label>
                <input type="date" name="fecha_filtro" id="fecha_filtro"
                    class="form-control" value="{{ request('fecha_filtro') }}">
            </div>

            <!-- 📌 Filtro por Empresa (Almacén) -->
            <div class="col-12 col-md-3">
                <label for="almacen_filtro" class="form-label fw-bold">Empresa</label>
                <select name="almacen_filtro" id="almacen_filtro" class="form-select"
                    {{ auth()->user()->rol === 'gestion_ventas' ? 'disabled' : '' }}>
                    <option value="">Todos</option>
                    @foreach($almacenes as $almacen)
                    <option value="{{ $almacen->id }}" {{ request('almacen_filtro') == $almacen->id ? 'selected' : '' }}>
                        {{ $almacen->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- 📌 Filtro por Cliente (Autocomplete) -->
            <div class="col-12 col-md-3">
                <label for="cliente_filtro" class="form-label fw-bold">Cliente</label>
                <input type="text" id="cliente_autocomplete" class="form-control"
                    placeholder="Buscar cliente..." value="{{ request('cliente_nombre') }}">
                <input type="hidden" name="cliente_filtro" id="cliente_filtro" value="{{ request('cliente_filtro') }}">
            </div>

            <!-- 📌 Filtro por Preventista -->
            <div class="col-12 col-md-3">
                <label for="preventista_filtro" class="form-label fw-bold">Preventista</label>
                <select name="preventista_filtro" id="preventista_filtro" class="form-select"
                    {{ auth()->user()->rol === 'gestion_ventas' ? 'disabled' : '' }}>
                    <option value="">Todos</option>
                    @foreach($preventistas as $preventista)
                    <option value="{{ $preventista->id }}" {{ request('preventista_filtro') == $preventista->id ? 'selected' : '' }}>
                        {{ $preventista->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- 📌 Botones de Acción -->
            <div class="col-12 d-flex flex-wrap justify-content-center gap-2 mt-3">
                <!-- Botón Filtrar -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>

                <!-- Botón Restablecer -->
                <a href="{{ route('preventas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Restablecer
                </a>

                <!-- Botón Generar PDF -->
                <a href="{{ route('preventas.generarPDFConFiltros', request()->query()) }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </div>

        </div>
    </form>




    {{-- Tabla para listar preventas --}}
    <form id="formEliminarMultiples" action="{{ route('preventas.eliminarMultiples') }}" method="POST" onsubmit="return confirmarEliminarSeleccionados();">
        @csrf
        @method('DELETE')

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        @auth
                        @if(auth()->user()->rol === 'administrador')
                        <th>
                            <input type="checkbox" id="check-all" onclick="toggleSeleccionarTodos(this)" />
                        </th>
                        @endif
                        @endauth

                        <th>Número de Pedido</th>
                        <th>Cliente</th>
                        <th>Precio Total</th>
                        <th>Observaciones</th>
                        <th>Fecha de Entrega</th>
                        @if (auth()->check() && in_array(auth()->user()->rol, ['usuario_operador', 'administrador']))
                        <th>Preventista</th>
                        @endif
                        <th>Acciones</th>
                        @if (auth()->check() && in_array(auth()->user()->rol, ['usuario_operador', 'administrador']))
                        <th>Nota Remisión</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($preventas as $preventa)
                    <tr>
                        @auth
                        @if(auth()->user()->rol === 'administrador')
                        <td>
                            <input type="checkbox" class="check-preventa" name="preventas_seleccionadas[]" value="{{ $preventa->id }}">
                        </td>
                        @endif
                        @endauth

                        <td class="nro-pedido">{{ $preventa->numero_pedido }}</td>
                        <td>{{ $preventa->cliente->nombre_propietario }}, {{ $preventa->cliente->nombre_comercio }}</td>
                        <td>{{ $preventa->precio_total }}</td>
                        <td class="text-dark fw-bold">{{ $preventa->observaciones ?? 'Sin Observaciones' }}</td>
                        <td class="fecha-entrega">{{ \Carbon\Carbon::parse($preventa->fecha_entrega)->format('Y-m-d') }}</td>
                        @if (auth()->check() && in_array(auth()->user()->rol, ['usuario_operador', 'administrador']))
                        <td>{{ $preventa->preventista->nombre ?? 'No especificado' }}</td>
                        @endif
                        <td>
                            <div class="btn-group">
                                @if(auth()->check() && in_array(auth()->user()->rol, ['administrador', 'usuario_operador', 'gestion_ventas']))
                                <a href="#" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editarPreventaModal" onclick="cargarPreventa('{{ $preventa->id }}')">
                                    <i class="fas fa-edit"></i> Editar
                                </a>

                                <form action="{{ route('preventas.destroy', $preventa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta preventa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('preventas.pdf', $preventa->id) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                        @if (auth()->check() && in_array(auth()->user()->rol, ['usuario_operador', 'administrador']))
                        <td>
                            <a href="{{ route('preventas.nota-remision', $preventa->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="fas fa-file-alt"></i> Nota de Remisión
                            </a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay preventas registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @auth
        @if(auth()->user()->rol === 'administrador')
        <button type="submit" class="btn btn-danger mt-3">
            <i class="fas fa-trash-alt"></i> Eliminar seleccionados
        </button>
        @endif
        @endauth

    </form>



    <!-- Modal para Editar Preventa -->
    <div class="modal fade" id="editarPreventaModal" tabindex="-1" aria-labelledby="editarPreventaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editarPreventaForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarPreventaModalLabel">Editar Preventa <span id="numeroPedidoEditar" class="text-primary"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="guardarPreventaEdicion()">Guardar Cambios</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal para crear preventa --}}
<div class="modal fade" id="crearPreventaModal" tabindex="-1" aria-labelledby="crearPreventaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('preventas.store') }}" method="POST">
                @csrf
                <!-- Campos ocultos para registrar visita automáticamente -->
                <input type="hidden" name="registrar_visita" id="registrar-visita" value="0">
                <input type="hidden" name="fecha_visita" id="fecha-visita" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="crearPreventaModalLabel">Crear Preventa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Selector de cliente --}}
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <input type="text" id="cliente-input" class="form-control" placeholder="Escribe para buscar un cliente..." required>
                        <input type="hidden" id="cliente-id" name="cliente_id"> <!-- Campo oculto para almacenar el ID del cliente -->
                    </div>



                    {{-- Detalles de la preventa --}}
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
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Escribe las observaciones..."></textarea>
                    </div>

                    {{-- Fecha de entrega --}}
                    <div class="mb-3">
                        <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                        <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required>
                    </div>

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

                        <!-- Si solo hay un tipo de venta asignado, enviar el valor como campo oculto -->
                        @if (count($tiposVentasPermitidos) == 1)
                        <input type="hidden" name="tipo_venta" value="{{ $tiposVentas->firstWhere('id', $tiposVentasPermitidos[0])->tipo_venta }}">
                        @endif
                    </div>




                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Preventa</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>


@section('scripts')
<script>
    document.getElementById('check-all')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.check-preventa');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    function confirmarEliminarSeleccionados() {
        const seleccionados = document.querySelectorAll('.check-preventa:checked');
        if (seleccionados.length === 0) {
            alert('Selecciona al menos una preventa para eliminar.');
            return false;
        }

        return confirm('¿Estás seguro de eliminar las preventas seleccionadas?');
    }
</script>

<script>
    function inicializarAutocompleteClientes(inputSelector, hiddenInputSelector, route) {
        console.log(`🚀 Inicializando Autocomplete para: ${inputSelector}`);

        if (typeof jQuery == "undefined") {
            console.error("❌ jQuery no está definido. Verifica que se ha cargado correctamente.");
            return;
        }

        $(inputSelector).autocomplete({
            source: function(request, response) {
                console.log("🔍 Buscando clientes con término:", request.term);

                $.ajax({
                    url: route, // ✅ Se recibe la ruta como parámetro
                    dataType: "json",
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        console.log("📌 Datos recibidos en Autocomplete Clientes:", data);

                        if (!data || data.length === 0) {
                            console.warn("⚠ No se encontraron clientes.");
                            response([{
                                label: "No se encontraron clientes",
                                value: ""
                            }]);
                            return;
                        }

                        response($.map(data, function(item) {
                            return {
                                label: `${item.nombre_propietario} - ${item.nombre_comercio} (ID: ${item.codigo_cliente || 'N/A'})`,
                                value: item.nombre_propietario + " - " + item.nombre_comercio,
                                id: item.id,
                                codigo_cliente: item.codigo_cliente || ""
                            };
                        }));
                    },
                    error: function(xhr, status, error) {
                        console.error("❌ Error en la búsqueda de clientes:", error);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                console.log("✅ Cliente seleccionado:", ui.item.value, "ID:", ui.item.id);

                $(inputSelector).val(ui.item.value);
                $(hiddenInputSelector).val(ui.item.id);

                // Si el input oculto de código cliente existe, lo actualiza también
                let clienteCodigoInput = $("#cliente-codigo");
                if (clienteCodigoInput.length) {
                    clienteCodigoInput.val(ui.item.codigo_cliente);
                }

                return false;
            }
        });
    }

    // ✅ Inicializar Autocomplete en diferentes partes del sistema
    $(document).ready(function() {
        console.log("🚀 Documento listo. Inicializando búsqueda de clientes...");

        // 🟢 Autocomplete en los filtros de la vista principal
        inicializarAutocompleteClientes("#cliente_autocomplete", "#cliente_filtro", "{{ route('clientes.buscarNombre') }}");

        // 🟢 Autocomplete en el modal al abrirlo
        $("#crearPreventaModal").on("shown.bs.modal", function() {
            inicializarAutocompleteClientes("#cliente-input", "#cliente-id", "/buscar-clientes");
        });
    });
</script>

<script>
    function generarPDFConFiltros() {
        // Obtener valores de los filtros
        let fecha = document.getElementById("fecha_filtro").value;
        let almacen = document.getElementById("almacen_filtro").value;
        let cliente = document.getElementById("cliente_filtro").value;
        let preventista = document.getElementById("preventista_filtro").value;

        // Construir la URL con los filtros
        let url = "{{ route('preventas.generarPDFConFiltros') }}";
        let params = new URLSearchParams({
            fecha_filtro: fecha,
            almacen_filtro: almacen,
            cliente_filtro: cliente,
            preventista_filtro: preventista
        });

        // Redirigir a la URL con los filtros aplicados
        window.open(url + "?" + params.toString(), "_blank");
    }
</script>

@endsection

<script>
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

<script>
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

    // ✅ Evitar ejecución de calcularTotalEdicion antes de que haya productos
    function calcularTotalEdicion() {
        let subtotalInputs = document.querySelectorAll('.subtotal-editar, .subtotal-input-editar');

        console.log(`📌 Recalculando total. Subtotales detectados: ${subtotalInputs.length}`);

        if (subtotalInputs.length === 0) {
            if (document.readyState !== "complete") {
                console.log("⏳ Página aún cargando, esperando productos...");
            } else {
                console.warn("⚠ No se encontraron subtotales en el formulario.");
            }
            return;
        }

        let total = 0;
        subtotalInputs.forEach(input => {
            let subtotal = parseFloat(input.value.replace(',', '.')) || 0;
            total += subtotal;
        });

        let descuento = parseFloat(document.getElementById('descuento')?.value) || 0;
        let totalFinal = total - (total * descuento / 100);

        document.getElementById('editar_total').value = totalFinal.toFixed(2);
        console.log(`✅ Total actualizado: ${totalFinal.toFixed(2)} (Descuento aplicado: ${descuento}%)`);
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
    document.addEventListener("DOMContentLoaded", inicializarEventos);
</script>
@endsection