<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Preventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2 class="text-center">Reporte de Preventas</h2>
    <p><strong>Fecha Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>

    <!-- Encabezado con Filtros Aplicados -->
    <table>
        <tr>
            <th>Filtro</th>
            <th>Valor</th>
        </tr>
        @foreach($filtros as $filtro => $valor)
        <tr>
            <td class="bold">{{ $filtro }}</td>
            <td>{{ $valor }}</td>
        </tr>
        @endforeach
    </table>

    <br>

    <!-- Tabla de Preventas -->
    <table>
        <thead>
            <tr>
                <th>Número de Pedido</th>
                <th>Cliente</th>
                <th>Precio Total</th>
                <th>Observaciones</th>
                <th>Fecha de Entrega</th>
                <th>Preventista</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preventas as $preventa)
            <tr>
                <td>{{ $preventa->numero_pedido }}</td>
                <td>{{ $preventa->cliente->nombre_propietario ?? 'Sin Cliente' }}, {{ $preventa->cliente->nombre_comercio ?? '' }}</td>
                <td class="text-center">{{ number_format($preventa->precio_total, 2) }}</td>
                <td>{{ $preventa->observaciones ?? 'Sin Observaciones' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($preventa->fecha_entrega)->format('d/m/Y') }}</td>
                <td>{{ $preventa->preventista->nombre ?? 'Desconocido' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
