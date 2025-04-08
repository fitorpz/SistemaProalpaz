<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoVenta;

class TipoVentaController extends Controller
{
    // Listar todos los tipos de ventas
    public function index()
    {
        $tiposVentas = TipoVenta::all();
        return response()->json(['status' => 'success', 'data' => $tiposVentas]);
    }

    // Guardar un nuevo tipo de venta
    public function store(Request $request)
    {
        $request->validate([
            'tipo_venta' => 'required|string|max:255'
        ]);

        TipoVenta::create($request->all());

        return redirect()->route('users.index')->with('success', 'Tipo de venta agregado correctamente.');
    }


    // Obtener un tipo de venta por ID
    public function show($id)
    {
        $tipoVenta = TipoVenta::findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $tipoVenta]);
    }

    // Actualizar un tipo de venta
    public function update(Request $request, $id)
    {
        $tipoVenta = TipoVenta::findOrFail($id);

        $request->validate([
            'tipo_venta' => 'required|string|max:255|unique:tipos_ventas,tipo_venta,' . $id,
        ]);

        $tipoVenta->update([
            'tipo_venta' => $request->tipo_venta,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Tipo de venta actualizado.', 'data' => $tipoVenta]);
    }

    // Eliminar un tipo de venta
    public function destroy($id)
    {
        $tipoVenta = TipoVenta::findOrFail($id);
        $tipoVenta->delete();

        return redirect()->route('users.index')->with('success', 'Tipo de venta eliminado correctamente.');
    }
}
