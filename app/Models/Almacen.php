<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    // Define el nombre de la tabla
    protected $table = 'almacenes';

    // Define los campos que se pueden asignar masivamente
    protected $fillable = ['nombre'];
}
