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
    public function ventas(Request $request)
    {
        $usuarios = User::where('rol', 'like', '%ventas%')->get();

        // Traer todos los detalles_preventa con relaciones
        $detalles = DetallePreventa::with(['preventa.cliente', 'preventa.preventista', 'preventa.cargo', 'producto'])
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
            })
            ->get();

        // Filtrar por modalidad de precio
        $ventasCredito = $detalles->filter(fn($d) => str_contains($d->tipo_precio, 'credito'));
        $ventasContado = $detalles->filter(fn($d) => str_contains($d->tipo_precio, 'contado'));
        $ventasPromocion = $detalles->filter(fn($d) => $d->tipo_precio === 'precio_promocion');

        return view('reportes.ventas.index', [
            'usuarios' => $usuarios,
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

        $pdf = Pdf::loadView('reportes.pdf.ventas', compact(
            'ventasCredito',
            'ventasContado',
            'ventasPromocion',
            'totalCredito',
            'totalContado',
            'totalPromocion',
            'usuario',
            'request'
        ))->setPaper('A4', 'portrait');

        return $pdf->stream('Reporte_Ventas.pdf');
    }
}
