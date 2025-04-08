<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique()->index(); // Añadir índice explícito
            $table->string('password');
            $table->enum('rol', ['administrador', 'usuario_operador', 'gestion_ventas']);
            $table->json('almacenes_permitidos')->nullable(); // Puede ser null o default([]) según necesidades
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
