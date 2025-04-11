<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $usuarios = \App\Models\User::select('id', 'nombre', 'rol')->get();

        $query = Cliente::query();

        // Si el usuario es "gestion_ventas", solo ve sus clientes
        if ($user->rol === 'gestion_ventas') {
            $query->where('preventista_id', $user->id);
        }

        // 🔹 Filtrar por usuario seleccionado
        if ($request->filled('usuario')) {
            $query->where('preventista_id', $request->usuario);
        }

        // 🔹 Filtrar por día de visita (coincidencias parciales en el texto)
        if ($request->filled('dia_visita')) {
            $query->where('dia_visita', 'LIKE', '%' . $request->dia_visita . '%');
        }

        // 🔹 Filtrar por nombre de comercio
        if ($request->filled('nombre_comercio')) {
            $query->where('nombre_comercio', 'LIKE', '%' . $request->nombre_comercio . '%');
        }

        $clientes = $query->get();

        return view('clientes.index', compact('clientes', 'usuarios'));
    }
    public function create()
    {
        return view('clientes.crear');
    }


    public function generarPDFConFiltros(Request $request)
    {
        $user = auth()->user(); // Obtener usuario autenticado

        $query = Cliente::query();

        // 🔹 Si el usuario es "gestion_ventas", solo ve sus clientes asignados
        if ($user->rol === 'gestion_ventas') {
            $query->where('preventista_id', $user->id);
        }

        // 🔹 Filtrar por usuario (solo si es administrador o usuario_operador)
        if ($request->filled('usuario') && in_array($user->rol, ['administrador', 'usuario_operador'])) {
            $query->where('preventista_id', $request->usuario);
        }

        // 🔹 Filtrar por día de visita
        if ($request->filled('dia_visita')) {
            $query->where('dia_visita', 'LIKE', '%' . $request->dia_visita . '%');
        }

        // 🔹 Filtrar por nombre de comercio
        if ($request->filled('nombre_comercio')) {
            $query->where('nombre_comercio', 'LIKE', '%' . $request->nombre_comercio . '%');
        }

        $clientes = $query->get();

        // 🔹 Capturar filtros aplicados para mostrar en el PDF
        $filtros = [
            'Usuario' => optional(User::find($request->usuario))->nombre ?? 'Todos',
            'Día de Visita' => $request->dia_visita ?? 'Todos',
            'Nombre Comercio' => $request->nombre_comercio ?? 'Todos',
        ];

        // 🔹 Generar PDF usando la vista
        $pdf = PDF::loadView('clientes.pdf_reporte', compact('clientes', 'filtros'));

        // 🔹 Descargar el PDF con nombre dinámico
        return $pdf->stream('Reporte_Clientes_' . now()->format('Ymd_His') . '.pdf');
    }



    public function indexApi(Request $request)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Validar que el usuario esté autenticado
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado.'
            ], 401);
        }

        // Inicializar la variable clientes
        $clientes = [];

        // Lógica basada en roles
        if ($user->rol === 'administrador' || $user->rol === 'usuario_operador') {
            $clientes = Cliente::all();
        } elseif ($user->rol === 'gestion_ventas') {
            $clientes = Cliente::where('preventista_id', $user->id)->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para acceder a esta sección.'
            ], 403);
        }

        // Retornar los datos
        return response()->json([
            'success' => true,
            'data' => $clientes
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_propietario' => 'required|string|max:100',
            'nombre_comercio' => 'required|string|max:100',
            'nit' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'ubicacion' => 'required|url',
            'horario_atencion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'cumpleanos_doctor' => 'nullable|date',
            'horario_visita' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'dia_visita' => 'required|array|min:1|max:2', // Validar que sea un array con 1 o 2 elementos
            'dia_visita.*' => 'string|in:Lunes,Martes,Miércoles,Jueves,Viernes', // Validar días válidos
        ]);

        $ultimoCliente = Cliente::latest('id')->first();
        $nuevoCodigo = $ultimoCliente ? intval($ultimoCliente->codigo_cliente) + 1 : 1;
        $codigoFormateado = str_pad($nuevoCodigo, 5, '0', STR_PAD_LEFT);

        $cliente = Cliente::create([
            'codigo_cliente' => $codigoFormateado,
            'nombre_propietario' => $request->nombre_propietario,
            'nombre_comercio' => $request->nombre_comercio,
            'nit' => $request->nit,
            'direccion' => $request->direccion,
            'referencia' => $request->referencia,
            'ubicacion' => $request->ubicacion,
            'horario_atencion' => $request->horario_atencion,
            'telefono' => $request->telefono,
            'cumpleanos_doctor' => $request->cumpleanos_doctor,
            'horario_visita' => $request->horario_visita,
            'observaciones' => $request->observaciones,
            'dia_visita' => implode(',', $request->dia_visita), // Guardamos los días como "Lunes,Miércoles"
            'preventista_id' => Auth::id(),
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado con éxito.');
    }


    public function apiStore(Request $request)
    {
        try {
            // Validación de datos
            $request->validate([
                'nombre_propietario' => 'required|string|max:100',
                'nombre_comercio' => 'required|string|max:100',
                'nit' => 'nullable|string|max:50',
                'direccion' => 'nullable|string|max:255',
                'referencia' => 'nullable|string|max:255',
                'ubicacion' => 'required|url',
                'horario_atencion' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'cumpleanos_doctor' => 'nullable|date',
                'horario_visita' => 'nullable|string|max:255',
                'observaciones' => 'nullable|string',
                'dia_visita' => 'nullable|string|max:255',
            ]);

            // Generar el código del cliente automáticamente
            $ultimoCliente = Cliente::latest('id')->first();
            $nuevoCodigo = $ultimoCliente ? intval($ultimoCliente->codigo_cliente) + 1 : 1;
            $codigoFormateado = str_pad($nuevoCodigo, 5, '0', STR_PAD_LEFT);

            // Crear el cliente
            $cliente = Cliente::create($request->all() + [
                'codigo_cliente' => $codigoFormateado,
                'preventista_id' => auth()->id(), // Usuario autenticado
            ]);

            // Crear la ruta para la visita
            Ruta::create([
                'cliente_id' => $cliente->id,
                'preventista_id' => auth()->id(),
                'fecha_visita' => now()->addWeek()->toDateString(),
            ]);

            // Retornar respuesta JSON
            return response()->json([
                'success' => true,
                'message' => 'Cliente registrado con éxito',
                'cliente' => $cliente
            ], 201);
        } catch (\Exception $e) {
            // Manejar errores internos del servidor
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'codigo_cliente' => 'required|string|max:50',
            'nombre_propietario' => 'required|string|max:100',
            'nombre_comercio' => 'required|string|max:100',
            'nit' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string',
            'horario_atencion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'cumpleanos_doctor' => 'nullable|date',
            'horario_visita' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'dia_visita' => 'nullable|array|max:2',
            'dia_visita.*' => 'string|in:Lunes,Martes,Miércoles,Jueves,Viernes',
        ]);

        $cliente = Cliente::findOrFail($id);
        $diasVisita = $request->has('dia_visita') ? implode(',', array_map('trim', $request->dia_visita)) : null;

        $cliente->update([
            'codigo_cliente' => $request->codigo_cliente,
            'nombre_propietario' => $request->nombre_propietario,
            'nombre_comercio' => $request->nombre_comercio,
            'nit' => $request->nit,
            'direccion' => $request->direccion,
            'referencia' => $request->referencia,
            'ubicacion' => $request->ubicacion,
            'horario_atencion' => $request->horario_atencion,
            'telefono' => $request->telefono,
            'cumpleanos_doctor' => $request->cumpleanos_doctor,
            'horario_visita' => $request->horario_visita,
            'observaciones' => $request->observaciones,
            'dia_visita' => $diasVisita,
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado con éxito.');
    }


    public function destroy($id)
    {
        // Verificar si el usuario es administrador
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
    public function buscarClientes(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q');

        // Si el usuario es administrador o usuario_operador, obtener todos los clientes
        if (in_array($user->rol, ['administrador', 'usuario_operador'])) {
            $clientes = Cliente::where(function ($q) use ($query) {
                $q->where('nombre_propietario', 'like', "%{$query}%")
                    ->orWhere('nombre_comercio', 'like', "%{$query}%");
            })
                ->limit(10)
                ->get();
        } else {
            // Si es preventista, obtener solo sus clientes asignados
            $clientes = Cliente::where('preventista_id', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('nombre_propietario', 'like', "%{$query}%")
                        ->orWhere('nombre_comercio', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();
        }

        // Registrar los datos obtenidos en los logs de Laravel
        Log::info("📌 Clientes encontrados para el usuario {$user->rol}:", $clientes->toArray());

        return response()->json($clientes);
    }

    public function buscarClientesPorNombre(Request $request)
    {
        $user = Auth::user();
        $query = $request->input('q');

        // Crear la consulta base
        $clientesQuery = Cliente::query();

        if (!empty($query)) {
            $clientesQuery->where(function ($q) use ($query) {
                $q->where('nombre_propietario', 'like', "%{$query}%")
                    ->orWhere('nombre_comercio', 'like', "%{$query}%");
            });
        }

        // Filtrar según el rol del usuario
        if (!in_array($user->rol, ['administrador', 'usuario_operador'])) {
            $clientesQuery->where('preventista_id', $user->id);
        }

        $clientes = $clientesQuery->limit(10)->get();

        return response()->json($clientes);
    }
}
