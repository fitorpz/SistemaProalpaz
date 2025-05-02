@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3 class="text-center mb-4">REPORTE DE VENTAS</h3>

    <!-- Filtros -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="usuario_id" class="form-label">Preventista</label>
            <select name="usuario_id" id="usuario_id" class="form-select">
                <option value="">-- Todos --</option>
                @foreach($usuarios as $usuario)
                <option value="{{ $usuario->id }}" {{ request('usuario_id') == $usuario->id ? 'selected' : '' }}>
                    {{ $usuario->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="almacen_id" class="form-label">Almacén</label>
            <select name="almacen_id" id="almacen_id" class="form-select">
                <option value="">-- Todos --</option>
                @foreach($almacenes as $almacen)
                <option value="{{ $almacen->id }}" {{ request('almacen_id') == $almacen->id ? 'selected' : '' }}>
                    {{ $almacen->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="mes" class="form-label">Mes</label>
            <select name="mes" id="mes" class="form-select">
                <option value="">-- Todos --</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ request('mes') == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($i)->locale('es')->isoFormat('MMMM') }}
                    </option>
                    @endfor
            </select>
        </div>

        <div class="col-md-3">
            <label for="del" class="form-label">Desde</label>
            <input type="date" name="del" id="del" class="form-control" value="{{ request('del') }}">
        </div>
        <div class="col-md-3">
            <label for="al" class="form-label">Hasta</label>
            <input type="date" name="al" id="al" class="form-control" value="{{ request('al') }}">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <a href="{{ route('reportes.ventas.detallado') }}" class="btn btn-secondary w-100">Restablecer</a>
        </div>

    </form>


    <div class="col-md-12 text-end mb-3">
        <a target="_blank" href="{{ route('reportes.ventas.pdf', request()->query()) }}" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
    </div>

    <!-- Resumen -->
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <strong>PREVENTISTA:</strong>
            {{ $usuarios->firstWhere('id', request('usuario_id'))->nombre ?? 'Todos' }}<br>
            <strong>DEL:</strong> {{ request('del') ?? '---' }}<br>
            <strong>AL:</strong> {{ request('al') ?? '---' }}
        </div>
        <div>
            <strong>TOTAL VENTAS CRÉDITO:</strong> Bs. {{ number_format($totalCredito, 2, ',', '.') }}<br>
            <strong>TOTAL VENTAS CONTADO:</strong> Bs. {{ number_format($totalContado, 2, ',', '.') }}<br>
            <strong>TOTAL VENTAS PROMOCIÓN:</strong> Bs. {{ number_format($totalPromocion, 2, ',', '.') }}<br>
            <strong>TOTAL GENERAL:</strong> Bs. {{ number_format($totalCredito + $totalContado + $totalPromocion, 2, ',', '.') }}
        </div>
    </div>

    <!-- Tabla Crédito -->
    <h5 class="mt-4">VENTAS CRÉDITO</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-secondary">
            <tr>
                <th class="text-start" style="white-space: nowrap; width: 1%;">NOTA DE REMISIÓN</th>
                <th class="text-start">CLIENTE</th>
                <th class="text-start">PRODUCTO</th>
                <th class="text-end">CANTIDAD</th>
                <th class="text-end">MONTO</th>
                <th class="text-start">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasCredito as $detalle)
            <tr>
                <td class="text-start" style="white-space: nowrap;">{{ $detalle->preventa->numero_pedido }}</td>
                <td class="text-start">
                    {{ $detalle->preventa->cliente->nombre_comercio ?? '-' }}<br>
                    <small class="text-muted">{{ $detalle->preventa->cliente->nombre_propietario ?? '' }}</small>
                </td>

                <td class="text-start">{{ $detalle->producto->nombre_producto ?? 'Sin nombre' }}</td>
                <td class="text-end">{{ $detalle->cantidad }}</td>
                <td class="text-end">{{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
                <td class="text-start">{{ $detalle->preventa->cargo->estado ?? 'Sin estado' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Sin resultados</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4"></th>
                <th class="text-end">Bs. {{ number_format($totalCredito, 2, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>


    <!-- Tabla Contado -->
    <h5 class="mt-5">VENTAS CONTADO</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-secondary">
            <tr>
                <th class="text-start" style="white-space: nowrap; width: 1%;">NOTA DE REMISIÓN</th>
                <th class="text-start">CLIENTE</th>
                <th class="text-start">PRODUCTO</th>
                <th class="text-end">CANTIDAD</th>
                <th class="text-end">MONTO</th>
                <th class="text-start">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasContado as $detalle)
            <tr>
                <td class="text-start" style="white-space: nowrap;">{{ $detalle->preventa->numero_pedido }}</td>
                <td class="text-start">
                    {{ $detalle->preventa->cliente->nombre_comercio ?? '-' }}<br>
                    <small class="text-muted">{{ $detalle->preventa->cliente->nombre_propietario ?? '' }}</small>
                </td>
                <td class="text-start">{{ $detalle->producto->nombre_producto ?? 'Sin nombre' }}</td>
                <td class="text-end">{{ $detalle->cantidad }}</td>
                <td class="text-end">{{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
                <td class="text-start">Pagado</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Sin resultados</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4"></th>
                <th class="text-end">Bs. {{ number_format($totalContado, 2, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>



    <!-- Tabla Promoción -->
    <h5 class="mt-5">VENTAS PROMOCIÓN</h5>
    <table class="table table-bordered table-sm">
        <thead class="table-secondary">
            <tr>
                <th class="text-start" style="white-space: nowrap; width: 1%;">NOTA DE REMISIÓN</th>
                <th class="text-start">CLIENTE</th>
                <th class="text-start">PRODUCTO</th>
                <th class="text-end">CANTIDAD</th>
                <th class="text-end">MONTO</th>
                <th class="text-start">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasPromocion as $detalle)
            <tr>
                <td class="text-start" style="white-space: nowrap;">{{ $detalle->preventa->numero_pedido }}</td>
                <td class="text-start">
                    {{ $detalle->preventa->cliente->nombre_comercio ?? '-' }}<br>
                    <small class="text-muted">{{ $detalle->preventa->cliente->nombre_propietario ?? '' }}</small>
                </td>
                <td class="text-start">{{ $detalle->producto->nombre_producto ?? 'Sin nombre' }}</td>
                <td class="text-end">{{ $detalle->cantidad }}</td>
                <td class="text-end">{{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
                <td class="text-start">Pagado</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Sin resultados</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4"></th>
                <th class="text-end">Bs. {{ number_format($totalPromocion, 2, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>


</div>
@endsection