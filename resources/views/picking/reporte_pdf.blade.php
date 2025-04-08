<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Picking & Packing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 250px;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .product-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .product-list li {
            padding: 3px 0;
            text-align: left;
        }

        .no-products {
            color: #888;
            font-style: italic;
        }

        /* Colores para los estados */
        .status-pendiente {
            background-color: #ffcc00; /* Amarillo */
            font-weight: bold;
            padding: 5px;
            display: inline-block;
            border-radius: 5px;
        }

        .status-preparado {
            background-color: #17a2b8; /* Azul */
            color: white;
            font-weight: bold;
            padding: 5px;
            display: inline-block;
            border-radius: 5px;
        }

        .status-entregado {
            background-color: #28a745; /* Verde */
            color: white;
            font-weight: bold;
            padding: 5px;
            display: inline-block;
            border-radius: 5px;
        }

        .status-no-entregado {
            background-color: #dc3545; /* Rojo */
            color: white;
            font-weight: bold;
            padding: 5px;
            display: inline-block;
            border-radius: 5px;
        }

        .obs-text {
            font-size: 12px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Encabezado con Logo y Datos -->
    <div class="header">
        <table style="width: 100%; margin-bottom: 20px; border: none;">
            <tr>
                <!-- Logo de la empresa -->
                <td style="width: 50%; text-align: left; vertical-align: middle; border: none;">
                    <img src="{{ public_path('logoHeader.png') }}" alt="Logo de la empresa">
                </td>

                <!-- Información de la empresa -->
                <td style="text-align: right; vertical-align: middle; border: none;">
                    <p style="margin: 0; font-size: 14px; font-weight: bold;">Calle Juan Manuel Carpio N° 275</p>
                    <p style="margin: 0; font-size: 14px;">Zona Los Andes El Alto</p>
                    <p style="margin: 0; font-size: 14px;">Cel.: 77246463 - 65178769</p>
                </td>
            </tr>
        </table>
        <h2>Reporte de Picking & Packing</h2>
        <p>Fecha de Reporte: <strong>{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</strong></p>
    </div>

    <!-- Tabla de Preventas -->
    <table>
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Cliente</th>
                <th>Nombre Comercio</th>
                <th>Dirección</th>
                <th>Productos</th>
                <th>Estado</th>
                <th>Observación</th> <!-- Nueva columna para observación -->
            </tr>
        </thead>
        <tbody>
            @foreach($preventas as $preventa)
            <tr>
                <!-- Número de Pedido -->
                <td>{{ $preventa->numero_pedido }}</td>

                <!-- Cliente -->
                <td>{{ $preventa->cliente ? ($preventa->cliente->nombre_propietario ?? $preventa->cliente->nombre_comercio) : 'Sin Cliente' }}</td>

                <!-- Nombre Comercio -->
                <td>{{ $preventa->cliente ? $preventa->cliente->nombre_comercio : 'No disponible' }}</td>  

                <!-- Dirección -->
                <td>{{ $preventa->cliente ? $preventa->cliente->direccion : 'No disponible' }}</td>  

                <!-- Productos -->
                <td>
                    @if($preventa->detalles->isEmpty())
                        <span class="no-products">Sin Productos</span>
                    @else
                        <ul class="product-list">
                            @foreach($preventa->detalles as $detalle)
                                <li>- {{ $detalle->producto ? $detalle->producto->nombre_producto : 'Producto no encontrado' }} ({{ $detalle->cantidad }})</li>
                            @endforeach
                        </ul>
                    @endif
                </td>

                <!-- Estado (Colores dinámicos) -->
                <td>
                    @if($preventa->estado === 'Pendiente')
                        <span class="status-pendiente">Pendiente</span>
                    @elseif($preventa->estado === 'Preparado')
                        <span class="status-preparado">Preparado</span>
                    @elseif($preventa->estado === 'Entregado')
                        <span class="status-entregado">Entregado</span>
                    @elseif($preventa->estado === 'No Entregado')
                        <span class="status-no-entregado">No Entregado</span>
                    @endif
                </td>

                <!-- Observación -->
                <td>
                    @if($preventa->observacion_entrega)
                        <span class="obs-text"><strong>Obs:</strong> {{ $preventa->observacion_entrega }}</span>
                    @else
                        <span class="obs-text">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
