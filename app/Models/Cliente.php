<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_cliente',
        'nombre_propietario',
        'nombre_comercio',
        'nit',
        'direccion',
        'referencia',
        'ubicacion',
        'horario_atencion',
        'telefono',
        'cumpleanos_doctor',
        'horario_visita',
        'observaciones',
        'dia_visita',
        'preventista_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'preventista_id'); // preventista_id es el usuario que lo registró
    }
    public function preventista()
    {
        return $this->belongsTo(User::class, 'preventista_id'); // preventista_id es el usuario que lo registró
    }
    public function preventas()
    {
        return $this->hasMany(Preventa::class, 'cliente_id');
    }
    public function visitas()
    {
        return $this->hasMany(RutaVisita::class, 'cliente_id');
    }
}
