<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table      = 'clientes';
    protected $primaryKey = 'documento';
    public $incrementing  = false;
    protected $keyType    = 'int';
    public $timestamps    = false;

    protected $fillable = [
        'documento',
        'id_tipo_de_usuario',
        'password_hash',
        'nombre',
        'fecha_registro',
        'estado_inicio_sesion',
        'correo',
        'codigo_recuperacion',
        'codigo_expiracion',
    ];

    protected $hidden = [
        'password_hash',
        'codigo_recuperacion',
    ];

    // Pertenece a un tipo de usuario
    public function tipoUsuario()
    {
        return $this->belongsTo(TipoUsuario::class, 'id_tipo_de_usuario', 'id_tipo_de_usuario');
    }

    // Un cliente tiene muchos pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'documento');
    }

    // Un cliente tiene muchas cotizaciones
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'cliente_id', 'documento');
    }

    // Un cliente tiene muchas notificaciones
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'documento_cliente', 'documento');
    }
}