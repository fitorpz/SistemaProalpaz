<?php

use Carbon\Carbon;

// 📌 Fecha y hora actual de generación del PDF
$fechaGeneracion = Carbon::now()->format('d/m/Y H:i:s');
function numberToWords($number)
{
    if (!class_exists('NumberFormatter')) {
        return 'La extensión intl no está habilitada en el servidor.';
    }

    // Separar la parte entera y decimal del número
    $parts = explode('.', number_format($number, 2, '.', ''));
    $whole = $parts[0]; // Parte entera
    $fraction = $parts[1] ?? '00'; // Parte decimal (asegurando 2 dígitos)

    // Convertir solo la parte entera a palabras
    $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    $literal = $formatter->format($whole);

    // Devolver el número en palabras con el decimal en formato `/100`
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


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Preventa - {{ $preventa->numero_pedido }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 350px;
            /* Ajusta el tamaño del logo */
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 0;
            font-size: 12px;
        }

        .details,
        .totals {
            width: 100%;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .details th {
            background-color: #f4f4f4;
        }

        .totals {
            margin-top: 20px;
        }

        /* Estilos para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .total-row td {
            font-weight: bold;
            text-align: right;
            background-color: #f9f9f9;
        }

        .product-name {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>

                <td style="width: 50%; text-align: left; vertical-align: middle; border: none;">
                    <img src="{{ $logoPath }}" alt="Logo de la empresa" style="width: 180px; height: auto;">
                    <p style="margin: 0; font-size: 12px; font-weight: bold;">NIT: 123123123</p>
                    <p style="margin: 0; font-size: 12px; font-weight: bold;">Calle Juan Manuel Carpio N° 275</p>
                    <p style="margin: 0; font-size: 12px;">Zona Los Andes El Alto</p>
                    <p style="margin: 0; font-size: 12px;">Cel.: 77246463 - 65178769</p>
                    <p><strong>Generado el:</strong> {{ $fechaGeneracion }}</p>
                </td>

            </tr>
        </table>
        <h2 style="text-align: center;">FORMULARIO DE PEDIDOS</h2>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <th>No. Pedido</th>
                <td>{{ $preventa->numero_pedido }}</td>
                <th>Fecha de Entrega</th>
                <td>{{ \Carbon\Carbon::parse($preventa->fecha_entrega)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Modalidad de Pago</th>
                <td>{{ $modalidadPago }}</td>
                <th>Fecha de Pago</th>
                <td>{{ \Carbon\Carbon::parse($fechaPago)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Vendedor</th>
                <td colspan="3">{{ $formaPedido }}</td>
            </tr>
        </table>
    </div>

    <!-- Información del Cliente -->
    <table>
        <tr>
            <th>Cliente</th>
            <td>{{ $preventa->cliente->nombre_comercio }}</td>
            <th>Teléfono</th>
            <td>{{ $preventa->cliente->telefono }}</td>
        </tr>
        <tr>
            <th>Dirección</th>
            <td>{{ $preventa->cliente->direccion }}</td>
            <th>C.I./NIT</th>
            <td>{{ $preventa->cliente->nit }}</td>
        </tr>
    </table>

    <!-- Tabla de Detalles -->
    <table>
        <thead>
            <tr>
                <th>CANT.</th>
                <th>PRODUCTO</th>
                <th>UNIDAD</th>
                <th>P.UNIT.</th>
                <th>P.TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
            <tr>
                <td>{{ $detalle['cantidad'] }}</td>
                <td class="product-name">{{ $detalle['producto'] }}</td>
                <td>{{ $detalle['unidad'] }}</td>
                <td>{{ $detalle['precio_unitario'] }}</td>
                <td>{{ $detalle['subtotal'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4">TOTAL Bs.: {{ numberToWords($preventa->detalles->sum('subtotal')) }} </td>
                <td>{{ number_format($preventa->detalles->sum('subtotal'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @if($preventa->descuento > 0)
    <!-- 🏷 **Sección de Totales y Descuento** -->
    <table>
        <tr>
            <th style="text-align: right;">Subtotal Bs.:</th>
            <td style="text-align: right;">{{ number_format($preventa->detalles->sum('subtotal'), 2) }}</td>
        </tr>


        <tr>
            <th style="text-align: right;">Descuento Aplicado ({{ $preventa->descuento }}%):</th>
            <td style="text-align: right;">- {{ number_format(($preventa->detalles->sum('subtotal') * $preventa->descuento) / 100, 2) }}</td>
        </tr>


        <tr class="total-row">
            <th style="text-align: right;">TOTAL FINAL Bs.:</th>
            <td style="text-align: right;"><strong>{{ number_format($preventa->precio_total, 2) }}</strong></td>
        </tr>

        <tr>
            <th style="text-align: right;">Total en Letras:</th>
            <td style="text-align: right;">{{ numberToWords($preventa->precio_total) }}</td>
        </tr>
    </table>
    @endif
    <!-- Nota -->
    <p style="margin-top: 20px;"><strong>Nota:</strong> En caso de incumplimiento se procederá a sanción de acuerdo a contrato.</p>
</body>

</html>