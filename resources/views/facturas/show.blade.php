@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Factura</h1>

    <div class="card">
        <div class="card-header">
            <h2>Factura de {{ $factura->nombre_proveedor }}</h2>
        </div>
        <div class="card-body">
            <p><strong>Proveedor:</strong> {{ $factura->nombre_proveedor }}</p>
            <p><strong>NIT:</strong> {{ $factura->nit_proveedor }}</p>
            <p><strong>Total de la Factura:</strong> {{ $factura->total }}</p>

            <h3>Productos</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio de Compra</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($factura->productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->pivot->cantidad }}</td>
                        <td>{{ $producto->pivot->precio_compra }}</td>
                        <td>{{ $producto->pivot->cantidad * $producto->pivot->precio_compra }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('facturas.index') }}" class="btn btn-primary mt-3">Volver a Facturas</a>
</div>
@endsection
