@extends('layouts.app')

@section('content')

<style>
    .card {
        border-radius: 10px;
    }

    .form-section {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    #qrContainer img {
        max-width: 200px;
        margin: 10px auto;
        display: block;
    }

    #previewImage {
        max-width: 100%;
        display: none;
        margin-top: 10px;
        border: 1px solid #ccc;
        padding: 5px;
    }
</style>

<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-cash-register me-2"></i>Registrar Abono
    </h4>

    <div class="form-section">
        <!-- 🔍 Autocompletado de Cliente -->
        <div class="mb-3">
            <label for="cliente_autocomplete" class="form-label fw-bold">Buscar Cliente</label>
            <input type="text" id="cliente_autocomplete" class="form-control" placeholder="Nombre o Comercio...">

        </div>

        <div id="formulario-abono" style="display:none;">
            <form id="formAbono" action="{{ route('contabilidad.cobranzas.registrarAbono') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="cliente_id" name="cliente_id">
                <!-- Crédito activo -->
                <div class="mb-3">
                    <label for="cargo_cliente_id" class="form-label">Seleccionar Crédito</label>
                    <select name="cargo_cliente_id" id="cargo_cliente_id" class="form-control" required>
                        <option value="">-- Seleccione un crédito --</option>
                    </select>
                </div>

                <!-- Monto Abonado -->
                <div class="mb-3">
                    <label for="monto_abonado" class="form-label">Monto Abonado</label>
                    <input type="number" step="0.01" class="form-control" name="monto_abonado" required>
                </div>

                <!-- Método de Pago -->
                <div class="mb-3">
                    <label for="metodo_pago" class="form-label">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago" class="form-control" required onchange="toggleQR()">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Depósito">Depósito</option>
                        <option value="QR">QR</option>
                    </select>
                </div>

                <!-- Referencia -->
                <div class="mb-3">
                    <label for="referencia_pago" class="form-label">Referencia</label>
                    <input type="text" name="referencia_pago" class="form-control">
                </div>

                <!-- Código QR -->
                <div id="qrContainer" class="text-center mb-3" style="display:none;">
                    <label><strong>Escanea el código QR:</strong></label>
                    <img src="{{ asset('img/qr-pago.png') }}" alt="QR de pago">
                </div>

                <!-- Comprobante -->
                <div id="comprobanteContainer" class="mb-3" style="display:none;">
                    <label for="comprobante_pago">Subir Comprobante</label>
                    <input type="file" name="comprobante_pago" id="comprobante_pago" class="form-control" accept="image/*" capture="environment">
                    <img id="previewImage" src="#" alt="Vista previa">
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('contabilidad.cobranzas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Registrar Abono
                    </button>
                </div>
            </form>
        </div>
        <!-- 🔴 Alerta si no tiene créditos activos -->
        <div id="mensaje-sin-creditos" class="alert alert-warning text-center" style="display: none;">
            <i class="fas fa-exclamation-circle"></i> El cliente seleccionado no tiene créditos activos.
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleQR() {
        const metodo = document.getElementById('metodo_pago').value;
        document.getElementById('qrContainer').style.display = metodo === 'QR' ? 'block' : 'none';
        document.getElementById('comprobanteContainer').style.display = metodo === 'QR' ? 'block' : 'none';
    }

    // Vista previa de imagen
    $('#comprobante_pago').on('change', function(event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImage').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // 🔍 Autocompletado de cliente
    $(document).ready(function() {
        $("#cliente_autocomplete").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('cobranzas.buscarClientes') }}",
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(cliente) {
                            return {
                                label: `${cliente.nombre_propietario} - ${cliente.nombre_comercio}`,
                                value: cliente.nombre_propietario,
                                id: cliente.id
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                console.log("✅ Cliente seleccionado:", ui.item); // 👈 DEBUG

                $('#cliente_id').val(ui.item.id);
                $('#cliente_autocomplete').val(ui.item.label);

                $.getJSON(`/api/cliente/${ui.item.id}/creditos`, function(data) {
                    console.log("📦 Créditos del cliente:", data); // 👈 DEBUG

                    let select = $('#cargo_cliente_id');
                    select.empty().append('<option value="">-- Seleccione un crédito --</option>');

                    if (data.length === 0) {
                        select.append('<option disabled>Este cliente no tiene créditos activos</option>');

                        $('#formulario-abono').hide();
                        $('#mensaje-sin-creditos').show();
                    } else {
                        data.forEach(function(c) {
                            select.append(`<option value="${c.id}">${c.numero_credito} - Saldo: ${parseFloat(c.saldo_pendiente).toFixed(2)}</option>`);
                        });

                        $('#mensaje-sin-creditos').hide();
                        $('#formulario-abono').show();
                    }
                });

                return false;
            }

        });
    });


    // Envío del formulario con AJAX
    $('#formAbono').on('submit', function(event) {
        event.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.open(response.pdf_url, '_blank');
                    setTimeout(() => location.reload(), 1000);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText); // 👈 Te mostrará detalles del error 422

                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    let mensajes = Object.values(errors).flat().join('\n');
                    alert(`❌ Error al registrar abono:\n${mensajes}`);
                } else {
                    alert('❌ Error al registrar abono. Revisa los datos.');
                }
            }

        });
    });
</script>
@endsection