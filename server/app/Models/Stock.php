<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table      = 'stocks';
    protected $primaryKey = 'id_stock';
    public $timestamps    = false;

    protected $fillable = [
        'cantidad',
        'id_producto',
    ];

    // Un stock pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}