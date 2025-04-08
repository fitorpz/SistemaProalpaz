<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Procesa el inicio de sesión del usuario.
     */
    public function login(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar el usuario por correo electrónico
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::error('Usuario no encontrado.');
            return redirect()->route('login')->with('error', 'Credenciales incorrectas.');
        }

        // Verificar la contraseña
        if (!Hash::check($request->password, $user->password)) {
            Log::error('Contraseña incorrecta.');
            return redirect()->route('login')->with('error', 'Credenciales incorrectas.');
        }

        // Autenticar manualmente al usuario
        Auth::login($user);

        Log::info('Usuario autenticado: ' . Auth::user()->email . ', Rol: ' . Auth::user()->rol);

        // Redirigir al dashboard
        return redirect()->route('dashboard');
    }
    public function showLoginForm()
    {
        return view('auth.login'); // Asegúrate de que la vista esté en el directorio correcto
    }


    /**
     * Cierra la sesión del usuario.
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'rol' => $user->rol,
            ]
        ]);
    }
}
