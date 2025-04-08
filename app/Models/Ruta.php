<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas'; // Aseguramos el nombre correcto de la tabla

    protected $fillable = [
        'cliente_id',
        'preventista_id',
        'created_at',
        'updated_at',
    ];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Relación con Usuario (Preventista)
    public function preventista()
    {
        return $this->belongsTo(User::class, 'preventista_id');
    }

    // Relación con visitas
    public function visitas()
    {
        return $this->hasMany(RutaVisita::class, 'ruta_id');
    }
}
