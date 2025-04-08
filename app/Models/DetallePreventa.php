<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePreventa extends Model
{
    use HasFactory;

    protected $table = 'detalles_preventa';

    protected $fillable = [
        'preventa_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'tipo_precio',
        'subtotal',
        'bonificacion',
        'fecha_vencimiento',
    ];
    //Relación con la tabla Preventas.
    public function preventa()
    {
        return $this->belongsTo(Preventa::class, 'preventa_id');
    }
    //Relación con la tabla Ingresos (Productos principales).
    public function producto()
    {
        return $this->belongsTo(Ingreso::class, 'producto_id');
    }
    //Relación con la tabla Ingresos (Bonificación).
    public function bonificacionProducto()
    {
        return $this->belongsTo(Ingreso::class, 'bonificacion');
    }
    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class, 'ingreso_id', ); // Ajusta según tus claves
    }
}
