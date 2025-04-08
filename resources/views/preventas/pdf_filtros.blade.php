<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Preventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Reporte de Preventas</h2>

    <!-- Encabezado de Filtros -->
    <p><strong>Filtros Aplicados:</strong></p>
    <ul>
        @foreach($filtros as $clave => $valor)
            <li><strong>{{ $clave }}:</strong> {{ $valor }}</li>
        @endforeach
    </ul>

    <!-- Tabla de Datos -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Empresa</th>
                <th>Preventista</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preventas as $index => $preventa)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $preventa->created_at->format('d/m/Y') }}</td>
                <td>{{ $preventa->cliente->nombre_propietario ?? 'Sin Cliente' }}</td>
                <td>{{ $preventa->almacen->nombre ?? 'Sin Empresa' }}</td>
                <td>{{ $preventa->preventista->nombre ?? 'Sin Preventista' }}</td>
                <td>{{ number_format($preventa->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
