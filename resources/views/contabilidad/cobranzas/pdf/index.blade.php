<?php

use Carbon\Carbon;

// 📌 Fecha y hora actual de generación del PDF
$fechaGeneracion = \Carbon\Carbon::now()->format('d/m/Y H:i:s');

// 📌 Definir las fechas del período consultado con valores reales o vacíos si no se filtra
$fechaDesde = request('fecha_desde') ? \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') : '';
$fechaHasta = request('fecha_hasta') ? \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') : '';

// 📌 Determinar cómo mostrar las fechas en el PDF
$periodoConsultado = ($fechaDesde && $fechaHasta)
    ? "Desde <strong>$fechaDesde</strong> hasta <strong>$fechaHasta</strong>"
    : ($fechaDesde ? "Desde <strong>$fechaDesde</strong>"
        : ($fechaHasta ? "Hasta <strong>$fechaHasta</strong>" : "Todo el historial"));

// 📌 Mantener lógica de logos (solo si existe una preventa válida)
$almacenId = isset($preventa) ? ($preventa->cliente->almacen_id ?? ($preventa->detalles->first()->producto->almacen_id ?? null)) : null;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cobranzas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
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
    <h2 style="text-align: center;">Reporte de Cobranzas</h2>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Monto Total</th>
                <th>Saldo Pendiente</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cargos as $index => $cargo)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $cargo->cliente->nombre_propietario }} - {{ $cargo->cliente->nombre_comercio }}</td>
                <td>{{ number_format($cargo->monto_total, 2) }} Bs.</td>
                <td>{{ number_format($cargo->saldo_pendiente, 2) }} Bs.</td>
                <td>{{ $cargo->estado }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>