<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invernaderos', function (Blueprint $table) {
            $table->increments('id_invernadero');
            $table->string('nombre', 50);
            $table->string('descripcion', 150)->nullable();
            $table->decimal('precio_m2', 12, 2);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invernaderos');
    }
};