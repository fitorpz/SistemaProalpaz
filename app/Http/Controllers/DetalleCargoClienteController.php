<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleCargoCliente;
use App\Models\CargosCliente;
use App\Models\Ingreso;

class DetalleCargoClienteController extends Controller
{
    // Mostrar los detalles de un cargo específico
    public function index($cargo_cliente_id)
    {
        $detalles = DetalleCargoCliente::where('cargo_cliente_id', $cargo_cliente_id)->with('ingreso')->get();
        return view('cobranzas.detalles', compact('detalles'));
    }

    // Agregar un nuevo producto al crédito
    public function store(Request $request)
    {
        $request->validate([
            'cargo_cliente_id' => 'required|exists:cargos_clientes,id',
            'ingreso_id' => 'required|exists:ingresos,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0'
        ]);

        DetalleCargoCliente::create([
            'cargo_cliente_id' => $request->cargo_cliente_id,
            'ingreso_id' => $request->ingreso_id,
            'cantidad' => $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'subtotal' => $request->cantidad * $request->precio_unitario
        ]);

        return redirect()->back()->with('success', 'Producto agregado al crédito exitosamente.');
    }
}
