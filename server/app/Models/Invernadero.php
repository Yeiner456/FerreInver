<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invernadero extends Model
{
    protected $table      = 'invernaderos';
    protected $primaryKey = 'id_invernadero';
    public $timestamps    = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_m2',
        'estado',
    ];

    // Un invernadero tiene muchas cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'invernadero_id', 'id_invernadero');
    }
}