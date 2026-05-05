<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table      = 'compras';
    protected $primaryKey = 'id_compra';
    public $timestamps    = false;

    protected $fillable = [
        'cantidad',
        'descripcion',
        'estado_compra',
        'id_proveedor',
        'id_producto',
    ];

    // Una compra pertenece a un proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'nit_proveedor');
    }

    // Una compra pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}