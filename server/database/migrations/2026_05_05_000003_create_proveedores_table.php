¿<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->unsignedInteger('nit_proveedor')->primary();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->string('correo', 80);
            $table->string('direccion', 80);
            $table->string('telefono', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};