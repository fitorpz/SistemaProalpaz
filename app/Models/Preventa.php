<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Preventa extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_pedido',
        'cliente_id',
        'precio_total',
        'descuento',
        'observaciones',
        'fecha_entrega',
        'preventista_id',
        'estado',
        'observacion_entrega',
        'tipo_venta',
    ];

    // Agrega el casting para fecha_entrega
    protected $casts = [
        'fecha_entrega' => 'date', // Convierte automáticamente en un objeto DateTime
    ];

    public static function boot()
    {
        parent::boot();

        // Generar número de pedido único antes de crear la preventa
        static::creating(function ($preventa) {
            // Obtener el nombre del usuario autenticado
            $nombrePreventista = Auth::user()->nombre; // Asume que el campo 'nombre' está en la tabla de usuarios
            $preventa->numero_pedido = self::generateNumeroPedido($nombrePreventista);

            if (!$preventa->estado) {
                $preventa->estado = 'Pendiente';
            }
        });
    }


    public static function generateNumeroPedido($nombrePreventista)
    {
        // Obtener las iniciales del preventista
        $iniciales = self::getIniciales($nombrePreventista);

        do {
            // Generar número de pedido
            $count = self::max('id') + 1; // Obtiene el último ID + 1
            $numeroPedido = $iniciales . '-PED-' . str_pad($count, 6, '0', STR_PAD_LEFT);
        } while (self::where('numero_pedido', $numeroPedido)->exists());

        return $numeroPedido;
    }

    // Método para obtener las iniciales del nombre del preventista
    protected static function getIniciales($nombre)
    {
        $palabras = explode(' ', $nombre);
        $iniciales = '';

        foreach ($palabras as $palabra) {
            $iniciales .= strtoupper($palabra[0]); // Toma la primera letra de cada palabra
        }

        return $iniciales;
    }



    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePreventa::class, 'preventa_id');
    }
    // Preventa.php
    public function preventista()
    {
        return $this->belongsTo(User::class, 'preventista_id'); // Asume que preventista_id guarda la relación con el usuario
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}
