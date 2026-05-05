<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table      = 'proveedores';
    protected $primaryKey = 'nit_proveedor';
    public $incrementing  = false;
    protected $keyType    = 'int';
    public $timestamps    = false;

    protected $fillable = [
        'nit_proveedor',
        'estado',
        'correo',
        'direccion',
        'telefono',
    ];

    // Un proveedor tiene muchas compras
    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_proveedor', 'nit_proveedor');
    }
}