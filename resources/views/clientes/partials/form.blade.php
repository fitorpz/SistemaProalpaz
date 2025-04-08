{{-- Formulario para Crear Cliente --}}
@if($formType === 'create')
<p class="text-muted">El código del cliente se generará automáticamente.</p>


<div class="form-group mb-3">
    <label for="nombre_propietario">Nombre Propietario</label>
    <input type="text" class="form-control" name="nombre_propietario" id="nombre_propietario" value="{{ old('nombre_propietario') }}" required>
</div>

<div class="form-group mb-3">
    <label for="nombre_comercio">Nombre Comercio</label>
    <input type="text" class="form-control" name="nombre_comercio" id="nombre_comercio" value="{{ old('nombre_comercio') }}" required>
</div>

<div class="form-group mb-3">
    <label for="nit">NIT</label>
    <input type="number" class="form-control" name="nit" id="nit" value="{{ old('nit') }}">
</div>

<div class="form-group mb-3">
    <label for="direccion">Dirección</label>
    <input type="text" class="form-control" name="direccion" id="direccion" value="{{ old('direccion') }}">
</div>

<div class="form-group mb-3">
    <label for="referencia">Referencia</label>
    <input type="text" class="form-control" name="referencia" id="referencia" value="{{ old('referencia') }}">
</div>

<div class="form-group mb-3">
    <label for="ubicacion">Ubicación</label>
    <div class="input-group">
        <input type="text" class="form-control" name="ubicacion" id="ubicacion" value="{{ old('ubicacion') }}" required>
        <button type="button" class="btn btn-outline-secondary" onclick="setLocation()">Usar Mi Ubicación</button>
    </div>
</div>

<div class="form-group mb-3">
    <label for="horario_atencion">Horario de Atención</label>
    <input type="text" class="form-control" name="horario_atencion" id="horario_atencion" value="{{ old('horario_atencion') }}">
</div>

<div class="form-group mb-3">
    <label for="telefono">Teléfono</label>
    <input type="text" class="form-control" name="telefono" id="telefono" value="{{ old('telefono') }}">
</div>

<div class="form-group mb-3">
    <label for="cumpleanos_doctor">Cumpleaños del Doctor</label>
    <input type="date" class="form-control" name="cumpleanos_doctor" id="cumpleanos_doctor" value="{{ old('cumpleanos_doctor') }}">
</div>

<div class="form-group mb-3">
    <label for="horario_visita">Horario de Visita</label>
    <input type="text" class="form-control" name="horario_visita" id="horario_visita" value="{{ old('horario_visita') }}">
</div>

<div class="form-group mb-3">
    <label for="observaciones">Observaciones</label>
    <textarea class="form-control" name="observaciones" id="observaciones" rows="3">{{ old('observaciones') }}</textarea>
</div>

{{-- Campo de Días de Visita en Creación --}}
<div class="form-group mb-3">
    <label for="dia_visita_create">Días de Visita (Máximo 2)</label>
    <div>
        @php
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        @endphp

        @foreach($diasSemana as $dia)
        <div class="form-check form-check-inline">
            <input class="form-check-input dia-visita-checkbox-create" type="checkbox" name="dia_visita[]" value="{{ $dia }}" id="dia_create_{{ $dia }}">
            <label class="form-check-label" for="dia_create_{{ $dia }}">{{ $dia }}</label>
        </div>
        @endforeach
    </div>
    <small class="text-danger" id="dia_visita_error_create" style="display: none;">Solo puedes seleccionar hasta 2 días.</small>
</div>
@endif

{{-- Formulario para Editar Cliente --}}
@if($formType === 'edit')
<div class="form-group mb-3">
    <label for="codigo_cliente">Código Cliente</label>
    <input type="text" class="form-control" name="codigo_cliente" id="codigo_cliente" value="{{ $cliente->codigo_cliente ?? old('codigo_cliente') }}" readonly>
</div>

<div class="form-group mb-3">
    <label for="nombre_propietario">Nombre Propietario</label>
    <input type="text" class="form-control" name="nombre_propietario" id="nombre_propietario" value="{{ $cliente->nombre_propietario ?? old('nombre_propietario') }}" required>
</div>

<div class="form-group mb-3">
    <label for="nombre_comercio">Nombre Comercio</label>
    <input type="text" class="form-control" name="nombre_comercio" id="nombre_comercio" value="{{ $cliente->nombre_comercio ?? old('nombre_comercio') }}" required>
</div>

<div class="form-group mb-3">
    <label for="nit">NIT</label>
    <input type="text" class="form-control" name="nit" id="nit" value="{{ $cliente->nit ?? old('nit') }}">
</div>

<div class="form-group mb-3">
    <label for="direccion">Dirección</label>
    <input type="text" class="form-control" name="direccion" id="direccion" value="{{ $cliente->direccion ?? old('direccion') }}">
</div>

<div class="form-group mb-3">
    <label for="referencia">Referencia</label>
    <input type="text" class="form-control" name="referencia" id="referencia" value="{{ $cliente->referencia ?? old('referencia') }}">
</div>

<div class="form-group mb-3">
    <label for="ubicacion">Ubicación</label>
    <div class="input-group">
        <input type="text" class="form-control" name="ubicacion" id="ubicacion" value="{{ $cliente->ubicacion ?? old('ubicacion') }}" required>
        <button type="button" class="btn btn-outline-secondary" onclick="setLocation()">Usar Mi Ubicación</button>
    </div>
</div>

<div class="form-group mb-3">
    <label for="horario_atencion">Horario de Atención</label>
    <input type="text" class="form-control" name="horario_atencion" id="horario_atencion" value="{{ $cliente->horario_atencion ?? old('horario_atencion') }}">
</div>

<div class="form-group mb-3">
    <label for="telefono">Teléfono</label>
    <input type="text" class="form-control" name="telefono" id="telefono" value="{{ $cliente->telefono ?? old('telefono') }}">
</div>

<div class="form-group mb-3">
    <label for="cumpleanos_doctor">Cumpleaños del Doctor</label>
    <input type="date" class="form-control" name="cumpleanos_doctor" id="cumpleanos_doctor" value="{{ $cliente->cumpleanos_doctor ?? old('cumpleanos_doctor') }}">
</div>

<div class="form-group mb-3">
    <label for="horario_visita">Horario de Visita</label>
    <input type="text" class="form-control" name="horario_visita" id="horario_visita" value="{{ $cliente->horario_visita ?? old('horario_visita') }}">
</div>

<div class="form-group mb-3">
    <label for="observaciones">Observaciones</label>
    <textarea class="form-control" name="observaciones" id="observaciones" rows="3">{{ $cliente->observaciones ?? old('observaciones') }}</textarea>
</div>

<input type="hidden" name="dia_visita_hidden" value="{{ $cliente->dia_visita }}">

{{-- Campo de Días de Visita en Edición --}}
<div class="form-group mb-3">
    <label for="dia_visita_edit">Días de Visita (Máximo 2)</label>
    <div>
        @php
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $diasSeleccionados = isset($cliente) && !empty($cliente->dia_visita) ? array_map('trim', explode(',', $cliente->dia_visita)) : [];
        @endphp

        @foreach($diasSemana as $dia)
        <div class="form-check form-check-inline">
            <input
                class="form-check-input dia-visita-checkbox-edit"
                type="checkbox"
                name="dia_visita[]"
                value="{{ $dia }}"
                id="dia_edit_{{ $dia }}"
                data-dia="{{ $dia }}">
            <label class="form-check-label" for="dia_edit_{{ $dia }}">{{ $dia }}</label>
        </div>
        @endforeach
    </div>
    <small class="text-danger" id="dia_visita_error_edit" style="display: none;">Solo puedes seleccionar hasta 2 días.</small>
</div>



@endif