@extends('layouts.app')

@section('content')

<style>
    .table-responsive {
        max-height: 400px;
        /* Ajusta la altura de la tabla */
        overflow-y: auto;
        /* Habilita el scroll vertical */
        border: 1px solid #ccc;
        /* Opcional: Agrega un borde */
    }

    .table thead tr {
        position: sticky;
        top: 0;
        background: #fff;
        /* Fijar el fondo para que no sea transparente */
        z-index: 100;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        /* Sombra para destacar */
    }
</style>
<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-balance-scale me-2"></i>Estado de Cuentas
    </h4>



    <div class="d-flex justify-content-end mb-3">
        <h4 class="fw-bold">
            Total por cobrar: <span id="total-generado">{{ number_format($totalGenerado, 2) }}</span> Bs.
        </h4>
    </div>

    <!-- 📌 Filtros -->
    <form method="GET" action="{{ route('contabilidad.cobranzas.estadoCuentas') }}" class="mb-3">
        <div class="row g-2 align-items-end">
            <!-- Filtro por Cliente (Autocomplete) -->
            <div class="col-12 col-md-3">
                <label for="cliente_autocomplete" class="form-label fw-bold">Cliente</label>
                <input type="text" id="cliente_autocomplete" class="form-control" placeholder="Buscar cliente...">
                <input type="hidden" name="cliente_filtro" id="cliente_filtro">
            </div>
            <!-- Filtro por Almacén -->
            <div class="col-12 col-md-3">
                <label for="almacen_id" class="form-label fw-bold">Almacén</label>
                <select class="form-select" name="almacen_id" id="almacen_id">
                    <option value="">Todos</option>
                    @foreach($almacenes as $almacen)
                    <option value="{{ $almacen->id }}" {{ request('almacen_id') == $almacen->id ? 'selected' : '' }}>
                        {{ $almacen->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>


            <!-- Botones de Acción -->
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('contabilidad.cobranzas.estadoCuentas') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync-alt"></i> Restablecer
                </a>
            </div>
        </div>
    </form>



    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Monto Total</th>
                    <th>Saldo Pendiente</th>
                    <th>Estado</th>
                    <th>Historial</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargos as $cargo)
                <tr>
                    <td>{{ $cargo->cliente->nombre_propietario }} - {{ $cargo->cliente->nombre_comercio }}</td>
                    <td>{{ number_format($cargo->monto_total, 2) }}</td>
                    <td>{{ number_format($cargo->saldo_pendiente, 2) }}</td>
                    <td>
                        @if ($cargo->estado == 'Pendiente')
                        Pendiente de pago
                        @else
                        {{ $cargo->estado }}
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('contabilidad.cobranzas.historial', $cargo->cliente_id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@section('scripts')
<!-- Autocompletado con JavaScript -->
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
                    url: route,
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
                                label: `${item.nombre_propietario} - ${item.nombre_comercio}`,
                                value: item.nombre_propietario + " - " + item.nombre_comercio,
                                id: item.id
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

                return false;
            }
        });
    }

    // ✅ Inicializar Autocomplete en Cobranzas
    $(document).ready(function() {
        console.log("🚀 Documento listo. Inicializando búsqueda de clientes...");

        inicializarAutocompleteClientes("#cliente_autocomplete", "#cliente_filtro", "{{ route('cobranzas.buscarClientes') }}");
    });
</script>

@endsection



@endsection