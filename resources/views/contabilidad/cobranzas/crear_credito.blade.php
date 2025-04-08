@extends('layouts.app')

@section('content')
<style>
    .card {
        border-radius: 10px;
    }

    .form-section {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }
</style>

<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-file-invoice me-2"></i>Registrar Cuenta por Cobrar
    </h4>

    <div class="form-section">
        <form action="{{ route('contabilidad.cobranzas.guardarCredito') }}" method="POST">
            @csrf

            <!-- Autocompletado de Cliente -->
            <div class="mb-3">
                <label for="cliente_autocomplete" class="form-label fw-bold">Buscar Cliente</label>
                <input type="text" id="cliente_autocomplete" class="form-control" placeholder="Nombre o Comercio..." required>
                <input type="hidden" name="cliente_id" id="cliente_id">
            </div>

            <!-- Monto del Crédito -->
            <div class="mb-3">
                <label for="monto_total" class="form-label fw-bold">Monto Total</label>
                <input type="number" name="monto_total" id="monto_total" class="form-control" step="0.01" required>
            </div>

            <!-- Días de Crédito -->
            <div class="mb-3">
                <label for="dias_credito" class="form-label fw-bold">Días de Crédito</label>
                <input type="number" name="dias_credito" id="dias_credito" class="form-control" value="15" min="1">
            </div>

            <!-- Concepto -->
            <div class="mb-3">
                <label for="concepto" class="form-label fw-bold">Concepto</label>
                <textarea name="concepto" id="concepto" class="form-control" rows="3" placeholder="Ej: Venta de productos a crédito" required></textarea>
            </div>

            <!-- Botones -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('contabilidad.cobranzas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Registrar Crédito
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        console.log("✅ Autocomplete cargado para registrar crédito manual");

        $('#cliente_autocomplete').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('cobranzas.buscarTodos') }}",
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: `${item.nombre_propietario} - ${item.nombre_comercio}`,
                                value: item.nombre_propietario,
                                id: item.id
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#cliente_autocomplete').val(ui.item.label);
                $('#cliente_id').val(ui.item.id);
                return false;
            }
        });
    });
</script>
@endsection