<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function index()
    {
        // Obtener todas las facturas
        $facturas = Factura::all();
        return view('facturas.index', compact('facturas'));
    }

    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'nombre_proveedor' => 'required|string|max:255',
            'nit_proveedor' => 'required|string|max:50',
            'productos' => 'required|array',
            'productos.*.nombre' => 'required|string',
            'productos.*.cantidad' => 'required|integer',
            'productos.*.precio_compra' => 'required|numeric',
        ]);

        // Calcular el total de la factura
        $total = 0;
        foreach ($request->productos as $producto) {
            $total += $producto['cantidad'] * $producto['precio_compra'];
        }

        // Guardar la factura
        Factura::create([
            'nombre_proveedor' => $request->nombre_proveedor,
            'nit_proveedor' => $request->nit_proveedor,
            'productos' => $request->productos, // Guardamos los productos como JSON
            'total' => $total,
        ]);

        return redirect()->route('facturas.index')->with('success', 'Factura registrada exitosamente.');
    }

    public function show($id)
    {
        $factura = Factura::with('productos')->findOrFail($id);
        return view('facturas.show', compact('factura'));
    }
}
