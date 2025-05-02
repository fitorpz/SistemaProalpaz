<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Cliente;
use App\Models\Preventa;
use App\Models\Ingreso;
use App\Models\Picking;
use App\Models\DetallePreventa;
use App\Models\Ruta;
use App\Models\User;
use App\Models\Almacen;


class ReportesController extends Controller
{
    public function vistaClientes(Request $request)
    {
        $clientes = Cliente::query()
            ->when($request->nombre, fn($q, $nombre) => $q->where('nombre_propietario', 'LIKE', "%$nombre%"))
            ->when($request->codigo_cliente, fn($q, $codigo) => $q->where('codigo_cliente', $codigo))
            ->when($request->nit, fn($q, $nit) => $q->where('nit', $nit))
            ->get();

        return view('reportes.clientes', compact('clientes'));
    }

    public function vistaIngresos(Request $request)
    {
        $ingresos = Ingreso::query()
            ->when($request->nombre_producto, fn($q, $nombre) => $q->where('nombre_producto', 'LIKE', "%$nombre%"))
            ->when($request->codigo_producto, fn($q, $codigo) => $q->where('codigo_producto', $codigo))
            ->when($request->tipo_ingreso, fn($q, $tipo) => $q->where('tipo_ingreso', $tipo))
            ->get();

        return view('reportes.ingresos', compact('ingresos'));
    }

    public function vistaPreventas(Request $request)
    {
        $preventas = Preventa::with('cliente')
            ->when($request->numero_pedido, fn($q, $numero) => $q->where('numero_pedido', 'LIKE', "%$numero%"))
            ->when($request->cliente, fn($q, $cliente) => $q->whereHas('cliente', fn($qc) => $qc->where('nombre_comercio', 'LIKE', "%$cliente%")))
            ->when($request->estado, fn($q, $estado) => $q->where('estado', $estado))
            ->get();

        return view('reportes.preventas', compact('preventas'));
    }

    public function vistaRutas(Request $request)
    {
        // Obtener la lista de preventistas
        $preventistas = User::where('rol', 'preventista')->get();

        // Obtener los filtros desde el request
        $fecha = $request->input('fecha_visita');
        $preventistaId = $request->input('preventista');

        // Consulta de rutas con relaciones
        $query = Ruta::with(['cliente', 'preventista', 'visita']);

        // Aplicar filtro de fecha si se proporciona
        if ($fecha) {
            $query->whereDate('fecha_visita', $fecha);
        }

        // Aplicar filtro de preventista si se proporciona
        if ($preventistaId) {
            $query->where('preventista_id', $preventistaId);
        }

        // Obtener las rutas filtradas
        $rutas = $query->get();

        return view('reportes.rutas', compact('preventistas', 'rutas', 'fecha', 'preventistaId'));
    }


    public function pdfClientes(Request $request)
    {
        $clientes = Cliente::query()
            ->when($request->nombre, fn($q, $nombre) => $q->where('nombre_propietario', 'LIKE', "%$nombre%"))
            ->when($request->codigo_cliente, fn($q, $codigo) => $q->where('codigo_cliente', $codigo))
            ->when($request->nit, fn($q, $nit) => $q->where('nit', $nit))
            ->get();

        $pdf = Pdf::loadView('reportes.pdf.clientes', compact('clientes'));

        return $pdf->download('Reporte_Clientes.pdf');
    }

    public function pdfIngresos(Request $request)
    {
        $ingresos = Ingreso::query()
            ->when($request->nombre_producto, fn($q, $nombre) => $q->where('nombre_producto', 'LIKE', "%$nombre%"))
            ->when($request->codigo_producto, fn($q, $codigo) => $q->where('codigo_producto', $codigo))
            ->when($request->tipo_ingreso, fn($q, $tipo) => $q->where('tipo_ingreso', $tipo))
            ->get();

        $pdf = Pdf::loadView('reportes.pdf.ingresos', compact('ingresos'));

        return $pdf->download('Reporte_Ingresos.pdf');
    }

    public function pdfPreventas(Request $request)
    {
        $preventas = Preventa::with('cliente')
            ->when($request->numero_pedido, fn($q, $numero) => $q->where('numero_pedido', 'LIKE', "%$numero%"))
            ->when($request->cliente, fn($q, $cliente) => $q->whereHas('cliente', fn($qc) => $qc->where('nombre_comercio', 'LIKE', "%$cliente%")))
            ->when($request->estado, fn($q, $estado) => $q->where('estado', $estado))
            ->get();

        $pdf = Pdf::loadView('reportes.pdf.preventas', compact('preventas'));

        return $pdf->download('Reporte_Preventas.pdf');
    }

    public function pdfRutas(Request $request)
    {
        $rutas = Ruta::with(['cliente', 'preventista'])
            ->when($request->fecha_visita, fn($q, $fecha) => $q->whereDate('fecha_visita', $fecha))
            ->when($request->preventista, fn($q, $nombre) => $q->whereHas('preventista', fn($qp) => $qp->where('nombre', 'LIKE', "%$nombre%")))
            ->get();

        $pdf = Pdf::loadView('reportes.pdf.rutas', compact('rutas'));

        return $pdf->download('Reporte_Rutas.pdf');
    }
    // MÉTODO ventas()
    public function ventas()
    {
        return view('reportes.ventas.index');
    }
    public function ventasGeneral(Request $request)
    {
        // Obtener todos los almacenes para el filtro
        $almacenes = Almacen::all();

        // Cargar detalles de preventa con relaciones necesarias
        $detalles = DetallePreventa::with([
            'producto.almacen',
            'preventa.preventista',
            'preventa',
            'preventa.cliente'
        ])->get();

        $ventas = [];

        foreach ($detalles as $detalle) {
            $almacenObj = $detalle->producto->almacen ?? null;
            $fechaEntrega = optional($detalle->preventa)->fecha_entrega;

            // Validación: si hay filtro por mes y no coincide, salta
            if ($request->filled('mes') && $fechaEntrega) {
                $mesEntrega = \Carbon\Carbon::parse($fechaEntrega)->month;
                if ((int)$request->mes !== $mesEntrega) {
                    continue;
                }
            }
            // Si se aplicó el filtro por almacén, y no coincide, se ignora
            if ($request->filled('almacen_id') && $almacenObj && $almacenObj->id != $request->almacen_id) {
                continue;
            }

            $almacen = $almacenObj->nombre ?? 'Sin almacén';
            $vendedor = $detalle->preventa->preventista->nombre ?? 'Sin nombre';

            if (!isset($ventas[$almacen])) {
                $ventas[$almacen] = [];
            }

            if (!isset($ventas[$almacen][$vendedor])) {
                $ventas[$almacen][$vendedor] = ['credito' => 0, 'contado' => 0];
            }

            if (str_contains($detalle->tipo_precio, 'credito')) {
                $ventas[$almacen][$vendedor]['credito'] += $detalle->subtotal;
            } elseif (str_contains($detalle->tipo_precio, 'contado')) {
                $ventas[$almacen][$vendedor]['contado'] += $detalle->subtotal;
            }
        }

        return view('reportes.ventas.reporte_general', compact('ventas', 'almacenes', 'request'));
    }
    public function ventasDetallado(Request $request)
    {
        // Obtener usuarios preventistas y operadores
        $usuarios = User::where(function ($query) {
            $query->where('rol', 'like', '%ventas%')->orWhere('rol', 'like', '%operador%');
        })->get();

        // Obtener almacenes para el filtro
        $almacenes = Almacen::all();

        // Obtener todos los detalles con relaciones necesarias
        $detalles = DetallePreventa::with([
            'preventa.cliente',
            'preventa.preventista',
            'preventa.cargo',
            'producto.almacen'
        ])
            ->whereHas('preventa', function ($query) use ($request) {
                if ($request->filled('usuario_id')) {
                    $query->where('preventista_id', $request->usuario_id);
                }
                if ($request->filled('del')) {
                    $query->whereDate('fecha_entrega', '>=', $request->del);
                }
                if ($request->filled('al')) {
                    $query->whereDate('fecha_entrega', '<=', $request->al);
                }
                if ($request->filled('mes')) {
                    $query->whereMonth('fecha_entrega', $request->mes);
                }
            })
            ->get()
            ->when($request->filled('almacen_id'), function ($items) use ($request) {
                return $items->filter(fn($d) => $d->producto->almacen_id == $request->almacen_id);
            });

        // Clasificación por tipo de precio
        $ventasCredito = $detalles->filter(fn($d) => str_contains($d->tipo_precio, 'credito'));
        $ventasContado = $detalles->filter(fn($d) => str_contains($d->tipo_precio, 'contado'));
        $ventasPromocion = $detalles->filter(fn($d) => $d->tipo_precio === 'precio_promocion');

        return view('reportes.ventas.reporte_detallado', [
            'usuarios' => $usuarios,
            'almacenes' => $almacenes,
            'ventasCredito' => $ventasCredito,
            'ventasContado' => $ventasContado,
            'ventasPromocion' => $ventasPromocion,
            'totalCredito' => $ventasCredito->sum('subtotal'),
            'totalContado' => $ventasContado->sum('subtotal'),
            'totalPromocion' => $ventasPromocion->sum('subtotal'),
            'request' => $request
        ]);
    }



    public function pdfVentas(Request $request)
    {
        // Traer todos los detalles_preventa con relaciones
        $detalles = DetallePreventa::with(['preventa.cliente', 'preventa.preventista', 'producto'])
            ->whereHas('preventa', function ($query) use ($request) {
                if ($request->filled('usuario_id')) {
                    $query->where('preventista_id', $request->usuario_id);
                }

                if ($request->filled('del')) {
                    $query->whereDate('fecha_entrega', '>=', $request->del);
                }

                if ($request->filled('al')) {
                    $query->whereDate('fecha_entrega', '<=', $request->al);
                }
                if ($request->filled('mes')) {
                    $query->whereMonth('fecha_entrega', $request->mes);
                }
            })
            ->get();

        // Separar por tipo de precio
        $ventasCredito = $detalles->filter(fn($item) => str_contains($item->tipo_precio, 'credito'));
        $ventasContado = $detalles->filter(fn($item) => str_contains($item->tipo_precio, 'contado'));
        $ventasPromocion = $detalles->filter(fn($item) => $item->tipo_precio === 'precio_promocion');

        // Calcular totales
        $totalCredito = $ventasCredito->sum('subtotal');
        $totalContado = $ventasContado->sum('subtotal');
        $totalPromocion = $ventasPromocion->sum('subtotal');

        // Info del preventista (si aplica)
        $usuario = $request->filled('usuario_id') ? User::find($request->usuario_id) : null;
        $almacenNombre = null;
        if ($request->filled('almacen_id')) {
            $almacen = Almacen::find($request->almacen_id);
            $almacenNombre = $almacen?->nombre ?? 'Sin nombre';
        }
        $pdf = Pdf::loadView('reportes.pdf.ventas', compact(
            'ventasCredito',
            'ventasContado',
            'ventasPromocion',
            'totalCredito',
            'totalContado',
            'totalPromocion',
            'usuario',
            'request',
            'almacenNombre'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('Reporte_Ventas.pdf');
    }
    public function ventasGeneralPdf(Request $request)
    {
        // Traer detalles con relaciones necesarias
        $detalles = DetallePreventa::with([
            'producto.almacen',
            'preventa.preventista',
            'preventa'
        ])->when($request->almacen_id, function ($query) use ($request) {
            $query->whereHas('producto', function ($q) use ($request) {
                $q->where('almacen_id', $request->almacen_id);
            });
        })->when($request->mes, function ($query) use ($request) {
            $query->whereHas('preventa', function ($q) use ($request) {
                $q->whereMonth('fecha_entrega', $request->mes);
            });
        })->get();

        $ventas = [];

        foreach ($detalles as $detalle) {
            $almacen = $detalle->producto->almacen->nombre ?? 'Sin almacén';
            $vendedor = $detalle->preventa->preventista->nombre ?? 'Sin nombre';

            if (!isset($ventas[$almacen])) {
                $ventas[$almacen] = [];
            }

            if (!isset($ventas[$almacen][$vendedor])) {
                $ventas[$almacen][$vendedor] = ['credito' => 0, 'contado' => 0];
            }

            if (str_contains($detalle->tipo_precio, 'credito')) {
                $ventas[$almacen][$vendedor]['credito'] += $detalle->subtotal;
            } elseif (str_contains($detalle->tipo_precio, 'contado')) {
                $ventas[$almacen][$vendedor]['contado'] += $detalle->subtotal;
            }
        }

        $almacenNombre = null;
        if ($request->filled('almacen_id')) {
            $almacenNombre = Almacen::find($request->almacen_id)?->nombre;
        }

        return Pdf::loadView('reportes.pdf.ventas_general', [
            'ventas' => $ventas,
            'request' => $request,
            'almacenNombre' => $almacenNombre
        ])->setPaper('A4', 'portrait')->stream('Reporte_General_Ventas.pdf');
    }
}
