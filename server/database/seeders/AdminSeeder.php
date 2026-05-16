<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Verifica que no exista ya para no duplicar
        $existe = DB::table('clientes')
            ->where('documento', '1000000000')
            ->exists();

        if (!$existe) {
            DB::table('clientes')->insert([
                'documento'            => '1000000000',
                'nombre'               => 'Administrador',
                'correo'               => 'admin@ferreinver.com',
                'password'             => Hash::make('Admin123Ferreinver'),
                'id_tipo_de_usuario'   => 1, 
                'estado_inicio_sesion' => 'activo',
                'fecha_registro'       => now(),
            ]);
        }
    }
}