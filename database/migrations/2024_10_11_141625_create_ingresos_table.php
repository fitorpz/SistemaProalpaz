<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_producto');
            $table->string('nombre_producto');
            $table->integer('cantidad');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('lote');
            $table->decimal('costo_produccion_compra', 10, 2);
            $table->enum('tipo_ingreso', ['compra', 'produccion'])->default('compra');
            $table->boolean('compra_con_factura');
            $table->decimal('precio_unidad_credito', 10, 2);
            $table->decimal('precio_unidad_contado', 10, 2);
            $table->decimal('precio_caja_credito', 10, 2);
            $table->decimal('precio_caja_contado', 10, 2);
            $table->decimal('precio_cajon_credito', 10, 2);
            $table->decimal('precio_cajon_contado', 10, 2);
            $table->integer('stock_critico');
            $table->foreignId('almacen_id')->constrained('almacenes')->onDelete('cascade'); // Relación con almacenes
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->dropForeign(['almacen_id']);
            $table->dropColumn('almacen_id');
        });
    }
};
