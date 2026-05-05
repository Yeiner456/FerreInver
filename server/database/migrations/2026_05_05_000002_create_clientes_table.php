<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedInteger('documento')->primary();
            $table->unsignedInteger('id_tipo_de_usuario')->default(2);
            $table->string('password_hash', 255);
            $table->string('nombre', 30);
            $table->dateTime('fecha_registro')->useCurrent();
            $table->enum('estado_inicio_sesion', ['activo', 'inactivo'])->default('activo');
            $table->string('correo', 50);
            $table->string('codigo_recuperacion', 6)->nullable();
            $table->dateTime('codigo_expiracion')->nullable();

            $table->foreign('id_tipo_de_usuario')
                ->references('id_tipo_de_usuario')
                ->on('tipos_usuarios')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};