<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCargoCliente extends Model
{
    use HasFactory;

    protected $table = 'detalle_cargo_clientes';
    protected $fillable = [
        'cargo_cliente_id',
        'ingreso_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    public function cargoCliente()
    {
        return $this->belongsTo(CargosCliente::class, 'cargo_cliente_id');
    }

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'ingreso_id');
    }
}
