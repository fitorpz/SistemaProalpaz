<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Preventas</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header img { width: 150px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('logoHeader.png') }}" alt="Logo Empresa">
        <h2 style="text-align: center;">Reporte de Preventas</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Cliente</th>
                <th>Fecha Entrega</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preventas as $preventa)
            <tr>
                <td>{{ $preventa->numero_pedido }}</td>
                <td>{{ $preventa->cliente->nombre_comercio ?? 'Sin Cliente' }}</td>
                <td>{{ \Carbon\Carbon::parse($preventa->fecha_entrega)->format('d/m/Y') }}</td>
                <td>{{ $preventa->estado }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
