@extends('layouts.app')

@section('content')

<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        /* Habilita un scroll más fluido en dispositivos táctiles */
    }

    .table {
        white-space: nowrap;
        /* Asegura que el contenido no se divida en varias líneas */
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
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-users me-2"></i>Gestión de Clientes
    </h4>

    <!-- Botón para redirigir a la nueva vista de registro -->
    <a href="{{ route('clientes.registrar') }}" class="btn btn-primary mb-3">
        <i class="fas fa-user-plus"></i> Registrar Cliente
    </a>

    <!-- 📌 Filtros Mejorados y Responsivos -->
    <form method="GET" action="{{ route('clientes.index') }}" class="mb-3">
        <div class="row g-3">

            <!-- 📌 Filtro por Nombre de Comercio -->
            <div class="col-12 col-md-4">
                <label for="nombre_comercio" class="form-label fw-bold">Nombre Comercio</label>
                <input type="text" name="nombre_comercio" id="nombre_comercio"
                    class="form-control" placeholder="Buscar por nombre..."
                    value="{{ request('nombre_comercio') }}">
            </div>

            <!-- 📌 Filtro por Día de Visita -->
            <div class="col-12 col-md-4">
                <label for="dia_visita" class="form-label fw-bold">Día de Visita</label>
                <select name="dia_visita" id="dia_visita" class="form-select">
                    <option value="">Todos los días</option>
                    @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                    <option value="{{ $dia }}" {{ request('dia_visita') == $dia ? 'selected' : '' }}>
                        {{ $dia }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- 📌 Filtro por Usuario (Solo para Administradores y Operadores) -->
            @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_operador')
            <div class="col-12 col-md-4">
                <label for="usuario" class="form-label fw-bold">Usuario</label>
                <select name="usuario" class="form-select">
                    <option value="">Todos los usuarios</option>
                    @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ request('usuario') == $usuario->id ? 'selected' : '' }}>
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

                <!-- Botón Restablecer -->
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Restablecer
                </a>

                <!-- Botón Generar PDF -->
                <a href="{{ route('clientes.generarPDFConFiltros', request()->query()) }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </div>

        </div>
    </form>



    @if($clientes->isEmpty())
    <div class="alert alert-warning">
        No tienes clientes registrados. Puedes agregar uno nuevo usando el botón "Registrar Cliente".
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Propietario</th>
                    <th>NIT</th>
                    <th>Dirección</th>
                    <th>Día de Visita</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientes as $cliente)
                <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#viewClienteModal{{ $cliente->id }}">
                    <td>{{ $cliente->codigo_cliente }}</td>
                    <td>{{ $cliente->nombre_propietario }} - {{ $cliente->nombre_comercio }}</td>
                    <td>{{ $cliente->nit ?? 'Sin NIT' }}</td>
                    <td>{{ $cliente->direccion ?? 'Sin Dirección' }}</td>
                    <td>{{ $cliente->dia_visita ?? 'No Definido' }}</td>
                    <td>
                        <!-- Botón para Editar Cliente -->
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editClienteModal{{ $cliente->id }}">
                            <i class="fas fa-edit"></i> Editar
                        </button>

                        <!-- Botón para Eliminar Cliente (Visible solo para Administrador) -->
                        @if(auth()->user()->rol === 'administrador')
                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                        @endif

                    </td>
                </tr>

                <!-- Modal Ver Cliente (Solo muestra datos, no editable) -->
                <div class="modal fade" id="viewClienteModal{{ $cliente->id }}" tabindex="-1" aria-labelledby="viewClienteLabel{{ $cliente->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title" id="viewClienteLabel{{ $cliente->id }}">Detalles del Cliente</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Código:</strong> {{ $cliente->codigo_cliente }}</p>
                                <p><strong>Propietario:</strong> {{ $cliente->nombre_propietario }}</p>
                                <p><strong>Comercio:</strong> {{ $cliente->nombre_comercio }}</p>
                                <p><strong>NIT:</strong> {{ $cliente->nit ?? 'No registrado' }}</p>
                                <p><strong>Dirección:</strong> {{ $cliente->direccion }}</p>
                                <p><strong>Referencia:</strong> {{ $cliente->referencia ?? 'Sin referencia' }}</p>
                                <p><strong>Ubicación:</strong>
                                    @if($cliente->ubicacion)
                                    <a href="{{ $cliente->ubicacion }}" target="_blank">Ver Ubicación</a>
                                    @else
                                    Sin Ubicación
                                    @endif
                                </p>
                                <p><strong>Horario de Atención:</strong> {{ $cliente->horario_atencion }}</p>
                                <p><strong>Teléfono:</strong> {{ $cliente->telefono ?? 'No registrado' }}</p>
                                <p><strong>Cumpleaños Doctor:</strong>
                                    @if($cliente->cumpleanos_doctor)
                                    {{ \Carbon\Carbon::parse($cliente->cumpleanos_doctor)->format('d/m/Y') }}
                                    @else
                                    N/A
                                    @endif
                                </p>
                                <p><strong>Horario Visita:</strong> {{ $cliente->horario_visita }}</p>
                                <p><strong>Observaciones:</strong> {{ $cliente->observaciones ?? 'Sin observaciones' }}</p>
                                <p><strong>Día Visita:</strong> {{ $cliente->dia_visita }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editClienteModal{{ $cliente->id }}" tabindex="-1" aria-labelledby="editClienteLabel{{ $cliente->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editClienteLabel{{ $cliente->id }}">Editar Cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Llamamos al formulario parcial -->
                                    @include('clientes.partials.form', ['formType' => 'edit', 'cliente' => $cliente])
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- Modal Crear Cliente -->
<div class="modal fade" id="createClienteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('clientes.partials.form', ['formType' => 'create'])
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function setLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    const locationField = document.querySelector('#createClienteModal #ubicacion');

                    if (locationField) {
                        locationField.value = `https://www.google.com/maps?q=${latitude},${longitude}`;
                    } else {
                        console.error('No se encontró el campo de ubicación en el formulario.');
                    }
                },
                function(error) {
                    let message = '';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'El usuario denegó la solicitud de geolocalización.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'La ubicación no está disponible.';
                            break;
                        case error.TIMEOUT:
                            message = 'El tiempo de espera de la solicitud de ubicación ha expirado.';
                            break;
                        case error.UNKNOWN_ERROR:
                            message = 'Ocurrió un error desconocido.';
                            break;
                    }
                    alert(`Error al obtener la ubicación: ${message}`);
                }
            );
        } else {
            alert('Tu navegador no admite geolocalización.');
        }
    }

    // Evento para limpiar el formulario al cerrar el modal de crear cliente
    document.getElementById('createClienteModal').addEventListener('hidden.bs.modal', function() {
        // Resetea todos los campos del formulario
        const formulario = this.querySelector('form');
        formulario.reset();

        // Limpia el campo de ubicación, si existe
        const ubicacionField = formulario.querySelector('#ubicacion');
        if (ubicacionField) {
            ubicacionField.value = '';
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Para el modal de creación
        document.querySelectorAll(".dia-visita-checkbox-create").forEach(checkbox => {
            checkbox.addEventListener("change", function() {
                let seleccionados = document.querySelectorAll(".dia-visita-checkbox-create:checked").length;
                if (seleccionados > 2) {
                    this.checked = false;
                    document.getElementById("dia_visita_error_create").style.display = "block";
                } else {
                    document.getElementById("dia_visita_error_create").style.display = "none";
                }
            });
        });

        // Para el modal de edición - Esperar a que el modal se muestre
        document.querySelectorAll('[id^="editClienteModal"]').forEach(modal => {
            modal.addEventListener("shown.bs.modal", function() {
                let modalId = this.getAttribute("id").replace("editClienteModal", ""); // Obtener ID del cliente
                let diasSeleccionados = document.querySelector(`#editClienteModal${modalId} input[name="dia_visita_hidden"]`).value.split(",");

                // Marcar los checkboxes correctos
                document.querySelectorAll(`#editClienteModal${modalId} .dia-visita-checkbox-edit`).forEach(checkbox => {
                    checkbox.checked = diasSeleccionados.includes(checkbox.getAttribute("data-dia"));
                });

                // Agregar validación de máximo 2 checkboxes en edición
                document.querySelectorAll(`#editClienteModal${modalId} .dia-visita-checkbox-edit`).forEach(checkbox => {
                    checkbox.addEventListener("change", function() {
                        let seleccionados = document.querySelectorAll(`#editClienteModal${modalId} .dia-visita-checkbox-edit:checked`).length;
                        if (seleccionados > 2) {
                            this.checked = false;
                            document.getElementById("dia_visita_error_edit").style.display = "block";
                        } else {
                            document.getElementById("dia_visita_error_edit").style.display = "none";
                        }
                    });
                });
            });
        });
    });
</script>

@endsection