<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clientes</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Reporte de Clientes</h2>
    <p><strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Filtro</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filtros as $key => $value)
                <tr>
                    <td><strong>{{ $key }}</strong></td>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Propietario</th>
                <th>Comercio</th>
                <th>Día de Visita</th>
                <th>Teléfono</th>
                <th>Dirección</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->codigo_cliente }}</td>
                    <td>{{ $cliente->nombre_propietario }}</td>
                    <td>{{ $cliente->nombre_comercio }}</td>
                    <td>{{ $cliente->dia_visita }}</td>
                    <td>{{ $cliente->telefono ?? 'Sin Teléfono' }}</td>
                    <td>{{ $cliente->direccion ?? 'Sin Dirección' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
