<?php
// app/Http/Controllers/CompraController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compra;

class CompraController extends Controller
{
    public function index()
    {
        // Obtener todas las compras
        $compras = Compra::all();
        return view('compras.index', compact('compras'));
    }

    public function create()
    {
        // Mostrar el formulario de creación de compras
        return view('compras.create');
    }

    public function store(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'codigo_producto' => 'required|string|max:100',
                'nombre_producto' => 'required|string|max:255',
                'cantidad' => 'required|integer',
                'fecha_vencimiento' => 'required|date',
                'lote' => 'required|string|max:100|unique:compras,lote,NULL,id,codigo_producto,' . $request->codigo_producto,
                'costo_compra' => 'required|numeric',
                'precio_unidad_credito' => 'required|numeric',
                'precio_unidad_contado' => 'required|numeric',
                'stock_critico' => 'required|integer',
                'almacen_producto_terminado' => 'required|integer',
                'almacen_materia_prima' => 'required|integer',
                'almacen_cosmeticos' => 'required|integer',
                'con_factura' => 'sometimes|boolean', // Permitir que el campo sea opcional
            ]);

            // Verificar si se marca la opción de "Compra con Factura" y aplicar descuento del 13%
            $costoCompra = $request->input('costo_compra');
            if ($request->input('con_factura')) {
                $costoCompra *= 0.87; // Aplicar el descuento del 13%
            }

            // Crear la compra
            Compra::create([
                'codigo_producto' => $request->input('codigo_producto'),
                'nombre_producto' => $request->input('nombre_producto'),
                'cantidad' => $request->input('cantidad'),
                'fecha_vencimiento' => $request->input('fecha_vencimiento'),
                'lote' => $request->input('lote'),
                'costo_compra' => $costoCompra, // Aquí se guarda el costo con el descuento aplicado
                'precio_unidad_credito' => $request->input('precio_unidad_credito'),
                'precio_unidad_contado' => $request->input('precio_unidad_contado'),
                'stock_critico' => $request->input('stock_critico'),
                'almacen_producto_terminado' => $request->input('almacen_producto_terminado'),
                'almacen_materia_prima' => $request->input('almacen_materia_prima'),
                'almacen_cosmeticos' => $request->input('almacen_cosmeticos'),
                'con_factura' => $request->input('con_factura') ? 1 : 0,
            ]);

            return redirect()->route('compras.index')->with('success', 'Compra registrada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['Error al registrar la compra: ' . $e->getMessage()]);
        }
    }

    public function fetchProducto($codigo_producto)
    {
        // Obtener los datos del producto por código
        $producto = Compra::where('codigo_producto', $codigo_producto)->first();

        if ($producto) {
            return response()->json($producto);
        } else {
            return response()->json(null);
        }
    }

    public function update(Request $request, $id)
    {
        // Validación de la actualización
        $request->validate([
            'codigo_producto' => 'required|string|max:100',
            'nombre_producto' => 'required|string|max:255',
            'cantidad' => 'required|integer',
            'fecha_vencimiento' => 'required|date',
            'lote' => 'required|string|max:100|unique:compras,lote,' . $id . ',id,codigo_producto,' . $request->codigo_producto,
            'costo_compra' => 'required|numeric',
            'precio_unidad_credito' => 'required|numeric',
            'precio_unidad_contado' => 'required|numeric',
            'stock_critico' => 'required|integer',
            'almacen_producto_terminado' => 'required|integer',
            'almacen_materia_prima' => 'required|integer',
            'almacen_cosmeticos' => 'required|integer',
            'con_factura' => 'sometimes|boolean',
        ]);

        // Actualizar la compra
        $compra = Compra::findOrFail($id);
        $compra->update($request->all());

        return redirect()->route('compras.index')->with('success', 'Compra actualizada exitosamente.');
    }

    public function destroy($id)
    {
        // Eliminar una compra
        $compra = Compra::findOrFail($id);
        $compra->delete();

        return redirect()->route('compras.index')->with('success', 'Compra eliminada exitosamente.');
    }
}
