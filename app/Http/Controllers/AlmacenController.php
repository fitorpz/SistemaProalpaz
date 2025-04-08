<?php

namespace App\Http\Controllers;

use App\Models\Ingreso; // Importa el modelo Ingreso
use App\Models\Almacen; // Importa el modelo Almacen
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        $ingresos = Ingreso::all();
        $almacenes = Almacen::all();

        return view('ingresos.index', compact('ingresos', 'almacenes'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Almacen::create($validated);

        return redirect()->back()->with('success', 'Almacén agregado correctamente.');
    }
    public function destroy($id)
    {
        $almacen = Almacen::findOrFail($id);

        // Lógica adicional: Verificar dependencias antes de eliminar, si es necesario
        // Por ejemplo:
        // if ($almacen->productos()->exists()) {
        //     return redirect()->back()->withErrors(['error' => 'No se puede eliminar un almacén que tiene productos asociados.']);
        // }

        $almacen->delete();

        return redirect()->back()->with('success', 'Almacén eliminado correctamente.');
    }
}
