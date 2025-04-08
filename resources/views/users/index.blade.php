@extends('layouts.app')

@section('content')

<style>
    /* 📌 Contenedor de la tabla con scroll */
    .table-responsive {
        max-height: 500px;
        /* Altura máxima con scroll */
        overflow-y: auto;
    }

    /* 📌 Encabezado fijo */
    .table thead {
        position: sticky;
        top: 0;
        background: #343a40;
        /* Fondo oscuro para diferenciar */
        color: white;
        z-index: 1000;
    }

    /* 📌 Estilos generales */
    .table {
        border-collapse: collapse;
        width: 100%;
    }

    /* 📌 Celdas de la tabla */
    .table th,
    .table td {
        text-align: left;
        vertical-align: middle;
        padding: 10px;
        border: 1px solid #dee2e6;
    }

    /* 📌 Botones dentro de la tabla */
    .table .btn {
        font-size: 12px;
        padding: 5px 10px;
    }

    /* 📌 Ajuste para pantallas pequeñas */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
        }

        .table th,
        .table td {
            font-size: 12px;
            padding: 8px;
        }

        .table .btn {
            font-size: 10px;
            padding: 4px 8px;
        }
    }
</style>

<div class="container">
    @if(auth()->user() && (auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_operador'))
    <h1>Lista de Usuarios</h1>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <!-- Botón para abrir el modal de agregar usuario -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
        Agregar Nuevo Usuario
    </button>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">Atrás</a>

    <br><br>

    <hr>
    <h2>Tipos de Ventas</h2>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTipoVentaModal">Agregar Tipo de Venta</button>
    <br><br>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tipo de Venta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tiposVentas as $tipo)
            <tr>
                <td>{{ $tipo->tipo_venta }}</td>
                <td>
                    <form action="{{ route('tipos_ventas.destroy', $tipo->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este tipo de venta?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal para agregar un nuevo tipo de venta -->
<div class="modal fade" id="createTipoVentaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Tipo de Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('tipos_ventas.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-2">
                        <label>Nombre del Tipo de Venta</label>
                        <input type="text" name="tipo_venta" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Almacenes Permitidos</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->nombre }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->rol) }}</td>
                <td>
                    @if($user->nombres_almacenes)
                    {{ $user->nombres_almacenes }}
                    @else
                    Sin asignar
                    @endif
                </td>

                <td>{{ $user->created_at }}</td>
                <td>
                    <!-- Botón para abrir el modal de edición -->
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                        Editar
                    </button>

                    <!-- Formulario para eliminar usuario -->
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">
                            Eliminar
                        </button>
                    </form>
                    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editUserModalLabel{{ $user->id }}">Editar Usuario</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('users.update', $user->id) }}" method="POST" id="editUserForm{{ $user->id }}">
                                        @csrf
                                        @method('PUT')

                                        <!-- Nombre -->
                                        <div class="form-group mb-2">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $user->nombre }}" required>
                                        </div>

                                        <!-- Correo Electrónico -->
                                        <div class="form-group mb-2">
                                            <label for="email">Correo Electrónico</label>
                                            <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}" required>
                                        </div>

                                        <!-- Rol -->
                                        <div class="form-group mb-2">
                                            <label for="rol">Rol</label>
                                            <select name="rol" id="rol" class="form-control" required>
                                                <option value="administrador" {{ $user->rol == 'administrador' ? 'selected' : '' }}>Administrador</option>
                                                <option value="usuario_operador" {{ $user->rol == 'usuario_operador' ? 'selected' : '' }}>Usuario Operador</option>
                                                <option value="gestion_ventas" {{ $user->rol == 'gestion_ventas' ? 'selected' : '' }}>Gestión Ventas</option>
                                            </select>
                                        </div>

                                        <!-- Nueva Contraseña (Opcional) -->
                                        <div class="form-group mb-2">
                                            <label for="password">Nueva Contraseña (Opcional)</label>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco si no desea cambiar">
                                        </div>

                                        <!-- Confirmación de Contraseña -->
                                        <div class="form-group mb-3">
                                            <label for="password_confirmation">Confirmar Contraseña</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Reingrese la nueva contraseña">
                                        </div>

                                        <!-- Almacenes Permitidos -->
                                        <div class="form-group">
                                            <label for="almacenes_permitidos">Almacenes Permitidos</label>
                                            <div id="almacenes_permitidos">
                                                @foreach ($almacenes as $almacen)
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="almacenes_permitidos[]"
                                                        value="{{ $almacen->id }}"
                                                        id="almacen_{{ $user->id }}_{{ $almacen->id }}"
                                                        {{ in_array($almacen->id, json_decode($user->almacenes_permitidos ?? '[]')) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="almacen_{{ $user->id }}_{{ $almacen->id }}">
                                                        {{ $almacen->nombre }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <!-- Tipos de Ventas Permitidos -->
                                        <div class="form-group">
                                            <label for="tipos_ventas_permitidos">Tipos de Ventas Permitidos</label>
                                            <div id="tipos_ventas_permitidos">
                                                @foreach ($tiposVentas as $tipo)
                                                <div class="form-check">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="tipos_ventas_permitidos[]"
                                                        value="{{ $tipo->id }}"
                                                        id="tipo_venta_{{ $user->id }}_{{ $tipo->id }}"
                                                        {{ in_array($tipo->id, $user->tipos_ventas_permitidos ?? []) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tipo_venta_{{ $user->id }}_{{ $tipo->id }}">
                                                        {{ $tipo->tipo_venta }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary" form="editUserForm{{ $user->id }}">Guardar Cambios</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal para agregar un nuevo usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
                    @csrf
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select name="rol" id="rol" class="form-control" required>
                            <option value="administrador">Administrador</option>
                            <option value="usuario_operador">Usuario Operador</option>
                            <option value="gestion_ventas">Gestión Ventas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="almacenes_permitidos">Almacenes Permitidos</label>
                        <div id="almacenes_permitidos">
                            @foreach ($almacenes as $almacen)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="almacenes_permitidos[]"
                                    value="{{ $almacen->id }}"
                                    id="almacen_create_{{ $almacen->id }}">
                                <label class="form-check-label" for="almacen_create_{{ $almacen->id }}">
                                    {{ $almacen->nombre }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Tipos de Ventas Permitidos -->
                    <div class="form-group">
                        <label for="tipos_ventas_permitidos">Tipos de Ventas Permitidos</label>
                        <div id="tipos_ventas_permitidos">
                            @foreach ($tiposVentas as $tipo)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="tipos_ventas_permitidos[]"
                                    value="{{ $tipo->id }}"
                                    id="tipo_venta_create_{{ $tipo->id }}">
                                <label class="form-check-label" for="tipo_venta_create_{{ $tipo->id }}">
                                    {{ $tipo->tipo_venta }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="createUserForm">Guardar Usuario</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Función para limpiar el formulario del modal "Agregar Nuevo Usuario"
    function resetCreateUserModal() {
        const createUserForm = document.getElementById('createUserForm');
        if (createUserForm) {
            createUserForm.reset(); // Restablece todos los campos del formulario

            // Restablecer el estado de los checkboxes
            const checkboxes = createUserForm.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false; // Desmarcar todos los checkboxes
            });

            // Limpia mensajes de error o clases de validación si las hubiera
            const errorAlerts = createUserForm.querySelectorAll('.is-invalid');
            errorAlerts.forEach(alert => {
                alert.classList.remove('is-invalid');
            });

            const errorMessages = createUserForm.querySelectorAll('.invalid-feedback');
            errorMessages.forEach(message => {
                message.textContent = '';
            });
        }
    }

    // Evento para llamar a la función cuando se cierra el modal
    document.getElementById('createUserModal').addEventListener('hidden.bs.modal', resetCreateUserModal);
</script>
@else
<br><br>
<div class="alert alert-danger text-center">
    <h4 class="alert-heading">Acceso Denegado</h4>
    <p>No tienes permisos para acceder a esta sección.</p>
</div>

@endif
@endsection