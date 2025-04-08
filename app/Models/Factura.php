<?php

// app/Models/Factura.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_proveedor',
        'nit_proveedor',
        'total',
        // Otros campos que sean necesarios
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'factura_producto')
                    ->withPivot('cantidad', 'precio_compra')
                    ->withTimestamps();
    }
}

