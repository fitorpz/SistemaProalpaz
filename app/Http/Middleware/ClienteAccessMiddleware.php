<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClienteAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Solo usuarios con rol operador o administrador pueden ver todos los clientes
        if ($user->rol === 'gestion_ventas' && !$request->route('cliente')->usuario->is($user)) {
            abort(403, 'No tienes permiso para acceder a este cliente.');
        }

        return $next($request);
    }
}
