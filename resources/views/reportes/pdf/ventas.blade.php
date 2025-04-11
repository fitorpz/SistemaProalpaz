<?php

use Carbon\Carbon;

$fechaGeneracion = Carbon::now()->format('d/m/Y H:i:s');
function numberToWords($number)
{
    if (!class_exists('NumberFormatter')) {
        return 'Extensión intl no habilitada.';
    }

    $parts = explode('.', number_format($number, 2, '.', ''));
    $whole = $parts[0];
    $fraction = $parts[1] ?? '00';

    $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    $literal = $formatter->format($whole);

    return ucfirst($literal) . ' con ' . $fraction . '/100';
}
$logoPath = public_path('logoHeader.png');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .section-title {
            background: #eee;
            font-weight: bold;
            padding: 5px;
        }
    </style>
</head>
<body>
    <table style="width: 100%; margin-bottom: 20px; border: none;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: top; border: none;">
                <img src="{{ $logoPath }}" alt="Logo de la empresa" style="width: 180px; height: auto;">
                <p><strong>Generado el:</strong> {{ $fechaGeneracion }}</p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top; border: none;">
                <strong>PREVENTISTA:</strong> {{ $usuario->nombre ?? 'Todos' }}<br>
                <strong>DEL:</strong> {{ $request->del ?? '---' }}<br>
                <strong>AL:</strong> {{ $request->al ?? '---' }}<br>
                <strong>TOTAL CRÉDITO:</strong> Bs. {{ number_format($totalCredito, 2, ',', '.') }}<br>
                <strong>TOTAL CONTADO:</strong> Bs. {{ number_format($totalContado, 2, ',', '.') }}<br>
                <strong>TOTAL PROMOCIÓN:</strong> Bs. {{ number_format($totalPromocion, 2, ',', '.') }}<br>
                <strong>TOTAL GENERAL:</strong> Bs. {{ number_format($totalCredito + $totalContado + $totalPromocion, 2, ',', '.') }}
            </td>
        </tr>
    </table>
    <div class="title">REPORTE DE VENTAS</div>

    {{-- CRÉDITO --}}
    <p class="section-title">VENTAS CRÉDITO</p>
    <table>
        <thead>
            <tr>
                <th class="text-start">NOTA REMISIÓN</th>
                <th class="text-start">CLIENTE</th>
                <th class="text-start">PRODUCTO</th>
                <th class="text-end">CANTIDAD</th>
                <th class="text-end">MONTO</th>
                <th class="text-start">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasCredito as $item)
            <tr>
                <td class="text-start" style="white-space: nowrap;">{{ $item->preventa->numero_pedido }}</td>
                <td class="text-start">
                    {{ $item->preventa->cliente->nombre_comercio ?? '-' }}<br>
                    <small>{{ $item->preventa->cliente->nombre_propietario ?? '' }}</small>
                </td>
                <td class="text-start">{{ $item->producto->nombre_producto ?? '-' }}</td>
                <td class="text-end">{{ $item->cantidad }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                <td class="text-start">{{ $item->preventa->cargo->estado ?? 'Sin estado' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-start">Sin resultados</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><th colspan="4"></th><th class="text-end">Bs. {{ number_format($totalCredito, 2, ',', '.') }}</th><th></th></tr>
        </tfoot>
    </table>

    {{-- CONTADO --}}
    <p class="section-title">VENTAS CONTADO</p>
    <table>
        <thead>
            <tr>
                <th class="text-start">NOTA REMISIÓN</th>
                <th class="text-start">CLIENTE</th>
                <th class="text-start">PRODUCTO</th>
                <th class="text-end">CANTIDAD</th>
                <th class="text-end">MONTO</th>
                <th class="text-start">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasContado as $item)
            <tr>
                <td class="text-start">{{ $item->preventa->numero_pedido }}</td>
                <td class="text-start">
                    {{ $item->preventa->cliente->nombre_comercio ?? '-' }}<br>
                    <small>{{ $item->preventa->cliente->nombre_propietario ?? '' }}</small>
                </td>
                <td class="text-start">{{ $item->producto->nombre_producto ?? '-' }}</td>
                <td class="text-end">{{ $item->cantidad }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                <td class="text-start">Pagado</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-start">Sin resultados</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><th colspan="4"></th><th class="text-end">Bs. {{ number_format($totalContado, 2, ',', '.') }}</th><th></th></tr>
        </tfoot>
    </table>

    {{-- PROMOCIÓN --}}
    <p class="section-title">VENTAS PROMOCIÓN</p>
    <table>
        <thead>
            <tr>
                <th class="text-start">NOTA REMISIÓN</th>
                <th class="text-start">CLIENTE</th>
                <th class="text-start">PRODUCTO</th>
                <th class="text-end">CANTIDAD</th>
                <th class="text-end">MONTO</th>
                <th class="text-start">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasPromocion as $item)
            <tr>
                <td class="text-start">{{ $item->preventa->numero_pedido }}</td>
                <td class="text-start">
                    {{ $item->preventa->cliente->nombre_comercio ?? '-' }}<br>
                    <small>{{ $item->preventa->cliente->nombre_propietario ?? '' }}</small>
                </td>
                <td class="text-start">{{ $item->producto->nombre_producto ?? '-' }}</td>
                <td class="text-end">{{ $item->cantidad }}</td>
                <td class="text-end">{{ number_format($item->subtotal, 2, ',', '.') }}</td>
                <td class="text-start">Pagado</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-start">Sin resultados</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><th colspan="4"></th><th class="text-end">Bs. {{ number_format($totalPromocion, 2, ',', '.') }}</th><th></th></tr>
        </tfoot>
    </table>
</body>
</html>
