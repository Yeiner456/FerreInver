<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table      = 'tipos_usuarios';
    protected $primaryKey = 'id_tipo_de_usuario';
    public $timestamps    = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];

    // Un tipo de usuario tiene muchos clientes
    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_tipo_de_usuario', 'id_tipo_de_usuario');
    }
}