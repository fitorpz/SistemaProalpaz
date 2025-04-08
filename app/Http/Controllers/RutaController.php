<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\RutaVisita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $fechaSeleccionada = $request->input('fecha', now()->toDateString());
        $usuarioSeleccionado = $request->input('usuario_id');

        // Obtener usuarios si el usuario logueado es administrador
        $usuarios = \App\Models\User::whereIn('rol', ['usuario_operador', 'gestion_ventas'])->get();

        // Si el usuario es "gestion_ventas", solo puede ver sus rutas asignadas
        if ($user->rol === 'gestion_ventas') {
            $usuarioSeleccionado = $user->id;
        }

        // 🔹 Obtener el día de la semana en español
        $nombreDia = ucfirst(Carbon::parse($fechaSeleccionada)->locale('es')->isoFormat('dddd'));

        // 🔹 Obtener clientes que tienen una ruta asignada para ese día de visita
        $clientes = Cliente::whereRaw("FIND_IN_SET(?, REPLACE(dia_visita, ' ', ''))", [$nombreDia])
            ->when($usuarioSeleccionado, function ($query) use ($usuarioSeleccionado) {
                return $query->where('preventista_id', $usuarioSeleccionado); // 🔹 Ahora filtramos por el usuario asignado
            })
            ->with(['visitas' => function ($query) use ($fechaSeleccionada) {
                $query->whereDate('fecha_visita', $fechaSeleccionada);
            }])
            ->get();

        // Si no hay clientes en la consulta, se envía un mensaje de error
        if ($clientes->isEmpty()) {
            session()->flash('error', 'No hay rutas programadas para este día.');
        }

        return view('rutas.index', compact('clientes', 'fechaSeleccionada', 'usuarioSeleccionado', 'usuarios'));
    }


    public function registrarVisita(Request $request, $clienteId)
    {
        try {
            $request->validate([
                'ubicacion' => 'required|string',
                'observaciones' => 'nullable|string',
                'fecha' => 'required|date'
            ]);

            // Obtener la fecha seleccionada
            $fechaSeleccionada = $request->input('fecha');

            // Verificar si ya existe una visita en esa fecha
            $existeVisita = RutaVisita::where('cliente_id', $clienteId)
                ->whereDate('fecha_visita', $fechaSeleccionada)
                ->exists();

            if ($existeVisita) {
                return response()->json(['error' => 'Ya se registró una visita para esta fecha.'], 400);
            }

            // Crear la nueva visita
            $visita = RutaVisita::create([
                'cliente_id' => $clienteId,
                'preventista_id' => Auth::id(),
                'fecha_visita' => $fechaSeleccionada,
                'ubicacion' => $request->ubicacion,
                'observaciones' => $request->observaciones,
            ]);

            return response()->json([
                'success' => 'Visita registrada correctamente.',
                'ubicacion' => $visita->ubicacion,
                'observaciones' => $visita->observaciones,
                'hora' => $visita->created_at->format('H:i')
            ]);
        } catch (\Exception $e) {
            Log::error("Error al registrar visita: " . $e->getMessage());
            return response()->json(['error' => 'Error en el servidor: ' . $e->getMessage()], 500);
        }
    }
    public function generarPDFConFiltros(Request $request)
    {
        $user = Auth::user();
        $fechaSeleccionada = $request->input('fecha', now()->toDateString());
        $usuarioSeleccionado = $request->input('usuario_id');

        // Obtener usuarios si el usuario logueado es administrador
        $usuarios = \App\Models\User::whereIn('rol', ['usuario_operador', 'gestion_ventas'])->get();

        // Si el usuario es "gestion_ventas", solo puede ver sus rutas asignadas
        if ($user->rol === 'gestion_ventas') {
            $usuarioSeleccionado = $user->id;
        }

        // 🔹 Obtener el día de la semana en español
        $nombreDia = ucfirst(Carbon::parse($fechaSeleccionada)->locale('es')->isoFormat('dddd'));

        // 🔹 Obtener clientes que tienen una ruta asignada para ese día de visita
        $clientes = Cliente::whereRaw("FIND_IN_SET(?, REPLACE(dia_visita, ' ', ''))", [$nombreDia])
            ->when($usuarioSeleccionado, function ($query) use ($usuarioSeleccionado) {
                return $query->where('preventista_id', $usuarioSeleccionado);
            })
            ->with(['visitas' => function ($query) use ($fechaSeleccionada) {
                $query->whereDate('fecha_visita', $fechaSeleccionada);
            }])
            ->get();

        // Si no hay resultados, retornar un mensaje en la vista en lugar de un PDF vacío
        if ($clientes->isEmpty()) {
            return back()->with('error', 'No hay datos para generar el PDF con los filtros seleccionados.');
        }

        // Encabezado con los filtros aplicados
        $filtros = [
            'Fecha' => $fechaSeleccionada ?? 'Todos',
            'Usuario' => optional(User::find($usuarioSeleccionado))->nombre ?? 'Todos',
        ];

        // Generar el PDF con la misma estructura de la vista `rutas.index`
        $pdf = Pdf::loadView('rutas.pdf_reporte', compact('clientes', 'filtros', 'fechaSeleccionada'));

        // Descargar el PDF con un nombre dinámico
        return $pdf->stream('Reporte_Rutas_' . now()->format('Ymd_His') . '.pdf');
    }
}
