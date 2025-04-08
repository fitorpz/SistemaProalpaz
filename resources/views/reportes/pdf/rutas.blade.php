<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Rutas</title>
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
        <h2 style="text-align: center;">Reporte de Rutas</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Preventista</th>
                <th>Fecha Visita</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rutas as $ruta)
            <tr>
                <td>{{ $ruta->id }}</td>
                <td>{{ $ruta->cliente->nombre_comercio ?? 'Sin Cliente' }}</td>
                <td>{{ $ruta->preventista->nombre ?? 'Sin Preventista' }}</td>
                <td>{{ \Carbon\Carbon::parse($ruta->fecha_visita)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
