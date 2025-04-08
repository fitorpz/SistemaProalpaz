<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Almacen;
use App\Models\TipoVenta;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();
        $almacenes = Almacen::all();
        $tiposVentas = TipoVenta::all();

        foreach ($users as $user) {
            // ✅ Asegurar que `almacenes_permitidos` sea un array válido
            $permitidos = json_decode($user->almacenes_permitidos ?? '[]', true);
            if (empty($permitidos)) {
                $user->nombres_almacenes = 'Sin asignar';
            } else {
                $nombresAlmacenes = collect($permitidos)->map(function ($id) use ($almacenes) {
                    return $almacenes->firstWhere('id', $id)->nombre ?? 'Desconocido';
                })->toArray();
                $user->nombres_almacenes = implode(', ', $nombresAlmacenes);
            }

            // ✅ Verificar si `tipos_ventas_permitidos` ya es un array, si no, convertirlo correctamente
            if (is_null($user->tipos_ventas_permitidos)) {
                $user->tipos_ventas_permitidos = [];
            } elseif (is_string($user->tipos_ventas_permitidos)) {
                $user->tipos_ventas_permitidos = json_decode($user->tipos_ventas_permitidos, true) ?? [];
            } elseif (!is_array($user->tipos_ventas_permitidos)) {
                $user->tipos_ventas_permitidos = [];
            }
        }

        return view('users.index', compact('users', 'almacenes', 'tiposVentas'));
    }



    public function indexApi()
    {
        $users = User::select('id', 'nombre', 'email', 'rol', 'almacen_id', 'almacenes_permitidos')->get();
        $almacenes = Almacen::all();

        foreach ($users as $user) {
            $permitidos = json_decode($user->almacenes_permitidos, true) ?? [];

            if (empty($permitidos)) {
                $user->nombres_almacenes = 'Sin asignar';
            } else {
                $nombresAlmacenes = collect($permitidos)->map(function ($id) use ($almacenes) {
                    return $almacenes->firstWhere('id', $id)->nombre ?? 'Desconocido';
                })->toArray();

                $user->nombres_almacenes = implode(', ', $nombresAlmacenes);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }


    //Muestra el formulario para crear un nuevo usuario
    public function create()
    {
        return view('users.create');
    }
    // Guardar  el nuevo usuario en la base de datos
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:administrador,usuario_operador,gestion_ventas',
            'almacenes_permitidos' => 'nullable|array',
            'almacenes_permitidos.*' => 'exists:almacenes,id',
            'tipos_ventas_permitidos' => 'nullable|array', // Nueva validación
            'tipos_ventas_permitidos.*' => 'exists:tipos_ventas,id', // Verifica que existan en la BD
        ]);

        $almacenesPermitidos = $request->almacenes_permitidos ?? [];
        $jsonAlmacenes = json_encode($almacenesPermitidos);

        $tiposVentasPermitidos = $request->tipos_ventas_permitidos ?? [];
        $jsonTiposVentas = json_encode($tiposVentasPermitidos);

        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'rol' => $request->rol,
            'almacenes_permitidos' => $jsonAlmacenes, // No se toca
            'tipos_ventas_permitidos' => $jsonTiposVentas, // Se agrega sin modificar lo demás
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }


    // Actualizar un usuario existente
    public function update(Request $request, $id)
    {
        // Validaciones
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'rol' => 'required|string|in:administrador,usuario_operador,gestion_ventas',
            'password' => 'nullable|string|min:6|confirmed', // Contraseña opcional pero con confirmación
            'almacenes_permitidos' => 'nullable|array',
            'tipos_ventas_permitidos' => 'nullable|array', // Nueva validación
            'tipos_ventas_permitidos.*' => 'exists:tipos_ventas,id', // Verifica que existan en la BD
        ]);

        // Buscar usuario
        $user = User::findOrFail($id);

        // Preparar datos para la actualización
        $data = [
            'nombre' => $request->nombre,
            'email' => $request->email,
            'rol' => $request->rol,
            'almacenes_permitidos' => json_encode($request->almacenes_permitidos ?? []), // No se toca
            'tipos_ventas_permitidos' => json_encode($request->tipos_ventas_permitidos ?? []), // Se agrega sin modificar lo demás
        ];

        // Solo actualizar la contraseña si el campo no está vacío
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Actualizar usuario
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }



    // Eliminar un usuario
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
