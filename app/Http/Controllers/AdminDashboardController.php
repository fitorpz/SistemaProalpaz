<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    //Mostrar el dashboard del administrador
    public function index()
    {
        // Solo permitir acceso a usuarios autenticados en rol de administrador
        if (auth()->user()->rol !== 'administrador'){
            return redirect()->route('login')->with('error', 'Acceso denegado. Esta pagina es solo para adminsitradores. ');
        }

        return view('admin.dashboard');
    }
}
