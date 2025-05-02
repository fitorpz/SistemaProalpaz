<?php

use Carbon\Carbon;

$fechaGeneracion = Carbon::now()->format('d/m/Y H:i:s');
$logoPath = public_path('logoHeader.png');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte General de Ventas</title>
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

        th,
        td {
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
                @if(request('mes'))
                <strong>MES:</strong> {{ \Carbon\Carbon::create()->month(request('mes'))->locale('es')->isoFormat('MMMM') }}<br>
                @endif
                @if(request('almacen_id'))
                <strong>EMPRESA:</strong> {{ $almacenNombre ?? '---' }}<br>
                @endif
            </td>
        </tr>
    </table>

    <div class="title">REPORTE GENERAL DE VENTAS</div>

    @foreach($ventas as $almacen => $vendedores)
    <p class="section-title">ALMACÉN: {{ strtoupper($almacen) }}</p>
    <table>
        <thead>
            <tr>
                <th class="text-start">Vendedor</th>
                <th class="text-end">Crédito</th>
                <th class="text-end">Contado</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalCredito = 0; $totalContado = 0; @endphp
            @foreach($vendedores as $vendedor => $valores)
                @php
                    $subCredito = $valores['credito'];
                    $subContado = $valores['contado'];
                    $subTotal = $subCredito + $subContado;
                    $totalCredito += $subCredito;
                    $totalContado += $subContado;
                @endphp
                <tr>
                    <td class="text-start">{{ $vendedor }}</td>
                    <td class="text-end">{{ number_format($subCredito, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($subContado, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($subTotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9e79f;">
                <td class="text-end">Total</td>
                <td class="text-end">{{ number_format($totalCredito, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalContado, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($totalCredito + $totalContado, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    @endforeach
</body>

</html>
