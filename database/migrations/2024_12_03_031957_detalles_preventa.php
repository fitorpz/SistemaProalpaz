<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetallesPreventaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalles_preventa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preventa_id');
            $table->unsignedBigInteger('ingreso_id');
            $table->enum('tipo_detalle', ['producto', 'bonificacion']);
            $table->enum('tipo_precio', ['unidad_contado', 'unidad_credito', 'caja_contado', 'caja_credito', 'cajon_contado', 'cajon_credito'])->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('preventa_id')->references('id')->on('preventas')->onDelete('cascade');
            $table->foreign('ingreso_id')->references('id')->on('ingresos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalles_preventa');
    }
}