<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'almacenes_permitidos',
        'tipos_ventas_permitidos',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'tipos_ventas_permitidos' => 'array',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'preventista_id'); // Relación inversa
    }


    // Encriptar la contraseña automáticamente
    //public function setPasswordAttribute($value)
    //{
    //    $this->attributes['password'] = bcrypt($value);
    //}
}
