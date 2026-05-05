<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('
            CREATE TRIGGER crear_stock_al_insertar_producto
            AFTER INSERT ON productos
            FOR EACH ROW
            INSERT INTO stocks (cantidad, id_producto)
            VALUES (0, NEW.id_producto)
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS crear_stock_al_insertar_producto');
    }
};