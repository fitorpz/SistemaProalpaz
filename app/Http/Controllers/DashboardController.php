<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Opciones del menú según el rol del usuario
        $menuOptions = match ($user->role) {
            'administrador' => ['VENTAS', 'INVENTARIO', 'GESTIÓN USUARIOS', 'REPORTES'],
            'usuario_operador' => ['VENTAS', 'INVENTARIO'],
            'gestion_ventas' => ['VENTAS', 'REPORTES'],
            default => [],
        };

        return view('dashboard', compact('menuOptions', 'user'));
    }
}
