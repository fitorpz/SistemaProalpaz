<?php

use Carbon\Carbon;

// 📌 Fecha y hora actual de generación del PDF
$fechaGeneracion = Carbon::now()->format('d/m/Y H:i:s');

// Función para convertir números a letras
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

// 📌 Obtener el almacén del cliente
$almacenId = $abono->cargoCliente->cliente->almacen_id ?? null;

// 📌 Asignar logo según almacén
$almacenLogos = [
    1 => public_path('logoHeader.png'),
    4 => public_path('logoVDN.png'),
];

$logoPath = $almacenLogos[$almacenId] ?? public_path('logoHeader.png');

// 📌 Generar número de recibo correlativo
$numeroRecibo = str_pad($abono->id, 6, '0', STR_PAD_LEFT); // Formato 000001, 000002...

// 📌 Obtener datos del cliente
$clienteNombre = $abono->cargoCliente->cliente->nombre_propietario ?? 'Cliente Desconocido';
$numeroCredito = $abono->numero_credito ?? 'Sin número';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo Oficial de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
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
            max-width: 280px;
            height: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .numero-recibo {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
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
        </div>
        <h3 style="text-align:center;">Recibo Oficial de Pago</h3>
        <p class="numero-recibo">Recibo N°: {{ $numeroRecibo }}</p>
        <p>Fecha: {{ Carbon::parse($abono->fecha_pago)->format('d/m/Y') }}</p>


        <!-- Información del cliente -->
        <table class="table">
            <tr>
                <th>Cliente</th>
                <td>{{ $clienteNombre }}</td>
                <th>Número de Crédito</th>
                <td>{{ $numeroCredito }}</td>
            </tr>
            <tr>
                <th>Método de Pago</th>
                <td>{{ $abono->metodo_pago }}</td>
                <th>Referencia</th>
                <td>{{ $abono->referencia_pago ?? 'N/A' }}</td>
            </tr>
        </table>

        <table class="table">
            <tr>
                <td>
                    <!-- Texto descriptivo -->
                    <p>
                        <strong>Recibí del Señor(a):</strong> {{ $clienteNombre }},
                        la suma de <strong>{{ numberToWords($abono->monto_abonado) }} Bolivianos</strong>,
                        por concepto de abono de crédito N° <strong>{{ $numeroCredito }}</strong>.
                    </p>
                </td>
            </tr>
        </table>

        <!-- Información del pago -->
        <table class="table">
            <thead>
                <tr>
                    <th>Total</th>
                    <th>A Cuenta</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Bs. {{ number_format($abono->cargoCliente->monto_total, 2) }}</td>
                    <td>Bs. {{ number_format($abono->monto_abonado, 2) }}</td>
                    <td>Bs. {{ number_format($abono->saldo_pendiente, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <p><strong>Fecha límite del saldo pendiente:</strong> {{ $fecha_vencimiento }}</p>

    </div>
</body>

</html>