<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContabilidadController extends Controller
{
    public function index()
    {
        return view('contabilidad.index');
    }
}
