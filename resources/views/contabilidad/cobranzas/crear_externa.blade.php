@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="text-center mb-4">Registrar Cuenta por Cobrar Externa</h3>

    <form method="POST" action="{{ route('contabilidad.cobranzas.guardarExterna') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Categoría</label>
            <input type="text" name="categoria" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Concepto</label>
            <input type="text" name="concepto" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Monto Total (Bs)</label>
            <input type="number" step="0.01" name="monto_total" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Fecha de Vencimiento</label>
            <input type="date" name="fecha_vencimiento" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Estado</label>
            <select name="estado" class="form-select" required>
                <option value="Activo" selected>Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3"></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar Cuenta
            </button>
        </div>
    </form>
</div>
@endsection
