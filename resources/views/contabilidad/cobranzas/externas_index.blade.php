@extends('layouts.app')

@section('content')
<style>
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ccc;
    }

    .table thead tr {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 100;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }
</style>
<div class="container">
    <h4 class="text-left fw-bold text-secondary mb-4">
        <i class="fas fa-address-book me-2"></i>Cuentas por Cobrar Externas
    </h4>

    <div class="d-flex justify-content-end mb-3">
        <h4 class="fw-bold">
            Total por cobrar: <span id="total-generado">{{ number_format($cuentas->sum('monto_total'), 2) }}</span> Bs.
        </h4>
    </div>

    <!-- 📌 Filtros -->
    <form method="GET" action="{{ route('contabilidad.cobranzas.externas.index') }}" class="mb-3">
        <div class="row g-2 align-items-end">
            <!-- Filtro por nombre o categoría -->
            <div class="col-12 col-md-4">
                <label for="filtro_nombre" class="form-label fw-bold">Buscar por Nombre o Categoría</label>
                <input type="text" name="filtro_nombre" id="filtro_nombre" class="form-control" placeholder="Ej: FARMACIAS..." value="{{ request('filtro_nombre') }}">
            </div>

            <!-- Filtro por Estado -->
            <div class="col-12 col-md-2">
                <label for="estado" class="form-label fw-bold">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="Activo" {{ request('estado') == 'Activo' ? 'selected' : '' }}>Activo</option>
                    <option value="Inactivo" {{ request('estado') == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>

            <!-- Botones de Acción -->
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('contabilidad.cobranzas.externas.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync-alt"></i> Restablecer
                </a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Concepto</th>
                    <th>Monto Total (Bs)</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cuentas as $cuenta)
                <tr>
                    <td>{{ $cuenta->nombre }}</td>
                    <td>{{ $cuenta->categoria ?? '-' }}</td>
                    <td>{{ $cuenta->concepto }}</td>
                    <td>{{ number_format($cuenta->monto_total, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($cuenta->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge bg-{{ $cuenta->estado == 'Activo' ? 'success' : 'secondary' }}">
                            {{ $cuenta->estado }}
                        </span>
                    </td>
                    <td>{{ $cuenta->observaciones ?? '-' }}</td>
                    <td>
                        <a href="{{ route('contabilidad.cobranzas.externas.historial', $cuenta->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No hay registros que coincidan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection