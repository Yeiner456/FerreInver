<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table      = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps    = false;

    protected $fillable = [
        'id_cliente',
        'fecha_hora',
        'medio_pago',
        'estado_pedido',
    ];

    // Un pedido pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'documento');
    }

    // Un pedido tiene muchos productos (tabla pivote productos_pedidos)
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'productos_pedidos', 'id_pedido', 'id_producto')
                ->using(ProductoPedido::class)
                ->withPivot('descripcion', 'cantidad');
    }
}