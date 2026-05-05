<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productos_pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_producto');
            $table->unsignedInteger('id_pedido');
            $table->string('descripcion', 100);
            $table->smallInteger('cantidad');

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('id_pedido')
                ->references('id_pedido')
                ->on('pedidos')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_pedidos');
    }
};