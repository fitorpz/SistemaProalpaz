<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    // Campos que se pueden llenar masivamente
    protected $fillable = ['nombre', 'descripcion', 'precio', 'stock'];
    
    // Si deseas desactivar los timestamps (created_at, updated_at), puedes hacerlo así:
    // public $timestamps = false;

    // Si tienes una relación con otra tabla, puedes definirla aquí, por ejemplo:
    // public function facturas()
    // {
    //     return $this->belongsToMany(Factura::class, 'factura_producto')
    //                 ->withPivot('cantidad', 'precio_unitario', 'precio_total')
    //                 ->withTimestamps();
    // }
}

