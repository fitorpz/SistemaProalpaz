@extends('layouts.app')

@section('content')

<style>
    .table-responsive {
        max-height: 500px;
        /* Ajusta según necesites */
        overflow-y: auto;
        border: 1px solid #ddd;
    }

    /* 📌 Fija el encabezado de la tabla */
    .table thead {
        position: sticky;
        top: 0;
        background: #343a40;
        /* Color de fondo del encabezado */
        color: white;
        z-index: 100;
    }
</style>
<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-route me-2"></i>Rutas Programadas
    </h4>


    <!-- 📌 Filtros Mejorados y Responsivos -->
    <form method="GET" action="{{ route('rutas.index') }}" class="mb-3">
        <div class="row g-3">

            <!-- 📌 Filtro por Fecha -->
            <div class="col-12 col-md-4">
                <label for="fecha_filtro" class="form-label fw-bold">Seleccionar Fecha:</label>
                <input type="date" id="fecha_filtro" name="fecha" class="form-control"
                    value="{{ request('fecha', now()->toDateString()) }}">
            </div>

            <!-- 📌 Filtro por Usuario (Solo visible para administradores) -->
            @if(Auth::user()->rol === 'administrador')
            <div class="col-12 col-md-4">
                <label for="usuario_filtro" class="form-label fw-bold">Seleccionar Usuario:</label>
                <select id="usuario_filtro" name="usuario_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                        {{ $usuario->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- 📌 Botones de Acción -->
            <div class="col-12 d-flex flex-wrap justify-content-center gap-2 mt-3">
                <!-- Botón Filtrar -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>

                <!-- Botón Generar PDF -->
                <a href="{{ url('/rutas/generar-pdf') . '?' . http_build_query(request()->query()) }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </div>

        </div>
    </form>



    {{-- Tabla de rutas --}}
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th>Ubicación</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre_comercio }} - {{ $cliente->nombre_propietario }}</td>
                    <td>{{ $cliente->direccion }}</td>
                    <td>
                        @if($cliente->visitas->isNotEmpty())
                        <span class="badge bg-success">✔ Registrada</span>
                        @else
                        <span class="badge bg-danger">✘ No Registrada</span>
                        @endif
                    </td>
                    <td>
                        @if($cliente->visitas->isNotEmpty())
                        <a href="{{ $cliente->visitas->first()->ubicacion }}" target="_blank">Ver Ubicación</a>
                        @else
                        No disponible
                        @endif
                    </td>
                    <td>{{ $cliente->visitas->first()->observaciones ?? 'Sin observaciones' }}</td>
                    <td>
                        @if($cliente->visitas->isEmpty())
                        <div class="d-flex flex-column gap-1">
                            <!-- Botón para registrar visita -->
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="abrirModalRegistro('{{ $cliente->id }}', '{{ $fechaSeleccionada }}')">
                                Registrar Visita
                            </button>

                            <!-- Botón para redirigir a crear preventa desde nueva página -->
                            <a href="{{ route('preventas.crearDesdeRuta', ['cliente_id' => $cliente->id, 'fecha' => $fechaSeleccionada]) }}"
                                class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Crear Preventa
                            </a>
                        </div>
                        @else
                        <span class="badge bg-primary">{{ $cliente->visitas->first()->created_at->format('H:i') }}</span>
                        @endif
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal para registrar visita -->
    <div class="modal fade" id="registrarVisitaModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="registrarVisitaForm" method="POST" action="{{ url('rutas/registrar-visita') }}">

                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Registrar Visita</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="cliente_id" id="modal_cliente_id">
                        <input type="hidden" name="fecha" id="modal_fecha_visita">

                        <div class="form-group mb-3">
                            <label for="ubicacion">Ubicación</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="ubicacion" id="modal_ubicacion" placeholder="Ingrese o use ubicación actual" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="setLocation()">Ubicación Actual</button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control" name="observaciones" id="modal_observaciones" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalRegistro(clienteId, fecha) {
            document.getElementById("modal_cliente_id").value = clienteId;
            document.getElementById("modal_fecha_visita").value = fecha;
            new bootstrap.Modal(document.getElementById("registrarVisitaModal")).show();
        }

        function setLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        document.getElementById("modal_ubicacion").value = `https://www.google.com/maps?q=${latitude},${longitude}`;
                    },
                    function(error) {
                        alert('Error al obtener la ubicación.');
                    }
                );
            } else {
                alert('Tu navegador no soporta geolocalización.');
            }
        }

        document.getElementById("registrarVisitaForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(this);
            let clienteId = formData.get("cliente_id");

            fetch(`{{ url('rutas/registrar-visita') }}/${clienteId}`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Visita registrada correctamente.");
                        location.reload();
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    </script>

</div>
@endsection