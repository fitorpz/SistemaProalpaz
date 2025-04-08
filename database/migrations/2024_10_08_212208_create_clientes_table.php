<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id(); // ID único para el cliente
            $table->string('codigo_cliente', 50)->unique(); // Código único para el cliente
            $table->string('nombre_propietario', 100); // Nombre del propietario del comercio
            $table->string('nombre_comercio', 100); // Nombre del comercio
            $table->string('nit', 50)->nullable(); // NIT del cliente
            $table->string('direccion', 255)->nullable(); // Dirección del cliente
            $table->string('referencia', 255)->nullable(); // Referencias para la ubicación
            $table->text('ubicacion')->nullable(); // Ubicación (puede almacenar latitud/longitud u otros detalles)
            $table->string('horario_atencion', 255)->nullable(); // Horario de atención
            $table->string('telefono', 20)->nullable(); // Teléfono de contacto
            $table->date('cumpleanos_doctor')->nullable(); // Cumpleaños del doctor/a si aplica
            $table->string('horario_visita', 255)->nullable(); // Horario sugerido para la visita
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->string('dia_visita', 255)->nullable(); // Día sugerido para la visita
            $table->unsignedBigInteger('preventista_id'); // ID del preventista que registró al cliente
            $table->timestamps(); // Timestamps (creado y actualizado)

            // Relación con el preventista
            $table->foreign('preventista_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // Eliminar clientes si se elimina al preventista
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
