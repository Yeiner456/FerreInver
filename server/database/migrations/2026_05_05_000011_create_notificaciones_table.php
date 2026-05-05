<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->increments('id_notificacion');
            $table->unsignedInteger('documento_cliente');
            $table->string('titulo', 100);
            $table->text('mensaje');
            $table->string('tipo', 50);
            $table->dateTime('fecha')->useCurrent();
            $table->boolean('leido')->default(false);

            $table->foreign('documento_cliente')
                ->references('documento')
                ->on('clientes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};