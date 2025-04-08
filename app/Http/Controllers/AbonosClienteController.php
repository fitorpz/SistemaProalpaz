<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbonosCliente;
use App\Models\CargosCliente;
use App\Models\Cliente;

class AbonosClienteController extends Controller
{
    // Mostrar todos los abonos de un cliente
    public function index($cliente_id)
    {
        $abonos = AbonosCliente::where('cliente_id', $cliente_id)->with('cargoCliente')->get();
        return view('cobranzas.abonos', compact('abonos'));
    }

    // Registrar un nuevo abono
    public function store(Request $request)
    {
        $request->validate([
            'cargo_cliente_id' => 'required|exists:cargos_clientes,id',
            'monto_abonado' => 'required|numeric|min:1',
            'metodo_pago' => 'required|in:Efectivo,Transferencia,Cheque,Tarjeta'
        ]);

        $cargo = CargosCliente::findOrFail($request->cargo_cliente_id);
        $nuevo_saldo = $cargo->saldo_pendiente - $request->monto_abonado;

        AbonosCliente::create([
            'fecha_pago' => now(),
            'cliente_id' => $cargo->cliente_id,
            'nombre_cliente' => $cargo->cliente->nombre_propietario,
            'numero_credito' => $cargo->numero_credito,
            'cargo_cliente_id' => $cargo->id,
            'monto_abonado' => $request->monto_abonado,
            'saldo_pendiente' => max($nuevo_saldo, 0),
            'concepto' => 'Abono a crédito #' . $cargo->numero_credito,
            'metodo_pago' => $request->metodo_pago,
            'referencia_pago' => $request->referencia_pago ?? null
        ]);

        // Actualizar estado del crédito
        $cargo->saldo_pendiente = $nuevo_saldo;
        if ($nuevo_saldo <= 0) {
            $cargo->estado = 'Pagado';
        } elseif ($nuevo_saldo < $cargo->monto_total) {
            $cargo->estado = 'Parcialmente Pagado';
        }
        $cargo->save();

        return redirect()->back()->with('success', 'Abono registrado exitosamente.');
    }
}
