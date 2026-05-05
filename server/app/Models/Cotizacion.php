<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    protected $table      = 'cotizaciones';
    protected $primaryKey = 'id_cotizacion';
    public $timestamps    = false;

    protected $fillable = [
        'cliente_id',
        'invernadero_id',
        'largo',
        'ancho',
        'metros_cuadrados',
        'valor_m2',
        'total',
        'fecha',
        'estado',
    ];

    // Una cotización pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'documento');
    }

    // Una cotización pertenece a un invernadero
    public function invernadero()
    {
        return $this->belongsTo(Invernadero::class, 'invernadero_id', 'id_invernadero');
    }
}