<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        // Aquí puedes pasar datos a la vista si es necesario
        $data = []; // Ejemplo: datos para el reporte

        return view('reportes.index', compact('data'));
    }
}
