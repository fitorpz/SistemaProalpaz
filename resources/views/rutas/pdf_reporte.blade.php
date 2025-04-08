<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Rutas Programadas</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .badge-success { color: green; font-weight: bold; }
        .badge-danger { color: red; font-weight: bold; }
    </style>
</head>
<body>

    <h2 class="text-center">Reporte de Rutas Programadas</h2>
    <p><strong>Fecha Generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>

    <table>
        <tr>
            <th>Filtro</th>
            <th>Valor</th>
        </tr>
        <tr>
            <td>Fecha</td>
            <td>{{ $filtros['Fecha'] }}</td>
        </tr>
        <tr>
            <td>Usuario</td>
            <td>{{ $filtros['Usuario'] }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Dirección</th>
                <th>Estado</th>
                <th>Ubicación</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td>{{ $cliente->nombre_comercio }} - {{ $cliente->nombre_propietario }}</td>
                <td>{{ $cliente->direccion }}</td>
                <td class="text-center">
                    @if($cliente->visitas->isNotEmpty())
                        <span class="badge-success">✔ Registrada</span>
                    @else
                        <span class="badge-danger">✘ No Registrada</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($cliente->visitas->isNotEmpty() && $cliente->visitas->first()->ubicacion)
                        <a href="{{ $cliente->visitas->first()->ubicacion }}" target="_blank">Ver Ubicación</a>
                    @else
                        No disponible
                    @endif
                </td>
                <td>{{ $cliente->visitas->first()->observaciones ?? 'Sin observaciones' }}</td>
                <td class="text-center">
                    @if($cliente->visitas->isEmpty())
                        <span class="badge-danger">No registrada</span>
                    @else
                        <span class="badge-success">{{ $cliente->visitas->first()->created_at->format('H:i') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
