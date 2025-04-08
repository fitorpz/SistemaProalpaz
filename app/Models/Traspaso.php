<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traspaso extends Model
{
    use HasFactory;

    // Definir la tabla si el nombre no sigue la convención
    protected $table = 'traspasos';

    // Definir los campos que pueden ser llenados masivamente
    protected $fillable = [
        'codigo_producto',
        'nombre_producto',
        'cantidad',
        'fecha_traspaso',
        'lote',
        'almacen_origen',
        'almacen_destino',
        'almacen_producto_terminado',
        'almacen_insumos',
        'almacen_cosmeticos',
    ];

    // Deshabilitar timestamps si no es necesario
    public $timestamps = false;
}
