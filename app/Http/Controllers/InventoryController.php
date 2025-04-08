<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    //Muestra la vista de gestion de inventario
    public function index()
    {
        return view('inventario.index');
    }
}
