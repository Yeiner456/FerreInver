<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Models\Notificacion;

class PedidoObserver
{
    public function created(Pedido $pedido): void
    {
        Notificacion::create([
            'documento_cliente' => $pedido->id_cliente,
            'titulo'            => 'Pedido recibido',
            'mensaje'           => 'Tu pedido fue registrado con el medio de pago: ' . $pedido->medio_pago . '. Estado actual: ' . $pedido->estado_pedido . '.',
            'tipo'              => 'pedido',
        ]);
    }
}