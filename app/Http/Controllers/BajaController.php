<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Ingreso;
use App\Models\Baja;
use Carbon\Carbon;

class BajaController extends Controller
{
    public function index()
    {
        // Obtener todas las bajas registradas
        $bajas = Baja::orderBy('fecha_registro', 'desc')->get();

        // Inicializar resultados vacíos para búsquedas
        $resultados = collect();

        return view('bajas.index', compact('bajas', 'resultados'));
    }

    public function buscar(Request $request)
    {
        $query = $request->input('query');

        // Buscar en la tabla ingresos
        $resultados = DB::table('ingresos')
            ->join('almacenes', 'ingresos.almacen_id', '=', 'almacenes.id') // Cambiar según tu estructura
            ->select(
                'ingresos.id',
                'ingresos.codigo_producto',
                'ingresos.nombre_producto',
                'ingresos.cantidad',
                'almacenes.nombre as almacen',
                'ingresos.created_at as fecha_registro'
            )
            ->where('ingresos.codigo_producto', 'LIKE', "%{$query}%")
            ->orWhere('ingresos.nombre_producto', 'LIKE', "%{$query}%")
            ->orWhere('ingresos.created_at', 'LIKE', "%{$query}%")
            ->get();

        // Mantener las bajas ya registradas
        $bajas = Baja::orderBy('fecha_registro', 'desc')->get();

        return view('bajas.index', compact('bajas', 'resultados'));
    }

    public function registrarBaja(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ingresos,id', // Verifica que el ID exista en la tabla ingresos
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string',
        ]);

        // Obtener el producto ingresado junto con el almacén
        $producto = DB::table('ingresos')
            ->join('almacenes', 'ingresos.almacen_id', '=', 'almacenes.id') // Cambia 'almacen' a 'almacen_id' si es necesario
            ->select(
                'ingresos.id',
                'ingresos.codigo_producto',
                'ingresos.nombre_producto',
                'ingresos.cantidad',
                'almacenes.nombre as almacen'
            )
            ->where('ingresos.id', $request->id)
            ->first();

        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        // Validar la cantidad disponible
        if ($producto->cantidad < $request->cantidad) {
            return redirect()->back()->with('error', 'La cantidad a dar de baja supera la cantidad disponible.');
        }

        // Actualizar la cantidad en la tabla ingresos
        DB::table('ingresos')
            ->where('id', $producto->id)
            ->decrement('cantidad', $request->cantidad);

        // Registrar la baja en la tabla bajas
        DB::table('bajas')->insert([
            'codigo_producto' => $producto->codigo_producto,
            'nombre_producto' => $producto->nombre_producto,
            'cantidad' => $request->cantidad,
            'motivo' => $request->motivo,
            'fecha_registro' => now(),
            'almacen' => $producto->almacen, // Usar el nombre del almacén obtenido del join
        ]);

        return redirect()->route('bajas.index')->with('success', 'La baja se ha registrado correctamente.');
    }
}
