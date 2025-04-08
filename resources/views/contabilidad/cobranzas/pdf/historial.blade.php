<?php

use Carbon\Carbon;

// 📌 Fecha y hora actual de generación del PDF
$fechaGeneracion = Carbon::now()->format('d/m/Y H:i:s');

// 📌 Definir las fechas del período consultado con valores reales o vacíos si no se filtra
$fechaDesde = request('fecha_desde') ? \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') : '';
$fechaHasta = request('fecha_hasta') ? \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') : '';

// 📌 Determinar cómo mostrar las fechas en el PDF
$periodoConsultado = ($fechaDesde && $fechaHasta)
    ? "Desde <strong>$fechaDesde</strong> hasta <strong>$fechaHasta</strong>"
    : ($fechaDesde ? "Desde <strong>$fechaDesde</strong>"
        : ($fechaHasta ? "Hasta <strong>$fechaHasta</strong>" : "Todo el historial"));

// 📌 Mantener lógica de logos
$almacenId = $preventa->cliente->almacen_id ?? ($preventa->detalles->first()->producto->almacen_id ?? null);
$almacenLogos = [
    1 => public_path('logoHeader.png'),
    4 => public_path('logoVDN.png'),
];
$logoPath = $almacenLogos[$almacenId] ?? public_path('logoHeader.png');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>ESTADO DE CUENTA DEL CLIENTE</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>

    <?php
    function numberToWords($number)
    {
        if (!class_exists('NumberFormatter')) {
            return 'La extensión intl no está habilitada en el servidor.';
        }

        // Separar la parte entera y decimal
        $parts = explode('.', number_format($number, 2, '.', ''));
        $whole = $parts[0]; // Parte entera
        $fraction = $parts[1] ?? '00'; // Parte decimal

        // Convertir la parte entera a palabras
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
    // Determinar el logo según almacén (default: 'logoHeader.png')
    $logoPath = $almacenLogos[$almacenId] ?? public_path('logoHeader.png');

    // 📌 Obtener fechas exactas del filtro
    $fechaDesde = request('fecha_desde') ? \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') : 'Inicio';
    $fechaHasta = request('fecha_hasta') ? \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') : 'Hoy';
    ?>

    <!-- Encabezado con Logo y Datos de la Empresa -->
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

    <h2 style="text-align: center;">ESTADO DE CUENTA DEL CLIENTE</h2>
    <h2 style="text-align: center;">(En Bolivianos.)</h2>

    <p><strong>Cliente:</strong> {{ $cliente->nombre_propietario }}</p>
    <p><strong>Preventista:</strong> {{ $preventa && $preventa->preventista ? $preventa->preventista->nombre : 'No disponible' }}</p>
    <p><strong>Periodo consultado:</strong> {!! $periodoConsultado !!}</p> <!-- 📌 Ahora muestra exactamente las fechas reales -->

    <table>
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
            </tr>
        </thead>
        <tbody>
            <?php $saldo_acumulado = 0; ?>
            @foreach ($historial as $mov)
            <?php
            if ($mov->tipo == 'Crédito') {
                $saldo_acumulado += $mov->cargo;
            }
            if ($mov->tipo == 'Abono') {
                $saldo_acumulado -= $mov->pago;
            }
            ?>
            <tr>
                <td>{{ Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                <td>{{ $mov->tipo }}</td>
                <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">{{ $mov->referencia }}</td>
                <td style="text-align: left;">{{ $mov->concepto }}</td>
                <td style="text-align: right;">{{ $mov->cargo > 0 ? number_format($mov->cargo, 2) : '-' }}</td>
                <td style="text-align: right;">{{ $mov->pago > 0 ? number_format($mov->pago, 2)  : '-' }}</td>
                <td style="text-align: right;">{{ number_format($saldo_acumulado, 2)  }}</td>
                <td>{{ Carbon::parse($mov->fecha_vencimiento)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: left; font-weight: bold;">Saldo:</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($saldo_acumulado, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>