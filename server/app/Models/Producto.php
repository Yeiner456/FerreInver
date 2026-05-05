<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table      = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps    = false;

    protected $fillable = [
        'precio',
        'nombre',
        'descripcion',
        'estado_producto',
        'imagen',
    ];

    // Un producto tiene un stock
    public function stock()
    {
        return $this->hasOne(Stock::class, 'id_producto', 'id_producto');
    }

    // Un producto tiene muchas compras
    public function compras()
    {
        return $this->hasMany(Compra::class, 'id_producto', 'id_producto');
    }

    // Un producto pertenece a muchos pedidos (tabla pivote productos_pedidos)
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'productos_pedidos', 'id_producto', 'id_pedido')
                ->using(ProductoPedido::class)
                ->withPivot('descripcion', 'cantidad');
    }
}