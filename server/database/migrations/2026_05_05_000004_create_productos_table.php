<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->bigInteger('precio');
            $table->string('nombre', 30);
            $table->string('descripcion', 100)->nullable();
            $table->enum('estado_producto', ['activo', 'inactivo'])->default('activo');
            $table->string('imagen', 255)->nullable();
        });
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS crear_stock_al_insertar_producto');
        Schema::dropIfExists('productos');
    }
};
