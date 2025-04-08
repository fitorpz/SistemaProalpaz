<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CargosCliente;
use App\Models\DetalleCargoCliente;
use App\Models\AbonosCliente;
use App\Models\Cliente;
use App\Models\Preventa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\Almacen;

class CobranzasController extends Controller
{
    // Mostrar la lista de créditos
    public function index()
    {
        return view('contabilidad.cobranzas.index');
    }
    public function crearCuentaExterna()
    {
        return view('contabilidad.cobranzas.crear_externa');
    }
    public function guardarCuentaExterna(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'concepto' => 'required|string|max:255',
            'monto_total' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date',
            'estado' => 'required|in:Activo,Inactivo',
            'observaciones' => 'nullable|string',
        ]);

        DB::table('cuentas_por_cobrar')->insert([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'concepto' => $request->concepto,
            'monto_total' => $request->monto_total,
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
            'fecha_registro' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('contabilidad.index')->with('success', 'Cuenta externa registrada correctamente.');
    }

    public function estadoCuentas(Request $request)
    {
        $clienteFiltro = $request->input('cliente_filtro');
        $almacenFiltro = $request->input('almacen_id');

        // 👉 Clientes y almacenes para los filtros
        $clientes = Cliente::select('id', 'nombre_propietario', 'nombre_comercio')->get();
        $almacenes = Almacen::select('id', 'nombre')->get();

        // 👉 Base de la consulta con relaciones necesarias
        $query = CargosCliente::with(['cliente', 'preventa.detalles']);

        // 🔎 Filtro por cliente (si aplica)
        if (!empty($clienteFiltro)) {
            $query->where('cliente_id', $clienteFiltro);
        }

        // 🔎 Filtro por almacén (si aplica)
        if (!empty($almacenFiltro)) {
            $query->whereHas('preventa.detalles', function ($q) use ($almacenFiltro) {
                $q->where('almacen_id', $almacenFiltro);
            });
        }

        // 👉 Agrupamiento por cliente y cálculo de totales
        $cargos = $query->select(
            'cliente_id',
            DB::raw('SUM(monto_total) as monto_total'),
            DB::raw('SUM(saldo_pendiente) as saldo_pendiente'),
            DB::raw("CASE 
            WHEN SUM(saldo_pendiente) = 0 THEN 'Pagado'
            WHEN SUM(saldo_pendiente) < SUM(monto_total) THEN 'Parcialmente Pagado'
            ELSE 'Pendiente'
         END as estado")
        )
            ->groupBy('cliente_id')
            ->get();

        // ✅ Calcular el total global de saldo pendiente correctamente
        $totalGenerado = $cargos->sum('saldo_pendiente');

        // 👉 Retornar la vista
        return view('contabilidad.cobranzas.estado_cuentas', compact('cargos', 'clientes', 'almacenes', 'totalGenerado'));
    }


    public function verCuentasExternas(Request $request)
    {
        $query = DB::table('cuentas_por_cobrar');

        if ($request->filled('filtro_nombre')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->filtro_nombre . '%')
                    ->orWhere('categoria', 'like', '%' . $request->filtro_nombre . '%');
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $cuentas = $query->orderBy('fecha_vencimiento', 'asc')->get();

        return view('contabilidad.cobranzas.externas_index', compact('cuentas'));
    }
    public function verHistorialCuentaExterna($id)
    {
        $cuenta = DB::table('cuentas_por_cobrar')->where('id', $id)->first();

        if (!$cuenta) {
            return redirect()->route('contabilidad.cobranzas.externas.index')->with('error', 'Cuenta no encontrada.');
        }

        return view('contabilidad.cobranzas.externas_historial', compact('cuenta'));
    }




    public function buscarClientes(Request $request)
    {
        $query = $request->input('q');

        // 👉 Solo traer IDs de clientes con al menos un crédito con saldo pendiente
        $clientesConCredito = CargosCliente::where('saldo_pendiente', '>', 0)
            ->pluck('cliente_id')
            ->unique();

        // 🔍 Buscar clientes por nombre o comercio dentro de los que tienen crédito activo
        $clientes = Cliente::whereIn('id', $clientesConCredito)
            ->where(function ($q) use ($query) {
                $q->where('nombre_propietario', 'like', "%{$query}%")
                    ->orWhere('nombre_comercio', 'like', "%{$query}%");
            })
            ->select('id', 'nombre_propietario', 'nombre_comercio')
            ->limit(10)
            ->get();

        return response()->json($clientes);
    }

    public function buscarTodosLosClientes(Request $request)
    {
        $query = $request->input('q');

        $clientes = Cliente::where('nombre_propietario', 'like', "%{$query}%")
            ->orWhere('nombre_comercio', 'like', "%{$query}%")
            ->select('id', 'nombre_propietario', 'nombre_comercio')
            ->limit(10)
            ->get();

        return response()->json($clientes);
    }

    public function historialPagos(Request $request, $cliente_id)
    {
        $cliente = Cliente::findOrFail($cliente_id);

        // ✅ Obtener parámetros de búsqueda desde la URL
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $filtroReferencia = $request->input('filtro_referencia');
        $estadoCredito = $request->input('estado_credito');

        // ✅ Obtener créditos (ventas a crédito) activos con filtros aplicados
        $cargos = DB::table('cargos_clientes')
            ->where('cliente_id', $cliente_id)
            ->when($filtroReferencia, function ($query) use ($filtroReferencia) {
                return $query->where('numero_credito', 'like', "%{$filtroReferencia}%");
            })
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                return $query->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                return $query->whereDate('created_at', '<=', $fechaHasta);
            })
            ->when($estadoCredito, function ($query) use ($estadoCredito) {
                return $query->where(DB::raw("
                CASE 
                    WHEN saldo_pendiente = 0 THEN 'Pagado'
                    WHEN saldo_pendiente < monto_total THEN 'Parcialmente Pagado'
                    ELSE 'Pendiente'
                END"), $estadoCredito);
            })
            ->select(
                'id',
                DB::raw('created_at as fecha'),
                DB::raw("'Crédito' as tipo"),
                'numero_credito as referencia',
                DB::raw("'Venta a crédito' as concepto"),
                'monto_total as cargo',
                DB::raw('0 as pago'),
                'saldo_pendiente',
                DB::raw('DATE_ADD(created_at, INTERVAL 15 DAY) as fecha_vencimiento'),
                DB::raw("CASE 
                    WHEN saldo_pendiente = 0 THEN 'Pagado'
                    WHEN saldo_pendiente < monto_total THEN 'Parcialmente Pagado'
                    ELSE 'Pendiente'
                END as estado"),
                DB::raw("NULL as metodo_pago"), // ✅ Se agrega un campo vacío para créditos
                DB::raw("NULL as comprobante_pago") // ✅ Se agrega un campo vacío para créditos
            );

        // ✅ Obtener pagos (abonos) vinculados a cada crédito con filtros aplicados
        $abonos = DB::table('abonos_clientes')
            ->where('cliente_id', $cliente_id)
            ->when($filtroReferencia, function ($query) use ($filtroReferencia) {
                return $query->where('numero_credito', 'like', "%{$filtroReferencia}%");
            })
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                return $query->whereDate('fecha_pago', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                return $query->whereDate('fecha_pago', '<=', $fechaHasta);
            })
            ->select(
                'id',
                DB::raw('fecha_pago as fecha'),
                DB::raw("'Abono' as tipo"),
                'numero_credito as referencia',
                'referencia_pago as concepto',
                DB::raw('0 as cargo'),
                'monto_abonado as pago',
                'saldo_pendiente',
                'fecha_pago as fecha_vencimiento',
                DB::raw("'N/A' as estado"), // ✅ Los abonos no tienen estado
                'metodo_pago', // ✅ Agregar el campo metodo_pago
                'comprobante_pago' // ✅ Agregar el campo comprobante_pago
            );

        // ✅ Unimos ambas consultas, asegurando que los créditos aparezcan antes
        $historial = $cargos->unionAll($abonos)
            ->orderBy('referencia', 'asc') // ✅ Agrupa por número de crédito
            ->orderBy('tipo', 'desc') // ✅ Primero los créditos, luego los abonos (mismo crédito)
            ->orderBy('fecha', 'asc') // ✅ Mantiene el orden cronológico
            ->get();

        // ✅ Obtener la última preventa del cliente (si existe)
        $preventa = Preventa::where('cliente_id', $cliente_id)->orderBy('created_at', 'desc')->first();

        return view('contabilidad.cobranzas.historial', compact('cliente', 'historial', 'preventa'));
    }


    public function generarPDFConFiltros(Request $request)
    {
        $cliente_id = $request->input('cliente_id');
        $cliente = Cliente::findOrFail($cliente_id);

        // ✅ Obtener la última preventa del cliente (si existe)
        $preventa = Preventa::where('cliente_id', $cliente_id)->orderBy('created_at', 'desc')->first();

        // ✅ Obtener parámetros de búsqueda desde la URL
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $filtroReferencia = $request->input('filtro_referencia');
        $estadoCredito = $request->input('estado_credito');

        // ✅ Obtener créditos (ventas a crédito) activos con filtros aplicados
        $cargos = DB::table('cargos_clientes')
            ->where('cliente_id', $cliente_id)
            ->when($filtroReferencia, function ($query) use ($filtroReferencia) {
                return $query->where('numero_credito', 'like', "%{$filtroReferencia}%");
            })
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                return $query->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                return $query->whereDate('created_at', '<=', $fechaHasta);
            })
            ->when($estadoCredito, function ($query) use ($estadoCredito) {
                return $query->where('estado', $estadoCredito);
            })
            ->select(
                'id',
                DB::raw('created_at as fecha'),
                DB::raw("'Crédito' as tipo"),
                'numero_credito as referencia',
                DB::raw("'Venta a crédito' as concepto"),
                'monto_total as cargo',
                DB::raw('0 as pago'),
                'saldo_pendiente',
                DB::raw('DATE_ADD(created_at, INTERVAL 15 DAY) as fecha_vencimiento')
            );

        // ✅ Obtener pagos (abonos) vinculados a cada crédito con filtros aplicados
        $abonos = DB::table('abonos_clientes')
            ->where('cliente_id', $cliente_id)
            ->when($filtroReferencia, function ($query) use ($filtroReferencia) {
                return $query->where('numero_credito', 'like', "%{$filtroReferencia}%");
            })
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                return $query->whereDate('fecha_pago', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                return $query->whereDate('fecha_pago', '<=', $fechaHasta);
            })
            ->select(
                'id',
                DB::raw('fecha_pago as fecha'),
                DB::raw("'Abono' as tipo"),
                'numero_credito as referencia',
                'referencia_pago as concepto',
                DB::raw('0 as cargo'),
                'monto_abonado as pago',
                'saldo_pendiente',
                'fecha_pago as fecha_vencimiento'
            );

        // ✅ Unimos ambas consultas, asegurando que los créditos aparezcan antes
        $historial = $cargos->unionAll($abonos)
            ->orderBy('referencia', 'asc')
            ->orderBy('tipo', 'desc')
            ->orderBy('fecha', 'asc')
            ->get();

        // ✅ Generar PDF con datos filtrados y enviando la preventa para mostrar el preventista
        $pdf = PDF::loadView('contabilidad.cobranzas.pdf.historial', compact('cliente', 'historial', 'preventa'));

        return $pdf->stream('Historial_Cobranzas_' . $cliente->id . '.pdf');
    }


    // Registrar un nuevo crédito (cargo)
    public function storeCredito(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'preventa_id' => 'required|exists:preventas,id',
            'monto_total' => 'required|numeric',
            'concepto' => 'required|string'
        ]);

        $preventa = Preventa::findOrFail($request->preventa_id);
        $fecha_vencimiento = Carbon::parse($preventa->fecha_entrega)->addDays(15);

        $credito = CargosCliente::create([
            'numero_credito' => 'CR-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'cliente_id' => $request->cliente_id,
            'preventa_id' => $request->preventa_id,
            'monto_total' => $request->monto_total,
            'dias_credito' => 15,
            'fecha_vencimiento' => $fecha_vencimiento,
            'saldo_pendiente' => $request->monto_total,
            'estado' => 'Pendiente',
            'concepto' => $request->concepto
        ]);

        return redirect()->route('contabilidad.cobranzas.index')->with('success', 'Credito registrado exitosamente');
    }

    public function storeAbono(Request $request)
    {
        Log::info('📌 Datos recibidos para registrar abono:', $request->all());

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'cargo_cliente_id' => 'required|exists:cargos_clientes,id',
            'monto_abonado' => 'required|numeric|min:1',
            'metodo_pago' => 'required|in:Efectivo,Transferencia,Cheque,Tarjeta,QR',
            'referencia_pago' => 'nullable|string|max:255',
            'comprobante_pago' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ]);

        $cliente = Cliente::findOrFail($request->cliente_id);
        $cargo = CargosCliente::findOrFail($request->cargo_cliente_id);

        if ($cargo->saldo_pendiente <= 0) {
            return response()->json(['success' => false, 'message' => 'Este crédito ya está pagado.']);
        }

        $nuevo_saldo = $cargo->saldo_pendiente - $request->monto_abonado;

        // ✅ Guardar el comprobante si existe
        $comprobantePath = null;
        // ✅ Verificar si el archivo se está subiendo correctamente
        if ($request->hasFile('comprobante_pago')) {
            $archivo = $request->file('comprobante_pago');
            if ($archivo->isValid()) {
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $archivo->storeAs('public/comprobantes', $nombreArchivo);

                // Guardar la ruta accesible en la base de datos
                $comprobantePath = 'storage/comprobantes/' . $nombreArchivo;
            }
            if (!$request->file('comprobante_pago')->isValid()) {
                Log::error('❌ Error: El archivo de comprobante de pago no es válido.');
                return response()->json(['success' => false, 'message' => 'El archivo de comprobante no es válido.']);
            }

            try {
                // Guardar el archivo en storage/app/public/comprobantes con un nombre único
                $comprobantePath = $request->file('comprobante_pago')->storeAs(
                    'comprobantes',
                    time() . '_' . $request->file('comprobante_pago')->getClientOriginalName(),
                    'public'
                );

                Log::info("✅ Comprobante guardado correctamente en: storage/app/public/$comprobantePath");
            } catch (\Exception $e) {
                Log::error('❌ Error al guardar el comprobante: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Error al guardar el comprobante de pago.']);
            }
        } else {
            Log::warning('⚠️ No se recibió ningún archivo de comprobante.');
            $comprobantePath = null;
        }


        // ✅ Registrar el abono en la base de datos
        try {
            $abono = AbonosCliente::create([
                'fecha_pago' => now(),
                'cliente_id' => $cliente->id,
                'cargo_cliente_id' => $cargo->id,
                'nombre_cliente' => $cliente->nombre_propietario,
                'numero_credito' => $cargo->numero_credito,
                'monto_abonado' => $request->monto_abonado,
                'saldo_pendiente' => max($nuevo_saldo, 0),
                'concepto' => 'Abono a crédito #' . $cargo->numero_credito,
                'metodo_pago' => $request->metodo_pago,
                'referencia_pago' => $request->referencia_pago ?? null,
                'comprobante_pago' => $comprobantePath,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al registrar el abono: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al registrar el abono.']);
        }

        $cargo->saldo_pendiente = max(0, $nuevo_saldo);
        $cargo->estado = $nuevo_saldo <= 0 ? 'Pagado' : 'Parcialmente Pagado';
        $cargo->save();

        Log::info("✅ Abono registrado correctamente: ID {$abono->id}");

        return response()->json([
            'success' => true,
            'message' => 'Abono registrado correctamente',
            'pdf_url' => route('contabilidad.cobranzas.descargarRecibo', ['abono_id' => $abono->id])
        ]);
    }

    public function generarPDFCobranzas(Request $request)
    {
        $clienteFiltro = $request->input('cliente_filtro');

        // ✅ Obtener clientes para mostrar en el PDF
        $clientes = Cliente::select('id', 'nombre_propietario', 'nombre_comercio')->get();
        // ✅ Obtener la última preventa de algún cliente para mostrar datos en el PDF (opcional)
        $preventa = Preventa::orderBy('created_at', 'desc')->first();

        // ✅ Consulta base de créditos
        $query = CargosCliente::with('cliente');

        if (!empty($clienteFiltro)) {
            $query->where('cliente_id', $clienteFiltro);
        }

        $cargos = $query->select(
            'cliente_id',
            DB::raw('SUM(monto_total) as monto_total'),
            DB::raw('SUM(saldo_pendiente) as saldo_pendiente'),
            DB::raw("CASE 
            WHEN SUM(saldo_pendiente) = 0 THEN 'Pagado'
            WHEN SUM(saldo_pendiente) < SUM(monto_total) THEN 'Parcialmente Pagado'
            ELSE 'Pendiente'
         END as estado")
        )
            ->groupBy('cliente_id')
            ->get();

        // ✅ Cargar la vista del PDF y enviar los datos
        $pdf = Pdf::loadView('contabilidad.cobranzas.pdf.index', compact('cargos', 'clientes'));

        return $pdf->stream('Cobranzas_Listado.pdf'); // 📌 Se mostrará en una nueva pestaña
    }



    public function descargarRecibo($abono_id)
    {
        $abono = AbonosCliente::findOrFail($abono_id);
        $cliente = Cliente::findOrFail($abono->cliente_id);
        $cargo = CargosCliente::findOrFail($abono->cargo_cliente_id);

        // ✅ Obtener la fecha de vencimiento del crédito
        $fecha_vencimiento = Carbon::parse($cargo->fecha_vencimiento)->format('d/m/Y');

        $pdf = Pdf::loadView('contabilidad.cobranzas.pdf.recibo', [
            'abono' => $abono,
            'cliente' => $cliente,
            'cargo' => $cargo,
            'fecha_vencimiento' => $fecha_vencimiento
        ]);

        return $pdf->download("Recibo_{$abono->id}.pdf");
    }

    public function generarRecibo($abono_id)
    {
        $abono = AbonosCliente::findOrFail($abono_id);
        $cargo = CargosCliente::findOrFail($abono->cargo_cliente_id);
        $cliente = Cliente::findOrFail($abono->cliente_id);

        // ✅ Obtener la fecha de vencimiento del crédito
        $fecha_vencimiento = Carbon::parse($cargo->fecha_vencimiento)->format('d/m/Y');

        // ✅ Generar el PDF y mostrarlo en una nueva pestaña
        $pdf = Pdf::loadView('contabilidad.cobranzas.pdf.recibo', [
            'abono' => $abono,
            'cliente' => $cliente,
            'cargo' => $cargo,
            'fecha_vencimiento' => $fecha_vencimiento
        ]);

        return $pdf->stream("Recibo_{$abono->id}.pdf"); // 🔹 Mostrar sin descargar
    }
    public function crearCredito()
    {
        $clientes = Cliente::all();
        return view('contabilidad.cobranzas.crear_credito', compact('clientes'));
    }
    public function crearAbono()
    {
        return view('contabilidad.cobranzas.abono');
    }
    public function guardarCreditoManual(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'monto_total' => 'required|numeric|min:1',
            'dias_credito' => 'nullable|integer|min:1',
            'concepto' => 'required|string|max:255'
        ]);

        $diasCredito = $request->input('dias_credito', 15);
        $fechaVencimiento = now()->addDays($diasCredito);

        $credito = CargosCliente::create([
            'numero_credito' => 'CR-' . now()->format('Ymd') . '-' . rand(1000, 9999),
            'cliente_id' => $request->cliente_id,
            'preventa_id' => null,
            'monto_total' => $request->monto_total,
            'dias_credito' => $diasCredito,
            'fecha_vencimiento' => $fechaVencimiento,
            'saldo_pendiente' => $request->monto_total,
            'estado' => 'Pendiente',
            'concepto' => $request->concepto
        ]);

        return redirect()->route('contabilidad.cobranzas.index')->with('success', 'Crédito registrado manualmente.');
    }
}
