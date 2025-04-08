<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargosCliente extends Model
{
    use HasFactory;

    protected $table = 'cargos_clientes';
    protected $fillable = [
        'numero_credito',
        'cliente_id',
        'preventa_id',
        'monto_total',
        'dias_credito',
        'fecha_vencimiento',
        'saldo_pendiente',
        'estado',
        'concepto'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function preventa()
    {
        return $this->belongsTo(Preventa::class, 'preventa_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCargoCliente::class, 'cargo_cliente_id');
    }

    public function abonos()
    {
        return $this->hasMany(AbonosCliente::class, 'cargo_cliente_id');
    }
}
