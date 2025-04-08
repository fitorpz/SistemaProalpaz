<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TraspasoController extends Controller
{
    public function index(Request $request)
    {
        $search_query = $request->input('search_query');

        // Si hay una búsqueda
        $result = [];
        if ($search_query) {
            $search_query_wildcard = "%$search_query%";
            $result = DB::select("
                SELECT codigo_producto, nombre_producto, cantidad, fecha_vencimiento, lote, 
                       almacen_producto_terminado, almacen_insumos, almacen_cosmeticos
                FROM ingresos
                WHERE codigo_producto LIKE ? OR nombre_producto LIKE ? OR lote LIKE ? OR fecha_vencimiento LIKE ?
            ", [
                $search_query_wildcard,
                $search_query_wildcard,
                $search_query_wildcard,
                $search_query_wildcard
            ]);
        }

        // Obtener traspasos realizados
        $traspasos = DB::select("
            SELECT t.codigo_producto, t.nombre_producto, t.cantidad AS cantidad, t.fecha_traspaso, 
                   t.almacen_origen, t.almacen_destino, 
                   i.almacen_producto_terminado, i.almacen_insumos, i.almacen_cosmeticos
            FROM traspasos t
            LEFT JOIN ingresos i ON t.codigo_producto = i.codigo_producto AND t.lote = i.lote
            ORDER BY t.fecha_traspaso DESC
        ");

        // Opciones de almacenes
        $almacenes = [
            'Almacén Producto Terminado',
            'Almacén Insumos',
            'Almacén Cosméticos',
        ];

        return view('traspasos.index', compact('result', 'traspasos', 'almacenes', 'search_query'));
    }

    public function registrarTraspaso(Request $request)
    {
        $request->validate([
            'codigo_producto' => 'required',
            'cantidad' => 'required|integer|min:1',
            'almacen_origen' => 'required',
            'almacen_destino' => 'required',
        ]);

        $almacenes = [
            'Almacén Producto Terminado' => 'almacen_producto_terminado',
            'Almacén Insumos' => 'almacen_insumos',
            'Almacén Cosméticos' => 'almacen_cosmeticos',
        ];

        // Capturar los valores correctos de los almacenes
        $almacen_origen = $almacenes[$request->input('almacen_origen')] ?? null;
        $almacen_destino = $almacenes[$request->input('almacen_destino')] ?? null;

        if (!$almacen_origen || !$almacen_destino) {
            return redirect()->back()->withErrors(['error' => 'Almacén inválido.']);
        }

        // Obtener el producto correspondiente al código
        $producto = DB::table('ingresos')->where('codigo_producto', $request->codigo_producto)->first();

        if (!$producto) {
            return redirect()->back()->withErrors(['error' => 'Producto no encontrado.']);
        }

        // Verificar si hay suficiente stock en el almacén de origen
        if ($producto->{$almacen_origen} < $request->cantidad) {
            return redirect()->back()->withErrors(['error' => 'No hay suficiente stock en el almacén de origen.']);
        }

        // Descontar del almacén de origen
        DB::table('ingresos')->where('codigo_producto', $request->codigo_producto)->update([
            $almacen_origen => DB::raw($almacen_origen . ' - ' . $request->cantidad),
        ]);

        // Aumentar en el almacén de destino
        DB::table('ingresos')->where('codigo_producto', $request->codigo_producto)->update([
            $almacen_destino => DB::raw($almacen_destino . ' + ' . $request->cantidad),
        ]);

        // Registrar el traspaso
        DB::table('traspasos')->insert([
            'codigo_producto' => $request->codigo_producto,
            'nombre_producto' => $request->nombre_producto,
            'cantidad' => $request->cantidad,
            'fecha_traspaso' => $request->fecha_traspaso,
            'lote' => $request->lote,
            'almacen_origen' => $request->almacen_origen,
            'almacen_destino' => $request->almacen_destino,
        ]);

        return redirect()->back()->with('success', 'Traspaso realizado correctamente.');
    }
}
