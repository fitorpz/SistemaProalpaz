<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baja extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_producto',
        'nombre_producto',
        'cantidad',
        'motivo',
        'fecha_registro',
        'almacen',        
    ];

    //Habilitar timertamps (created_at y updated_at)
    public $timestamps = true;

    // Configurar fecha_registro como una fecha en el modelo
    protected $dates = ['fecha_registro'];
}
