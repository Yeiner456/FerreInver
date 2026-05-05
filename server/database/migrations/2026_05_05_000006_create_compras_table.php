<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('id_compra');
            $table->integer('cantidad');
            $table->string('descripcion', 150);
            $table->enum('estado_compra', ['entregado'])->default('entregado');
            $table->unsignedInteger('id_proveedor');
            $table->unsignedInteger('id_producto');

            $table->foreign('id_proveedor')
                ->references('nit_proveedor')
                ->on('proveedores')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};