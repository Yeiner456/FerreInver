<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductoPedido extends Pivot
{
    protected $table      = 'productos_pedidos';
    protected $primaryKey = 'id';
    public $timestamps    = false;

    protected $fillable = [
        'id_producto',
        'id_pedido',
        'descripcion',
        'cantidad',
    ];

    // Pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    // Pertenece a un pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}