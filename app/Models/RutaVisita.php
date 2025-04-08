<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RutaVisita extends Model
{
    use HasFactory;

    protected $table = 'ruta_visitas';

    protected $fillable = [
        'cliente_id',
        'preventista_id',
        'fecha_visita',
        'ubicacion',
        'observaciones',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function preventista()
    {
        return $this->belongsTo(User::class, 'preventista_id');
    }
}
