<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbonosCliente extends Model
{
    use HasFactory;

    protected $table = 'abonos_clientes';
    protected $fillable = [
        'fecha_pago',
        'cliente_id',
        'nombre_cliente',
        'numero_credito',
        'cargo_cliente_id',
        'monto_abonado',
        'saldo_pendiente',
        'concepto',
        'metodo_pago',
        'referencia_pago',
        'recibo_pdf'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cargoCliente()
    {
        return $this->belongsTo(CargosCliente::class, 'cargo_cliente_id');
    }
}
