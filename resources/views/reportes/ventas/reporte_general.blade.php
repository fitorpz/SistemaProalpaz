@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3 class="text-center mb-4">Reporte General de Ventas</h3>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="almacen_id" class="form-label">Empresa</label>
            <select name="almacen_id" id="almacen_id" class="form-select">
                <option value="">-- Todos --</option>
                @foreach($almacenes as $almacen)
                <option value="{{ $almacen->id }}" {{ request('almacen_id') == $almacen->id ? 'selected' : '' }}>
                    {{ $almacen->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="mes" class="form-label">Mes</label>
            <select name="mes" id="mes" class="form-select">
                <option value="">-- Todos --</option>
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ request('mes') == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($i)->locale('es')->monthName }}
                    </option>
                    @endfor
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('reportes.ventas.general') }}" class="btn btn-secondary w-100">Restablecer</a>
        </div>



    </form>


    @if(request('mes'))
    <p><strong>MES:</strong> {{ \Carbon\Carbon::create()->month(request('mes'))->locale('es')->monthName }}</p>
    @endif
    <div class="col-md-12 text-end mb-3">
        <a target="_blank" href="{{ route('reportes.ventas.general.pdf', request()->query()) }}" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </a>
    </div>
    @foreach($ventas as $almacen => $vendedores)
    <h5 class="mt-4 text-uppercase">Empresa: {{ $almacen }}</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-secondary">
                <tr>
                    <th class="text-start">Vendedor</th>
                    <th class="text-end">Crédito</th>
                    <th class="text-end">Contado</th>
                    <th class="text-end">Promoción</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                $totalCredito = 0;
                $totalContado = 0;
                @endphp

                @foreach($vendedores as $vendedor => $valores)
                @php
                $subCredito = $valores['credito'];
                $subContado = $valores['contado'];
                $subPromocion = $valores['promocion'];
                $subTotal = $subCredito + $subContado +$subPromocion;
                $totalCredito += $subCredito;
                $totalContado += $subContado;
                $totalPromocion = ($totalPromocion ?? 0) + $subPromocion;
                @endphp
                <tr>
                    <td class="text-start">{{ $vendedor }}</td>
                    <td class="text-end">{{ number_format($subCredito, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($subContado, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($subPromocion, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($subTotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach

                <tr class="fw-bold table-warning">
                    <td class="text-end">Total</td>
                    <td class="text-end">{{ number_format($totalCredito, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($totalContado, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($totalPromocion, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($totalCredito + $totalContado + $totalPromocion, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endsection