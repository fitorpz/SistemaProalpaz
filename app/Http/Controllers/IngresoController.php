<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingreso;
use App\Models\Almacen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class IngresoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user(); // Obtener usuario autenticado
        $almacenSeleccionado = $request->input('almacen_id'); // ID del almacén seleccionado

        if ($user->rol === 'usuario_operador') {
            $almacenesPermitidos = json_decode($user->almacenes_permitidos, true) ?? [];

            // Filtrar solo los almacenes permitidos para el usuario
            $almacenes = Almacen::whereIn('id', $almacenesPermitidos)->get();

            // Filtrar ingresos de los almacenes permitidos, y si hay un almacén seleccionado, aplicar el filtro
            $query = Ingreso::whereIn('almacen_id', $almacenesPermitidos);

            if ($almacenSeleccionado) {
                $query->where('almacen_id', $almacenSeleccionado);
            }

            $ingresos = $query->with('almacen')->get();
        } else {
            // Administradores y otros roles pueden ver todos los almacenes e ingresos
            $almacenes = Almacen::all();
            $query = Ingreso::query();

            if ($almacenSeleccionado) {
                $query->where('almacen_id', $almacenSeleccionado);
            }

            $ingresos = $query->with('almacen')->get();
        }

        return view('ingresos.index', compact('ingresos', 'almacenes', 'almacenSeleccionado'));
    }



    public function create()
    {
        // Obtener todos los almacenes para el formulario
        $almacenes = Almacen::all();
        return view('ingresos.create', compact('almacenes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'codigo_producto' => 'required|string|max:255',
            'nombre_producto' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'required|string|max:255',
            'costo_produccion_compra' => 'required|numeric|min:0',
            'compra_con_factura' => 'required|boolean',
            'tipo_ingreso' => 'required|string|in:compra,produccion',
            'precio_unidad_credito' => 'required|numeric|min:0',
            'precio_unidad_contado' => 'required|numeric|min:0',
            'precio_caja_credito' => 'required|numeric|min:0',
            'precio_caja_contado' => 'required|numeric|min:0',
            'precio_cajon_credito' => 'required|numeric|min:0',
            'precio_cajon_contado' => 'required|numeric|min:0',
            'precio_promocion' => 'nullable|numeric|min:0',
            'stock_critico' => 'required|integer|min:0',
        ]);

        //Agrregar cantidad_inicial solo al momento de crear
        $validated['cantidad_inicial'] = $validated['cantidad'];

        // Normalizar los valores
        $validated['lote'] = trim($validated['lote']);
        $validated['fecha_vencimiento'] = $validated['fecha_vencimiento'] ? date('Y-m-d', strtotime($validated['fecha_vencimiento'])) : null;

        // Verificar duplicados: mismo código de producto con lote o fecha de vencimiento coincidentes
        //$duplicateQuery = Ingreso::where('codigo_producto', $validated['codigo_producto'])
        //    ->where(function ($query) use ($validated) {
        //        $query->where('lote', $validated['lote'])
        //            ->orWhere('fecha_vencimiento', $validated['fecha_vencimiento']);
        //    });

        //$duplicate = $duplicateQuery->exists();

        //if ($duplicate) {
        //    return redirect()->back()
        //        ->withErrors(['codigo_fecha_error' => 'Ya existe un ingreso con el mismo código de producto, lote o fecha de vencimiento.'])
        //        ->withInput();
        //}

        // Crear el ingreso si no hay duplicados
        Ingreso::create($validated);

        return redirect()->back()->with('success', 'Ingreso registrado correctamente.');
    }
    public function edit($id)
    {
        $ingreso = Ingreso::findOrFail($id);
        $almacenes = Almacen::all();
        return view('ingresos.edit', compact('ingreso', 'almacenes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'almacen_id' => 'required|exists:almacenes,id',
            'codigo_producto' => 'required|string|max:255',
            'nombre_producto' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'fecha_vencimiento' => 'nullable|date',
            'lote' => 'required|string|max:255',
            'costo_produccion_compra' => 'required|numeric|min:0',
            'compra_con_factura' => 'required|boolean',
            'tipo_ingreso' => 'required|string|in:compra,produccion',
            'precio_unidad_credito' => 'required|numeric|min:0',
            'precio_unidad_contado' => 'required|numeric|min:0',
            'precio_caja_credito' => 'required|numeric|min:0',
            'precio_caja_contado' => 'required|numeric|min:0',
            'precio_cajon_credito' => 'required|numeric|min:0',
            'precio_cajon_contado' => 'required|numeric|min:0',
            'precio_promocion' => 'nullable|numeric|min:0',
            'stock_critico' => 'required|integer|min:0',
        ]);

        // Obtener el ingreso original
        $ingreso = Ingreso::findOrFail($id);

        // Verificar si el código de producto cambió (esto no debe cambiar)
        if ($ingreso->codigo_producto !== $request->codigo_producto) {
            return response()->json(['error' => 'No puedes cambiar el código de producto.'], 400);
        }

        // Definir los campos que se deben actualizar en todos los registros con el mismo código de producto
        $campos_globales = [
            'nombre_producto',
            'costo_produccion_compra',
            'precio_unidad_credito',
            'precio_unidad_contado',
            'precio_caja_credito',
            'precio_caja_contado',
            'precio_cajon_credito',
            'precio_cajon_contado',
            'precio_promocion',
            'stock_critico',
            'almacen_id',
        ];

        // Solo actualizar los campos globales si han cambiado
        $datos_a_actualizar_globalmente = [];
        foreach ($campos_globales as $campo) {
            if ($request->$campo != $ingreso->$campo) {
                $datos_a_actualizar_globalmente[$campo] = $request->$campo;
            }
        }

        // Si hay cambios en los campos globales, actualizar todos los registros con el mismo código de producto
        if (!empty($datos_a_actualizar_globalmente)) {
            Ingreso::where('codigo_producto', $ingreso->codigo_producto)
                ->update($datos_a_actualizar_globalmente);
        }

        // Actualizar solo este registro con los campos específicos del lote
        $ingreso->update([
            'cantidad' => $request->cantidad,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'lote' => $request->lote,
            'tipo_ingreso' => $request->tipo_ingreso,
            'compra_con_factura' => $request->compra_con_factura,
        ]);

        return redirect()->back()->with('success', 'Ingreso actualizado correctamente.');
    }

    public function checkDuplicate(Request $request)
    {
        try {
            // Validar los campos
            $validated = $request->validate([
                'codigo_producto' => 'required|string',
                'fecha_vencimiento' => 'nullable|date',
            ]);

            // Construir la consulta para verificar duplicados
            $query = Ingreso::where('codigo_producto', $validated['codigo_producto']);

            if (!empty($validated['fecha_vencimiento'])) {
                $query->where('fecha_vencimiento', $validated['fecha_vencimiento']);
            }

            // Verificar si existe un registro duplicado
            $exists = $query->exists();

            // Devolver respuesta en JSON
            return response()->json(['exists' => $exists]);
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json([
                'error' => 'Ocurrió un error al verificar duplicados.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProduct(Request $request)
    {
        $validated = $request->validate([
            'codigo_producto' => 'required|string',
        ]);

        $ingreso = Ingreso::where('codigo_producto', $validated['codigo_producto'])->latest()->first();

        if ($ingreso) {
            return response()->json([
                'codigo_producto' => $ingreso->codigo_producto,
                'nombre_producto' => $ingreso->nombre_producto,
                'costo_produccion_compra' => $ingreso->costo_produccion_compra,
                'precio_unidad_credito' => $ingreso->precio_unidad_credito,
                'precio_unidad_contado' => $ingreso->precio_unidad_contado,
                'precio_caja_credito' => $ingreso->precio_caja_credito,
                'precio_caja_contado' => $ingreso->precio_caja_contado,
                'precio_cajon_credito' => $ingreso->precio_cajon_credito,
                'precio_cajon_contado' => $ingreso->precio_cajon_contado,
                'precio_promocion' => $ingreso->precio_promocion,
                'stock_critico' => $ingreso->stock_critico,
                'fecha_vencimiento' => $ingreso->fecha_vencimiento,
            ]);
        }

        return response()->json(null, 404); // Si no se encuentra, devolver un error 404
    }
    public function destroy($id)
    {
        $ingreso = Ingreso::findOrFail($id);
        $ingreso->delete();

        return redirect()->back()->with('success', 'Ingreso eliminado correctamente.');
    }
    public function ingresosAsignados()
    {
        $user = auth()->user();

        // Validar que el usuario tenga el rol gestion_ventas
        if ($user->role == 'gestion_ventas') {
            abort(403, 'Acceso no autorizado.');
        }

        // Decodificar los almacenes permitidos (suponiendo que almacenes_permitidos es un JSON)
        $almacenesPermitidos = json_decode($user->almacenes_permitidos, true);

        // Validar que el usuario tenga almacenes permitidos configurados
        if (empty($almacenesPermitidos)) {
            return view('ingresos.asignados', ['ingresos' => collect()])
                ->with('warning', 'No tienes almacenes asignados.');
        }

        // Filtrar los ingresos según los almacenes permitidos
        $ingresos = Ingreso::whereIn('almacen_id', $almacenesPermitidos)->get();

        return view('ingresos.asignados', compact('ingresos'));
    }
    public function getFechasVencimiento($codigoProducto)
    {
        // Buscar productos por el código proporcionado y que tengan stock mayor a 0
        $ingresos = Ingreso::where('codigo_producto', $codigoProducto)
            ->where('cantidad', '>', 0)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get(['id', 'fecha_vencimiento', 'cantidad']); // Solo devolver los campos necesarios

        // Devolver los resultados como JSON
        return response()->json($ingresos);
    }
    public function buscarProductos(Request $request)
    {
        $query = $request->input('q');
        $user = Auth::user(); // ✅ Obtener el usuario autenticado

        // ✅ Obtener los almacenes permitidos para este usuario
        $almacenesPermitidos = json_decode($user->almacenes_permitidos, true) ?? [];

        // 📌 Aplicamos el filtro de almacenes en la consulta
        $productos = Ingreso::selectRaw('
        codigo_producto, 
        nombre_producto as nombre, 
        almacen_id,  -- ✅ Incluir almacén en la respuesta
        SUM(cantidad) as stock_total, 
        MAX(precio_unidad_credito) as precio_unidad_credito, 
        MAX(precio_unidad_contado) as precio_unidad_contado, 
        MAX(precio_caja_credito) as precio_caja_credito, 
        MAX(precio_caja_contado) as precio_caja_contado, 
        MAX(precio_cajon_credito) as precio_cajon_credito, 
        MAX(precio_cajon_contado) as precio_cajon_contado,
        MAX(precio_promocion) AS precio_promocion
    ')
            ->where(function ($q) use ($query) {
                $q->where('nombre_producto', 'LIKE', "%{$query}%")
                    ->orWhere('codigo_producto', 'LIKE', "%{$query}%");
            })
            ->whereIn('almacen_id', $almacenesPermitidos) // ✅ Filtrar solo productos de los almacenes asignados
            ->groupBy('codigo_producto', 'nombre_producto', 'almacen_id') // ✅ Agregar almacen_id al groupBy
            ->having('stock_total', '>', 0) // Solo mostrar productos con stock disponible
            ->limit(10)
            ->get();

        return response()->json($productos);
    }


    public function getPrecioProducto(Request $request)
    {
        $producto = Ingreso::where('nombre_producto', $request->nombre)
            ->latest()
            ->first();

        if (!$producto) {
            return response()->json(null, 404);
        }

        return response()->json([
            'precio_unidad_credito' => $producto->precio_unidad_credito,
            'precio_unidad_contado' => $producto->precio_unidad_contado,
            'precio_caja_credito' => $producto->precio_caja_credito,
            'precio_caja_contado' => $producto->precio_caja_contado,
            'precio_cajon_credito' => $producto->precio_cajon_credito,
            'precio_cajon_contado' => $producto->precio_cajon_contado,
        ]);
    }
}
