<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVenta extends Model
{
    use HasFactory;

    protected $table = 'tipos_ventas'; // Nombre de la tabla en la BD

    protected $fillable = ['tipo_venta']; // Campos que pueden asignarse en masa
}
