<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreventasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('preventas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('preventista_id');
            $table->decimal('precio_total', 10, 2);
            $table->date('fecha_entrega');
            $table->date('fecha_pago')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('preventista_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('preventas');
    }
}
