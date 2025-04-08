<?php

namespace App\Http\Controllers;

use App\Models\Preventa;
use App\Models\CargosCliente;
use Carbon\Carbon;
use App\Models\DetallePreventa;
use App\Models\Cliente;
use App\Models\Ingreso;
use App\Models\Almacen;
use App\Models\User;
use App\Models\RutaVisita;
use App\Models\TipoVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PreventaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 📅 Obtener la fecha seleccionada o establecer la del día actual por defecto
        $fechaFiltro = $request->input('fecha_filtro');

        // 📌 Obtener el almacén seleccionado (si se eligió alguno)
        $almacenFiltro = $request->input('almacen_filtro');

        // 📌 Obtener el cliente seleccionado (si se eligió alguno)
        $clienteFiltro = $request->input('cliente_filtro');

        // 📌 Obtener el preventista seleccionado (si se eligió alguno)
        $preventistaFiltro = $request->input('preventista_filtro');

        // 📌 Consulta base con relaciones
        $query = Preventa::with(['detalles.producto', 'detalles.bonificacionProducto', 'cliente', 'preventista']);

        // 🔹 Si el usuario tiene rol 'gestion_ventas', solo verá sus preventas
        if ($user->rol === 'gestion_ventas') {
            $query->where('preventista_id', $user->id);
        } elseif ($user->rol === 'usuario_operador') {
            // 🔹 Si el usuario es "usuario_operador", verá todas las preventas de su almacén

            // ✅ Obtener los IDs de los almacenes asignados al usuario operador
            $almacenesPermitidos = isset($user->almacenes_permitidos) ? json_decode($user->almacenes_permitidos, true) : [];
            $almacenesPermitidos = is_array($almacenesPermitidos) ? $almacenesPermitidos : [];


            // ✅ Filtrar preventas donde los productos estén en los almacenes asignados
            $query->whereHas('detalles.producto', function ($q) use ($almacenesPermitidos) {
                $q->whereIn('almacen_id', $almacenesPermitidos);
            });

            // ✅ Filtrar también por preventistas que pertenecen a estos almacenes
            $preventistasEnAlmacenes = User::where(function ($query) use ($almacenesPermitidos) {
                foreach ($almacenesPermitidos as $almacen) {
                    $query->orWhereJsonContains('almacenes_permitidos', (string) $almacen);
                }
            })->pluck('id')->toArray();
        }

        // 📅 Filtrar por fecha si el usuario selecciona una válida
        if (!empty($fechaFiltro)) {
            $query->whereDate('created_at', $fechaFiltro);
        } else {
            $fechaFiltro = null;
        }

        // 📌 Filtrar por almacén si se seleccionó uno
        if (!empty($almacenFiltro)) {
            $query->whereHas('detalles.producto', function ($q) use ($almacenFiltro) {
                $q->where('almacen_id', $almacenFiltro);
            });
        }

        // 📌 Filtrar por cliente si se seleccionó uno
        if (!empty($clienteFiltro)) {
            $query->where('cliente_id', $clienteFiltro);
        }

        // 📌 Filtrar por preventista si se seleccionó uno
        if (!empty($preventistaFiltro)) {
            $query->where('preventista_id', $preventistaFiltro);
        }

        // 📌 Obtener las preventas filtradas
        $preventas = $query->get();

        // 📌 Calcular el total generado
        $totalGenerado = $preventas->sum('precio_total');

        // 📌 Obtener la lista de almacenes para el filtro
        $almacenes = Almacen::all();

        // 📌 Obtener la lista de clientes
        $clientes = Cliente::all();

        // 📌 Obtener la lista de preventistas (usuarios con rol preventista o similar)
        $preventistas = User::whereIn('rol', ['gestion_ventas', 'usuario_operador', 'administrador'])->get();

        // 📌 Obtener productos disponibles
        $productos = Ingreso::select(
            'codigo_producto',
            'nombre_producto',
            DB::raw('SUM(cantidad) as stock'),
            DB::raw('MIN(fecha_vencimiento) as fecha_vencimiento'),
            DB::raw('MIN(precio_unidad_credito) as precio_unidad_credito'),
            DB::raw('MIN(precio_unidad_contado) as precio_unidad_contado'),
            DB::raw('MIN(precio_caja_credito) as precio_caja_credito'),
            DB::raw('MIN(precio_caja_contado) as precio_caja_contado'),
            DB::raw('MIN(precio_cajon_credito) as precio_cajon_credito'),
            DB::raw('MIN(precio_cajon_contado) as precio_cajon_contado'),
            DB::raw('MIN(precio_promocion) as precio_promocion')
        )
            ->where('cantidad', '>', 0)
            ->groupBy('codigo_producto', 'nombre_producto')
            ->orderBy('fecha_vencimiento', 'asc')
            ->having('stock', '>', 0);

        if ($user->rol === 'gestion_ventas') {
            $almacenesPermitidos = json_decode($user->almacenes_permitidos, true);
            if (!is_array($almacenesPermitidos)) {
                $almacenesPermitidos = [];
            }
            $productos->whereIn('almacen_id', $almacenesPermitidos);
        }

        $productos = $productos->get();

        // ✅ Obtener los tipos de venta permitidos por usuario y asegurarnos de que sea un array válido
        $tiposVentasPermitidos = auth()->user()->tipos_ventas_permitidos ?? [];

        if (is_string($tiposVentasPermitidos)) {
            $tiposVentasPermitidos = json_decode($tiposVentasPermitidos, true);
        }

        if (!is_array($tiposVentasPermitidos)) {
            $tiposVentasPermitidos = [];
        }


        $tiposVentas = TipoVenta::whereIn('id', $tiposVentasPermitidos)->get();

        return view('preventas.index', compact(
            'preventas',
            'almacenes',
            'clientes',
            'productos',
            'user',
            'fechaFiltro',
            'almacenFiltro',
            'clienteFiltro',
            'preventistaFiltro',
            'preventistas',
            'totalGenerado',
            'tiposVentas' // 🔹 Se envían los tipos de venta correctamente filtrados
        ));
    }
    public function buscarClientes(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q');

        $queryClientes = Cliente::where(function ($q) use ($query) {
            $q->where('nombre', 'like', "%{$query}%")
                ->orWhere('empresa', 'like', "%{$query}%");
        });

        if ($user->rol === 'gestion_ventas') {
            $queryClientes->where('preventista_id', $user->id);
        }

        $clientes = $queryClientes->limit(10)->get();

        // 🔍 Verificar en logs si se están obteniendo clientes
        Log::info("📌 Clientes encontrados:", $clientes->toArray());

        return response()->json($clientes);
    }
    public function buscarProductos(Request $request)
    {
        $query = $request->input('q');

        $productos = DB::table('ingresos')
            ->select(
                'codigo_producto',
                'nombre_producto as nombre',
                DB::raw('SUM(cantidad) as stock_total'),
                DB::raw('MAX(precio_unidad_credito) as precio_unidad_credito'),
                DB::raw('MAX(precio_unidad_contado) as precio_unidad_contado'),
                DB::raw('MAX(precio_caja_credito) as precio_caja_credito'),
                DB::raw('MAX(precio_caja_contado) as precio_caja_contado'),
                DB::raw('MAX(precio_cajon_credito) as precio_cajon_credito'),
                DB::raw('MAX(precio_cajon_contado) as precio_cajon_contado'),
                DB::raw('COALESCE(MAX(precio_promocion), 0) as precio_promocion')
            )
            ->where('nombre_producto', 'LIKE', "%{$query}%")
            ->orWhere('codigo_producto', 'LIKE', "%{$query}%")
            ->groupBy('codigo_producto', 'nombre_producto')
            ->having('stock_total', '>', 0)
            ->orderBy('codigo_producto', 'ASC')
            ->limit(10)
            ->get();

        // 🔥 DETENER LA EJECUCIÓN Y VERIFICAR LOS DATOS
        dd($productos);

        return response()->json($productos);
    }
    public function getProductosDisponibles()
    {
        // Consulta agrupada por código de producto y suma de cantidades
        $productos = DB::table('ingresos')
            ->select('codigo_producto', 'nombre_producto', DB::raw('SUM(cantidad) as cantidad_total'))
            ->groupBy('codigo_producto', 'nombre_producto')
            ->having('cantidad_total', '>', 0) // Solo mostrar productos con cantidad disponible
            ->get();

        return response()->json($productos);
    }
    public function store(Request $request)
    {
        Log::info('📌 Datos recibidos para registrar preventa:', $request->all());

        // ✅ Validación de datos
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'observaciones' => 'nullable|string|max:255',
            'fecha_entrega' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:ingresos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.tipo_precio' => 'nullable|string|max:50',
            'detalles.*.subtotal' => 'required|numeric|min:0',
            'detalles.*.fecha_vencimiento' => 'required|date',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'tipo_venta' => 'required|string',
        ]);

        Log::info('✅ Datos validados correctamente.');

        DB::beginTransaction(); // Iniciar transacción para garantizar integridad

        try {
            // ✅ Obtener los tipos de venta permitidos del usuario
            $tiposVentasPermitidos = auth()->user()->tipos_ventas_permitidos;

            // ✅ Verificar si es una cadena JSON y decodificarla
            if (is_string($tiposVentasPermitidos)) {
                $tiposVentasPermitidos = json_decode($tiposVentasPermitidos, true);
            }

            // ✅ Asegurar que es un array válido
            if (!is_array($tiposVentasPermitidos)) {
                $tiposVentasPermitidos = [];
            }

            // ✅ Verificar que el tipo de venta seleccionado es válido
            $tiposVentasDisponibles = TipoVenta::whereIn('id', $tiposVentasPermitidos)->pluck('tipo_venta')->toArray();
            if (!in_array($request->tipo_venta, $tiposVentasDisponibles)) {
                return back()->withErrors(['tipo_venta' => 'El tipo de venta seleccionado no es válido.']);
            }

            // ✅ Crear la preventa
            $preventa = Preventa::create([
                'numero_pedido' => Preventa::generateNumeroPedido(Auth::user()->nombre),
                'cliente_id' => $request->cliente_id,
                'precio_total' => collect($request->detalles)->sum('subtotal'),
                'descuento' => $request->descuento ?? 0,
                'observaciones' => $request->observaciones,
                'fecha_entrega' => $request->fecha_entrega,
                'preventista_id' => Auth::id(),
                'estado' => 'Pendiente',
                'tipo_venta' => $request->tipo_venta,
            ]);

            Log::info("✅ Preventa creada con ID: {$preventa->id}");

            $monto_total_credito = 0;
            $detalle_credito = [];
            $precios_credito = ['precio_unidad_credito', 'precio_caja_credito', 'precio_cajon_credito'];

            foreach ($request->detalles as $detalle) {
                // ✅ Guardar el detalle de la preventa (sin descontar stock aquí)
                // 🔍 Buscar el ingreso (producto) para obtener el almacen_id
                $ingreso = Ingreso::find($detalle['producto_id']);
                $almacenId = $ingreso ? $ingreso->almacen_id : null;

                DetallePreventa::create([
                    'preventa_id' => $preventa->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'tipo_precio' => $detalle['tipo_precio'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                    'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                    'almacen_id' => $almacenId, // ✅ Nuevo campo registrado
                ]);

                Log::info("📦 Detalle guardado con almacen_id: {$almacenId}");

                Log::info("🔍 Producto ID: {$detalle['producto_id']} - Tipo Precio: {$detalle['tipo_precio']}");

                // ✅ Verificar si el producto se vendió a crédito
                if (in_array($detalle['tipo_precio'], $precios_credito)) {
                    Log::info("✅ Producto a crédito detectado: ID {$detalle['producto_id']}");

                    $monto_total_credito += $detalle['subtotal'];
                    $detalle_credito[] = "{$detalle['cantidad']}x Producto ID: {$detalle['producto_id']} - $" . number_format($detalle['subtotal'], 2);
                }
            }

            // ✅ Si la preventa tiene productos a crédito, registrar el cargo en cobranzas
            if ($monto_total_credito > 0) {
                Log::info("💳 Registrando crédito para Cliente ID: {$request->cliente_id}");

                CargosCliente::create([
                    'numero_credito' => 'CR-' . now()->format('Ymd') . '-' . rand(1000, 9999),
                    'cliente_id' => $request->cliente_id,
                    'preventa_id' => $preventa->id,
                    'monto_total' => $monto_total_credito,
                    'saldo_pendiente' => $monto_total_credito,
                    'fecha_vencimiento' => Carbon::parse($request->fecha_entrega)->addDays(15),
                    'estado' => 'Pendiente',
                    'concepto' => implode(', ', $detalle_credito),
                ]);

                Log::info("✅ Crédito registrado correctamente para Cliente ID: {$request->cliente_id}");
            }


            // ✅ Registrar visita automáticamente si viene desde el módulo de rutas
            if ($request->has('registrar_visita') && $request->registrar_visita == 1) {
                $fechaVisita = $request->fecha_visita ?? now()->toDateString();

                // Verifica si ya se registró una visita ese día para ese cliente
                $yaRegistrado = RutaVisita::where('cliente_id', $request->cliente_id)
                    ->where('fecha_visita', $fechaVisita)
                    ->exists();

                if (!$yaRegistrado) {
                    RutaVisita::create([
                        'cliente_id' => $request->cliente_id,
                        'preventista_id' => Auth::id(),
                        'fecha_visita' => $fechaVisita,
                        'ubicacion' => $request->ubicacion ?? 'No disponible',
                        'observaciones' => 'Se realizó pedido',
                    ]);
                }
            }
            Log::info('🔁 Redirigiendo a:', ['desde_ruta' => $request->desde_ruta]);

            DB::commit(); // Confirmamos los cambios en la base de datos

            if ($request->has('desde_ruta')) {
                return redirect()->route('rutas.index')->with('success', 'Preventa creada desde ruta y visita registrada.');
            } else {
                return redirect()->route('preventas.index')->with('success', 'Preventa creada correctamente.');
            }
        } catch (\Exception $e) {
            DB::rollback(); // Si hay error, deshacemos los cambios
            Log::error("❌ Error al registrar la preventa: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al registrar la preventa: ' . $e->getMessage());
        }
    }
    public function crearDesdeRuta(Request $request)
    {
        $clienteId = $request->cliente_id;
        $fecha = $request->fecha ?? now()->toDateString();

        $cliente = Cliente::findOrFail($clienteId);
        $tiposVentas = TipoVenta::all();
        $tiposVentasPermitidos = auth()->user()->tipos_ventas_permitidos;

        if (is_string($tiposVentasPermitidos)) {
            $tiposVentasPermitidos = json_decode($tiposVentasPermitidos, true);
        }

        return view('preventas.crear_preventa', compact(
            'cliente',
            'fecha',
            'tiposVentas',
            'tiposVentasPermitidos'
        ));
    }



    public function edit($id)
    {
        try {
            $preventa = Preventa::with(['detalles.producto'])->findOrFail($id);

            return response()->json([
                'id' => $preventa->id,
                'cliente_id' => $preventa->cliente_id,
                'observaciones' => $preventa->observaciones,
                'fecha_entrega' => optional($preventa->fecha_entrega)->format('Y-m-d'),
                'numero_pedido' => $preventa->numero_pedido,
                'precio_total' => $preventa->precio_total,
                'descuento' => $preventa->descuento,
                'tipo_venta' => $preventa->tipo_venta,
                'detalles' => $preventa->detalles->map(function ($detalle) {
                    return [
                        'producto_id' => $detalle->producto_id,
                        'nombre_producto' => $detalle->producto->nombre_producto ?? 'Producto no encontrado',
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                        'tipo_precio' => $detalle->tipo_precio,
                        // Aseguramos que fecha_vencimiento sea Carbon antes de formatear
                        'fecha_vencimiento' => $detalle->fecha_vencimiento
                            ? (new \Carbon\Carbon($detalle->fecha_vencimiento))->format('Y-m-d')
                            : null
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener la preventa: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo obtener la información de la preventa'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            Log::info("📌 Datos recibidos en update():", $request->all());

            $validatedData = $request->validate([
                'observaciones' => 'nullable|string|max:255',
                'fecha_entrega' => 'required|date',
                'total' => 'required|numeric|min:0',
                'descuento' => 'nullable|numeric|min:0',
                'tipo_venta' => 'required|string',
                'detalles' => 'nullable|array',
                'detalles.*.id' => 'nullable|exists:detalles_preventa,id',
                'detalles.*.producto_id' => 'required|exists:ingresos,id',
                'detalles.*.cantidad' => 'required|integer|min:1',
                'detalles.*.subtotal' => 'required|numeric|min:0',
                'detalles.*.fecha_vencimiento' => 'required|date',
                'detalles.*.tipo_precio' => 'required|string',
                'detalles.*.precio_unitario' => 'required|numeric|min:0',
            ]);

            $preventa = Preventa::findOrFail($id);
            Log::info("📌 Actualizando preventa ID: " . $preventa->id);

            $preventa->update([
                'observaciones' => $validatedData['observaciones'],
                'fecha_entrega' => $validatedData['fecha_entrega'],
                'precio_total' => $validatedData['total'],
                'descuento' => $validatedData['descuento'],
                'tipo_venta' => $validatedData['tipo_venta'],
            ]);

            if (!empty($validatedData['detalles'])) {
                foreach ($validatedData['detalles'] as &$detalle) { // Usamos & para modificar por referencia
                    // 📌 Si el tipo de precio es "bonificación", forzar valores a 0.00
                    if (strtolower($detalle['tipo_precio']) === "bonificación") {
                        $detalle['precio_unitario'] = 0.00;
                        $detalle['subtotal'] = 0.00;
                    }

                    if (isset($detalle['id'])) {
                        DetallePreventa::where('id', $detalle['id'])->update([
                            'producto_id' => $detalle['producto_id'],
                            'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                            'tipo_precio' => $detalle['tipo_precio'],
                            'cantidad' => $detalle['cantidad'],
                            'precio_unitario' => $detalle['precio_unitario'],
                            'subtotal' => $detalle['subtotal'],
                        ]);
                    } else {
                        DetallePreventa::create([
                            'preventa_id' => $preventa->id,
                            'producto_id' => $detalle['producto_id'],
                            'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                            'tipo_precio' => $detalle['tipo_precio'],
                            'cantidad' => $detalle['cantidad'],
                            'precio_unitario' => $detalle['precio_unitario'],
                            'subtotal' => $detalle['subtotal'],
                        ]);
                    }
                }
            }



            Log::info("✅ Preventa actualizada correctamente.");

            return response()->json(['success' => true, 'message' => 'Preventa actualizada correctamente.']);
        } catch (\Exception $e) {
            Log::error("❌ Error en update(): " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar la preventa.'], 500);
        }
    }


    public function obtenerDetalles($preventaId)
    {
        $detalles = DetallePreventa::where('preventa_id', $preventaId)->get();

        if ($detalles->isEmpty()) {
            return response()->json(['message' => 'No se encontraron detalles para esta preventa.'], 404);
        }

        return response()->json($detalles);
    }
    public function obtenerDetallesPreventa($preventaId)
    {
        $detalles = DetallePreventa::where('preventa_id', $preventaId)
            ->with('producto')
            ->get();

        $detallesFormateados = $detalles->map(function ($detalle) {
            return [
                'id' => $detalle->id,  // ✅ Incluir el ID del detalle para la eliminación
                'producto_id' => $detalle->producto_id,
                'nombre_producto' => $detalle->producto->nombre_producto ?? 'Producto desconocido',
                'fecha_vencimiento' => $detalle->fecha_vencimiento,
                'tipo_precio' => $detalle->tipo_precio,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $detalle->subtotal,
            ];
        });

        return response()->json($detallesFormateados);
    }

    public function eliminarProductoDePreventa($detalleId)
    {
        try {
            // 📌 Buscar el detalle directamente por su ID en la tabla `detalles_preventa`
            $detalle = DetallePreventa::find($detalleId);

            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado en la preventa.'], 404);
            }

            // 📌 Guardamos el preventa_id antes de eliminar para futuras validaciones
            $preventaId = $detalle->preventa_id;

            // 📌 Eliminamos el detalle
            $detalle->delete();

            // 📌 Verificamos si la preventa aún tiene productos
            $productosRestantes = DetallePreventa::where('preventa_id', $preventaId)->count();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente.',
                'productos_restantes' => $productosRestantes
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar producto.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $preventa = Preventa::with(['detalles.producto', 'cliente'])->findOrFail($id);

        return response()->json([
            'cliente' => $preventa->cliente,
            'detalles' => $preventa->detalles->map(function ($detalle) {
                return [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'nombre_producto' => $detalle->producto->nombre_producto ?? 'Producto no encontrado',
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                    'tipo_precio' => $detalle->tipo_precio,
                ];
            }),
            'observaciones' => $preventa->observaciones,
            'fecha_entrega' => $preventa->fecha_entrega,
        ]);
    }
    public function eliminarDetallePreventa(Request $request, $detalleId)
    {
        try {
            $detalle = DetallePreventa::find($detalleId);

            if (!$detalle) {
                return response()->json(['success' => false, 'message' => 'Detalle no encontrado.'], 404);
            }

            // 📌 Eliminar el detalle de la preventa
            $detalle->delete();

            return response()->json(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar producto.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $preventa = Preventa::findOrFail($id);

        // ✅ Eliminar detalles antes de eliminar la preventa
        $preventa->detalles()->delete();

        $preventa->delete();

        return redirect()->route('preventas.index')->with('success', 'Preventa eliminada correctamente.');
    }

    public function generarPDF($id)
    {
        // Buscar la preventa con sus relaciones necesarias
        $preventa = Preventa::with(['cliente', 'detalles', 'preventista'])->findOrFail($id);

        // Determinar la modalidad de pago
        $tiposPrecios = $preventa->detalles->pluck('tipo_precio');
        $modalidades = [];

        if ($tiposPrecios->contains(function ($tipo) {
            return in_array($tipo, [
                'precio_unidad_contado',
                'precio_caja_contado',
                'precio_cajon_contado'
            ]);
        })) {
            $modalidades[] = 'Contado';
        }

        if ($tiposPrecios->contains(function ($tipo) {
            return in_array($tipo, [
                'precio_unidad_credito',
                'precio_caja_credito',
                'precio_cajon_credito'
            ]);
        })) {
            $modalidades[] = 'Crédito';
        }

        $modalidadPago = implode(' y ', $modalidades);

        // Calcular la fecha de pago
        $fechaPago = null;
        if ($modalidadPago === 'Contado') {
            $fechaPago = $preventa->fecha_entrega;
        } elseif ($modalidadPago === 'Crédito') {
            $fechaPago = \Carbon\Carbon::parse($preventa->fecha_entrega)->addDays(15)->format('Y-m-d');
        } elseif ($modalidadPago === 'Crédito y Contado') {
            $fechaPago = \Carbon\Carbon::parse($preventa->fecha_entrega)->addDays(15)->format('Y-m-d');
        }

        // Preparar los detalles para el PDF
        $detalles = $preventa->detalles->map(function ($detalle) {
            return [
                'cantidad' => $detalle->cantidad,
                'producto' => $detalle->producto->nombre_producto ?? 'Producto no encontrado',
                'unidad' => $detalle->tipo_precio,
                'precio_unitario' => number_format($detalle->precio_unitario, 2),
                'subtotal' => number_format($detalle->subtotal, 2),
            ];
        });

        // Datos a enviar a la vista PDF
        $data = [
            'preventa' => $preventa,
            'detalles' => $detalles,
            'modalidadPago' => $modalidadPago,
            'fechaPago' => $fechaPago,
            'formaPedido' => $preventa->preventista->nombre ?? 'Desconocido', // Nombre del usuario preventista
        ];

        // Generar la vista en PDF
        $pdf = PDF::loadView('preventas.pdf', $data);

        // Descargar el PDF con un nombre específico
        return $pdf->stream('preventa_' . $preventa->numero_pedido . '.pdf');
    }
    public function generarNotaRemision($id)
    {
        $user = Auth::user();
        if (!in_array($user->rol, ['usuario_operador', 'administrador'])) {
            abort(403, 'No tienes permiso para generar la Nota de Remisión.');
        }

        // Buscar la preventa con sus relaciones necesarias
        $preventa = Preventa::with(['cliente', 'detalles.producto', 'preventista'])->findOrFail($id);

        // Determinar la modalidad de pago en función de los productos
        $tiposPrecios = $preventa->detalles->pluck('tipo_precio');
        $modalidades = [];

        if ($tiposPrecios->contains(fn($tipo) => in_array($tipo, ['precio_unidad_contado', 'precio_caja_contado', 'precio_cajon_contado']))) {
            $modalidades[] = 'Contado';
        }

        if ($tiposPrecios->contains(fn($tipo) => in_array($tipo, ['precio_unidad_credito', 'precio_caja_credito', 'precio_cajon_credito']))) {
            $modalidades[] = 'Crédito';
        }

        $modalidadPago = implode(' y ', $modalidades);

        // Calcular la fecha de pago
        $fechaPago = null;
        if ($modalidadPago === 'Contado') {
            $fechaPago = $preventa->fecha_entrega;
        } elseif ($modalidadPago === 'Crédito' || $modalidadPago === 'Crédito y Contado') {
            $fechaPago = \Carbon\Carbon::parse($preventa->fecha_entrega)->addDays(15)->format('Y-m-d');
        }

        // Preparar los detalles, incluyendo Lote y Fecha de Vencimiento
        $detalles = $preventa->detalles->map(function ($detalle) {
            return [
                'codigo' => $detalle->producto->codigo_producto ?? 'Código no disponible',
                'producto' => $detalle->producto->nombre_producto ?? 'Producto no encontrado',
                'lote' => $detalle->producto->lote ?? 'N/A', // ✅ Se agrega el lote
                'fecha_vencimiento' => $detalle->producto->fecha_vencimiento ?? 'N/A', // ✅ Se agrega la fecha de vencimiento
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => number_format($detalle->precio_unitario, 2),
                'subtotal' => number_format($detalle->subtotal, 2),
            ];
        });

        // Datos para la vista
        $data = [
            'preventa' => $preventa,
            'detalles' => $detalles,
            'fecha' => now()->format('d/m/Y H:i:s'),
            'usuario' => $user->name, // Usuario actual que genera el PDF
            'modalidadPago' => $modalidadPago,
            'fechaPago' => $fechaPago,
        ];

        // Generar PDF
        $pdf = PDF::loadView('preventas.nota-remision', $data);

        return $pdf->stream('nota_remision_' . $preventa->numero_pedido . '.pdf');
    }
    public function generarPDFConFiltros(Request $request)
    {
        $query = Preventa::with(['cliente', 'preventista']);

        if ($request->filled('fecha_filtro')) {
            $query->whereDate('created_at', $request->fecha_filtro);
        }

        if ($request->filled('cliente_filtro')) {
            $query->where('cliente_id', $request->cliente_filtro);
        }

        if ($request->filled('preventista_filtro')) {
            $query->where('preventista_id', $request->preventista_filtro);
        }

        $preventas = $query->get();

        // Encabezado con los filtros aplicados
        $filtros = [
            'Fecha' => $request->fecha_filtro ?? 'Todos',
            'Cliente' => optional(Cliente::find($request->cliente_filtro))->nombre_propietario ?? 'Todos',
            'Preventista' => optional(User::find($request->preventista_filtro))->nombre ?? 'Todos',
        ];

        // Renderizar la vista del PDF
        $pdf = Pdf::loadView('preventas.pdf_reporte', compact('preventas', 'filtros'));

        // Descargar el PDF con un nombre dinámico
        return $pdf->stream('Reporte_Preventas_' . now()->format('Ymd_His') . '.pdf');
    }
    public function eliminarMultiples(Request $request)
    {
        $ids = $request->input('preventas_seleccionadas', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No seleccionaste ninguna preventa.');
        }

        // ✅ Eliminar los detalles relacionados primero
        \App\Models\DetallePreventa::whereIn('preventa_id', $ids)->delete();

        // ✅ Luego eliminar las preventas
        \App\Models\Preventa::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', 'Preventas seleccionadas eliminadas correctamente.');
    }
}
