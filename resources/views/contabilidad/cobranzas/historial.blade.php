@extends('layouts.app')

@section('content')



<style>
    /* Estilos generales de la tabla */
    .table-container {
        max-height: 500px;
        overflow-y: auto;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    .table-custom th {
        background-color: rgb(77, 83, 89);
        color: white;
        text-align: center;
        padding: 10px;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .table-custom td {
        text-align: center;
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    .table-custom tr:hover {
        background-color: #f1f1f1;
    }

    .table-custom .text-muted {
        color: #6c757d;
    }

    .btn-custom {
        padding: 4px 8px;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-custom i {
        margin-right: 5px;
    }
</style>

<div class="container">
    <h2 class="text-center">Estado de cuenta del cliente - {{ $cliente->nombre_propietario }} </h2>
    <h2 class="text-center">(En Bolivianos.)</h2>
    <p>Detalle de créditos y abonos:</p>

    <form method="GET" action="{{ route('contabilidad.cobranzas.historial', $cliente->id) }}" class="mb-3">
        <div class="row g-3">
            <!-- 📌 Filtro por Fecha Desde -->
            <div class="col-12 col-md-3">
                <label for="fecha_desde" class="form-label fw-bold">Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                    value="{{ request('fecha_desde') }}">
            </div>

            <!-- 📌 Filtro por Fecha Hasta -->
            <div class="col-12 col-md-3">
                <label for="fecha_hasta" class="form-label fw-bold">Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                    value="{{ request('fecha_hasta') }}">
            </div>

            <!-- 📌 Filtro por Referencia de Crédito -->
            <div class="col-12 col-md-3">
                <label for="filtro_referencia" class="form-label fw-bold">Referencia de Crédito</label>
                <input type="text" name="filtro_referencia" id="filtro_referencia" class="form-control"
                    placeholder="Ingrese referencia..." value="{{ request('filtro_referencia') }}">
            </div>

            <!-- 📌 Filtro por Estado del Crédito -->
            <div class="col-12 col-md-3">
                <label for="estado_credito" class="form-label fw-bold">Estado del Crédito</label>
                <select name="estado_credito" id="estado_credito" class="form-select">
                    <option value="">Todos</option>
                    <option value="Pendiente" {{ request('estado_credito') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Parcialmente Pagado" {{ request('estado_credito') == 'Parcialmente Pagado' ? 'selected' : '' }}>Parcialmente Pagado</option>
                    <option value="Pagado" {{ request('estado_credito') == 'Pagado' ? 'selected' : '' }}>Pagado</option>
                </select>
            </div>
        </div>

        <!-- 📌 Botones de Acción -->
        <div class="row mt-3">
            <div class="col-12 d-flex flex-wrap justify-content-center gap-2">
                <!-- Botón Filtrar -->
                <button type="submit" class="btn btn-primary w-25">
                    <i class="fas fa-filter"></i> Filtrar
                </button>

                <!-- Botón Restablecer -->
                <a href="{{ route('contabilidad.cobranzas.historial', $cliente->id) }}" class="btn btn-secondary w-25">
                    <i class="fas fa-sync-alt"></i> Restablecer
                </a>

                <!-- Botón Generar PDF -->
                <a href="{{ route('contabilidad.cobranzas.generarPDFConFiltros', array_merge(['cliente_id' => $cliente->id], request()->query())) }}"
                    class="btn btn-danger w-25" target="_blank">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </div>
        </div>
    </form>



    <div class="table-container">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Concepto</th>
                    <th>Cargo</th>
                    <th>Pago</th>
                    <th>Saldo</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                    <th>Comprobante</th> <!-- ✅ Nueva columna -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                $saldo_acumulado = 0;
                $preventa_id = null;
                @endphp

                @foreach ($historial as $mov)
                @php
                if ($mov->tipo == 'Crédito') {
                $credito = App\Models\CargosCliente::find($mov->id);
                $preventa_id = $credito ? $credito->preventa_id : null;
                $saldo_acumulado += $mov->cargo;
                }
                if ($mov->tipo == 'Abono') {
                $saldo_acumulado -= $mov->pago;
                }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $mov->tipo }}</td>
                    <td>{{ $mov->referencia }}</td>
                    <td>{{ $mov->concepto }}</td>
                    <td style="text-align: right;">{{ $mov->cargo > 0 ? number_format($mov->cargo, 2) : '-' }}</td>
                    <td style="text-align: right;">{{ $mov->pago > 0 ? number_format($mov->pago, 2) : '-' }}</td>
                    <td style="text-align: right;">{{ number_format($saldo_acumulado, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($mov->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>
                        @if ($mov->tipo == 'Crédito')
                        <span class="badge {{ $mov->estado == 'Pagado' ? 'bg-success' : ($mov->estado == 'Parcialmente Pagado' ? 'bg-warning' : 'bg-danger') }}">
                            {{ $mov->estado }}
                        </span>
                        @elseif ($mov->tipo == 'Abono')
                        <span class="badge bg-info">Abono Aplicado</span>
                        @endif
                    </td>

                    <!-- ✅ Mostrar comprobante si el pago fue con QR -->
                    <td>
                        @if ($mov->metodo_pago == 'QR' && !empty($mov->comprobante_pago))
                        <a href="{{ asset('storage/' . $mov->comprobante_pago) }}" class="btn btn-sm btn-info btn-custom" target="_blank">
                            <i class="fas fa-image"></i> Ver Comprobante
                        </a>
                        @else
                        -
                        @endif
                    </td>

                    <td>
                        @if ($mov->tipo == 'Abono')
                        <a href="{{ route('contabilidad.cobranzas.generarRecibo', ['abono_id' => $mov->id]) }}" class="btn btn-sm btn-primary btn-custom" target="_blank">
                            <i class="fas fa-file-pdf"></i> Ver Recibo
                        </a>

                        @endif

                        @if ($mov->tipo == 'Crédito' && isset($preventa_id))
                        <a href="{{ route('preventas.nota-remision', ['id' => $preventa_id]) }}" class="btn btn-sm btn-outline-secondary btn-custom" target="_blank">
                            <i class="fas fa-file-alt"></i> Nota de Remisión
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold;">Saldo:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($saldo_acumulado, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>



    <!-- Modal para registrar abono -->
    <div class="modal fade" id="modalAbono" tabindex="-1" role="dialog" aria-labelledby="modalAbonoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAbonoLabel">Registrar Abono</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAbono" action="{{ route('contabilidad.cobranzas.registrarAbono') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">

                        <!-- Selección del crédito activo -->
                        <div class="form-group">
                            <label for="cargo_cliente_id">Seleccionar Crédito</label>
                            <select name="cargo_cliente_id" id="cargo_cliente_id" class="form-control" required>
                                <option value="">-- Seleccione un crédito --</option>
                                @foreach ($historial->where('tipo', 'Crédito') as $credito)
                                <option value="{{ $credito->id }}">
                                    {{ $credito->referencia }} - Saldo: {{ number_format($credito->saldo_pendiente, 2) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Monto Abonado -->
                        <div class="form-group">
                            <label for="monto_abonado">Monto Abonado</label>
                            <input type="number" step="0.01" class="form-control" name="monto_abonado" required>
                        </div>

                        <!-- Método de Pago -->
                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-control" required onchange="toggleQR()">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Depósito">Depósito</option>
                                <option value="QR">QR</option> <!-- ✅ Nueva opción -->
                            </select>
                        </div>

                        <!-- Referencia de Pago -->
                        <div class="form-group">
                            <label for="referencia_pago">Referencia</label>
                            <input type="text" class="form-control" name="referencia_pago">
                        </div>

                        <!-- 📌 Código QR (Solo se muestra si se selecciona QR) -->
                        <div id="qrContainer" class="form-group text-center" style="display: none;">
                            <label><strong>Escanea el código QR para pagar:</strong></label>
                            <img src="{{ asset('img/qr-pago.png') }}" alt="Código QR de Pago" style="max-width: 200px; display: block; margin: 10px auto;">
                        </div>

                        <!-- 📌 Campo para subir imagen o tomar foto -->
                        <div id="comprobanteContainer" class="form-group" style="display: none;">
                            <label for="comprobante_pago">Subir Comprobante de Pago</label>
                            <input type="file" name="comprobante_pago" id="comprobante_pago" class="form-control" accept="image/*" capture="environment">

                            <!-- 📌 Vista previa de la imagen seleccionada -->
                            <img id="previewImage" src="#" alt="Vista previa" style="max-width: 100%; display: none; margin-top: 10px; border: 1px solid #ccc; padding: 5px;">
                        </div>




                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnRegistrarAbono">
                            <i class="fas fa-save"></i> Registrar Abono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <br>
    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAbono">
            <i class="fas fa-plus"></i> Registrar Abono
        </button>

        <a href="{{ route('contabilidad.cobranzas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Cobranzas
        </a>
    </div>
</div>
@section('scripts')
<script>
    $(document).ready(function() {
        $('#formAbono').on('submit', function(event) {
            event.preventDefault(); // Evita la recarga del formulario

            let formData = new FormData(this); // ✅ Permite el envío de archivos

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                processData: false, // ✅ Necesario para FormData
                contentType: false, // ✅ Necesario para FormData
                success: function(response) {
                    if (response.success) {
                        // ✅ Cierra el modal después de completar la solicitud
                        $('#modalAbono').modal('hide');

                        // ✅ Abre el PDF automáticamente en una nueva pestaña
                        window.open(response.pdf_url, '_blank');

                        // ✅ Recarga la página después de 1 segundo para actualizar los datos
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('❌ Hubo un error al registrar el abono. Verifique los datos e intente nuevamente.');
                }
            });
        });

        // ✅ Mostrar vista previa de la imagen seleccionada
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
    });

    function toggleQR() {
        let metodoPago = document.getElementById("metodo_pago").value;
        let qrContainer = document.getElementById("qrContainer");
        let comprobanteContainer = document.getElementById("comprobanteContainer");

        if (metodoPago === "QR") {
            qrContainer.style.display = "block";
            comprobanteContainer.style.display = "block";
        } else {
            qrContainer.style.display = "none";
            comprobanteContainer.style.display = "none";
        }
    }
</script>

@endsection


@endsection