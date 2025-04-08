<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'almacen_id',
        'codigo_producto',
        'nombre_producto',
        'cantidad',
        'cantidad_inicial',
        'fecha_vencimiento',
        'lote',
        'costo_produccion_compra',
        'compra_con_factura',
        'tipo_ingreso',
        'precio_unidad_credito',
        'precio_unidad_contado',
        'precio_caja_credito',
        'precio_caja_contado',
        'precio_cajon_credito',
        'precio_cajon_contado',
        'precio_promocion',
        'stock_critico',
    ];
    protected $dates = [
        'fecha_vencimiento',
    ];

    /**
     * Relación con el modelo Almacen.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }
    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0)->orderBy('fecha_vencimiento', 'asc');
    }
    public function detallesPreventa()
    {
        return $this->hasMany(DetallePreventa::class, 'producto_id');
    }
}
