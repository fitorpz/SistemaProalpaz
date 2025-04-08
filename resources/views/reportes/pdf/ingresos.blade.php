<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>
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
        <h2 style="text-align: center;">Reporte de Ingresos</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Fecha Vencimiento</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ingresos as $ingreso)
            <tr>
                <td>{{ $ingreso->id }}</td>
                <td>{{ $ingreso->codigo_producto }}</td>
                <td>{{ $ingreso->nombre_producto }}</td>
                <td>{{ $ingreso->cantidad }}</td>
                <td>{{ $ingreso->fecha_vencimiento ?? 'N/A' }}</td>
                <td>{{ ucfirst($ingreso->tipo_ingreso) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
