<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Preventa;
use App\Models\Ingreso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PickingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $fechaSeleccionada = $request->input('fecha', now()->format('Y-m-d'));

        $query = Preventa::with(['detalles.producto', 'cliente'])
            ->whereDate('fecha_entrega', $fechaSeleccionada);

        if ($user->rol === 'usuario_operador') {
            $almacenesPermitidos = json_decode($user->almacenes_permitidos, true) ?? [];
            $query->whereHas('detalles.producto', function ($q) use ($almacenesPermitidos) {
                $q->whereIn('almacen_id', $almacenesPermitidos);
            });
        }

        $preventas = $query->get();

        return view('picking.index', compact('preventas', 'fechaSeleccionada'));
    }

    public function preparar($id)
    {
        $preventa = Preventa::findOrFail($id);
        $preventa->estado = 'Preparado';
        $preventa->save();

        return redirect()->route('picking.index')->with('success', 'Preventa marcada como preparada.');
    }

    public function entregado(Request $request, $id)
    {
        // Buscar la preventa con los detalles y productos relacionados
        $preventa = Preventa::with('detalles.producto')->findOrFail($id);

        // ✅ Asignar automáticamente "Entregado con éxito"
        $observacionEntrega = 'Entregado con éxito';

        // 🔍 Registrar en logs la petición para depuración
        Log::info("📌 Pedido {$preventa->id} marcado como ENTREGADO.", [
            'estado' => 'Entregado',
            'observacion_entrega' => $observacionEntrega
        ]);

        // 🔥 Actualizar estado y observación en la preventa
        $preventa->update([
            'estado' => 'Entregado',
            'observacion_entrega' => $observacionEntrega
        ]);

        // 🔥 Descontar el stock en la tabla de ingresos según la fecha de vencimiento de los productos
        foreach ($preventa->detalles as $detalle) {
            $producto = $detalle->producto;
            if (!$producto) continue;

            $cantidadRestante = $detalle->cantidad;
            $fechaVencimiento = date('Y-m-d', strtotime($detalle->fecha_vencimiento));

            // 🔍 Buscar los ingresos disponibles para el producto ordenados por fecha de vencimiento (FIFO)
            $ingresos = Ingreso::where('codigo_producto', $producto->codigo_producto)
                ->whereDate('fecha_vencimiento', $fechaVencimiento)
                ->where('cantidad', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();

            foreach ($ingresos as $ingreso) {
                if ($cantidadRestante <= 0) break;

                if ($ingreso->cantidad >= $cantidadRestante) {
                    $ingreso->cantidad -= $cantidadRestante;
                    $cantidadRestante = 0;
                } else {
                    $cantidadRestante -= $ingreso->cantidad;
                    $ingreso->cantidad = 0;
                }

                // Guardar los cambios en el stock
                $ingreso->save();
            }
        }

        // ✅ Confirmación en logs para verificar el proceso completo
        Log::info("🚀 Pedido {$preventa->numero_pedido} actualizado correctamente con estado: ENTREGADO y observación: {$observacionEntrega}");

        return redirect()->route('picking.index')->with('success', 'Pedido marcado como Entregado y stock actualizado.');
    }



    public function noEntregado(Request $request, $id)
    {
        $preventa = Preventa::findOrFail($id);
        $preventa->estado = 'No Entregado';
        $preventa->observacion_entrega = $request->input('observacion_entrega', 'Motivo no especificado');
        $preventa->save();

        return redirect()->route('picking.index')->with('warning', 'Pedido marcado como No Entregado.');
    }


    public function exportarPDF($fecha)
    {
        $preventas = Preventa::whereDate('fecha_entrega', $fecha)
            ->with(['detalles.producto', 'cliente'])
            ->get();
        $pdf = Pdf::loadView('picking.reporte_pdf', compact('preventas', 'fecha'));

        return $pdf->download("Pedidos_Picking_$fecha.pdf");
    }
}
