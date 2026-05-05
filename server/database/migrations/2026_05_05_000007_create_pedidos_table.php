<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->increments('id_pedido');
            $table->unsignedInteger('id_cliente');
            $table->dateTime('fecha_hora')->useCurrent();
            $table->string('medio_pago', 30);
            $table->enum('estado_pedido', ['pendiente', 'recibido', 'listo para recibir', 'cancelado'])
                ->default('pendiente');

            $table->foreign('id_cliente')
                ->references('documento')
                ->on('clientes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};