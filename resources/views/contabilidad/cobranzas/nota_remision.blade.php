<?php

function numberToWords($number)
{
    if (!class_exists('NumberFormatter')) {
        return 'La extensión intl no está habilitada en el servidor.';
    }

    $parts = explode('.', number_format($number, 2, '.', ''));
    $whole = $parts[0];
    $fraction = $parts[1] ?? '00';

    $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    $literal = $formatter->format($whole);

    return ucfirst($literal) . ' con ' . $fraction . '/100';
}
// Obtener el almacén de la preventa
$almacenId = $preventa->cliente->almacen_id ?? ($preventa->detalles->first()->producto->almacen_id ?? null);

$almacenLogos = [
    1 => public_path('logoHeader.png'),
    4 => public_path('logoVDN.png'),
];
// Determinar el logo según el almacén (si no hay coincidencia, usa 'logoHeader.png')
$logoPath = $almacenLogos[$almacenId] ?? public_path('logoHeader.png');
?>

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Remisión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header,
        .footer {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 150px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .align-left {
            text-align: left;
        }

        .align-right {
            text-align: right;
        }

        .row {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Logo de la empresa --}}
        <div class="header">
            <img src="{{ $logoPath }}" alt="Logo de la empresa" style="width: 280px; height: auto;">
            <h3>Nota de Remisión</h3>
            <p>{{ $fecha }}</p>
        </div>

        {{-- Datos de la preventa --}}
        <table class="table">
            <tr>
                <th>Cliente</th>
                <td>{{ $preventa->cliente->nombre_comercio }}</td>
                <th>Fecha Preventa</th>
                <td>{{ $preventa->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Vendedor</th>
                <td>{{ $preventa->preventista->nombre ?? 'No especificado' }}</td>
                <th>Código Preventa</th>
                <td>{{ $preventa->numero_pedido }}</td>
            </tr>
            <tr>
                <th>Modalidad de Pago</th>
                <td>{{ $modalidadPago }}</td> <!-- ✅ Agregado -->
                <th>Observaciones</th>
                <td>{{ $preventa->observaciones ?? 'Sin observaciones' }}</td>
            </tr>
        </table>
        {{-- Detalles de la preventa --}}
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Fecha de vencimiento</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                <tr>
                    <td>{{ $detalle['codigo'] }}</td>
                    <td>{{ $detalle['producto'] }}</td>
                    <td>{{ $detalle['lote'] }}</td>
                    <td>{{ $detalle['fecha_vencimiento'] }}</td>
                    <td>{{ $detalle['cantidad'] }}</td>
                    <td>{{ $detalle['precio_unitario'] }}</td>
                    <td>{{ $detalle['subtotal'] }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="6">TOTAL Bs.: {{ numberToWords($preventa->detalles->sum('subtotal')) }} </td>
                    <td>{{ number_format($preventa->detalles->sum('subtotal'), 2) }}</td>
                </tr>
            </tbody>
        </table><br>
        @if($preventa->descuento > 0)
        {{-- **Desglose del total con descuento (Solo se muestra si aplica)** --}}
        <table class="table">
            <tr>
                <th>Subtotal Bs.</th>
                <td>{{ number_format($preventa->detalles->sum('subtotal'), 2) }}</td>
            </tr>


            <tr>
                <th>Descuento Aplicado ({{ $preventa->descuento }}%)</th>
                <td>- {{ number_format(($preventa->detalles->sum('subtotal') * $preventa->descuento) / 100, 2) }}</td>
            </tr>


            <tr class="total-row">
                <th>TOTAL FINAL Bs.</th>
                <td><strong>{{ number_format($preventa->precio_total, 2) }}</strong></td>
            </tr>
            <tr>
                <th>Total en Letras</th>
                <td>{{ numberToWords($preventa->precio_total) }}</td>
            </tr>
        </table>
        @endif
        {{-- Firma --}}
        <div class="row" style="display: flex; justify-content: space-between; margin-top: 50px;">
            <!-- Columna izquierda -->
            <div class="col-md-6" style="width: 45%; text-align: center; margin-left: 50px">
                <p style="margin-bottom: 5px;">________________________</p>
                <p>Entregué Conforme</p>
            </div>
            <!-- Columna derecha -->
            <div class="col-md-6" style="width: 45%; text-align: center; margin-top: -80px; margin-left: 300px">
                <p style="margin-bottom: 5px;">________________________</p>
                <p>Recibí Conforme</p>
            </div>
        </div>

    </div>
</body>

</html>