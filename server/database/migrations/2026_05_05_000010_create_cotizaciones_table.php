<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->increments('id_cotizacion');
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('invernadero_id');
            $table->decimal('largo', 10, 2);
            $table->decimal('ancho', 10, 2);
            $table->decimal('metros_cuadrados', 12, 2);
            $table->decimal('valor_m2', 12, 2);
            $table->decimal('total', 15, 2);
            $table->dateTime('fecha')->useCurrent();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');

            $table->foreign('cliente_id')
                ->references('documento')
                ->on('clientes')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('invernadero_id')
                ->references('id_invernadero')
                ->on('invernaderos')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};