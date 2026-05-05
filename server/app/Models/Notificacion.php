<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table      = 'notificaciones';
    protected $primaryKey = 'id_notificacion';
    public $timestamps    = false;

    protected $fillable = [
        'documento_cliente',
        'titulo',
        'mensaje',
        'tipo',
        'fecha',
        'leido',
    ];

    // Una notificación pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'documento_cliente', 'documento');
    }
}