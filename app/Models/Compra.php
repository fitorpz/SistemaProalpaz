<?php

// app/Models/Compra.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    // Especificar el nombre de la tabla
    protected $table = 'compras';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'codigo_producto',
        'nombre_producto',
        'cantidad',
        'fecha_vencimiento',
        'lote',
        'costo_compra',
        'precio_unidad_credito',
        'precio_unidad_contado',
        'stock_critico',
        'almacen_producto_terminado',
        'almacen_materia_prima',
        'almacen_cosmeticos',
        'con_factura',
    ];

    // Si la tabla tiene campos de tipo timestamp (created_at, updated_at)
    public $timestamps = true;
}
