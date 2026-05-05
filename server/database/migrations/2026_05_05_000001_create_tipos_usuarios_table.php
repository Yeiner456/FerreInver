<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_usuarios', function (Blueprint $table) {
            $table->increments('id_tipo_de_usuario');
            $table->string('nombre', 30);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
        });

        // Seed inicial requerido (referenciado por clientes)
        DB::table('tipos_usuarios')->insert([
            ['id_tipo_de_usuario' => 1, 'nombre' => 'admin',   'estado' => 'activo'],
            ['id_tipo_de_usuario' => 2, 'nombre' => 'cliente',  'estado' => 'activo'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_usuarios');
    }
};