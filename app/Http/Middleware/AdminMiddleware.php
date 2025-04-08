<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        // Verificar si el usuario está autenticado y tiene el rol de 'administrador'
        if ($user->rol === 'administrador' || $user->rol === 'usuario_operador' || $user->rol === 'gestion_ventas') {
            return $next($request);
        }

        // Depurar para ver qué rol tiene el usuario
        //dd(Auth::user());

        // Redireccionar si el usuario no es administrador
        return redirect('/')->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
