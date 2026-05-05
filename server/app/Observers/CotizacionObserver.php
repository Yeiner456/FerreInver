<?php

namespace App\Observers;

use App\Models\Cotizacion;
use App\Models\Notificacion;

class CotizacionObserver
{
    public function created(Cotizacion $cotizacion): void
    {
        Notificacion::create([
            'documento_cliente' => $cotizacion->cliente_id,
            'titulo'            => 'Nueva cotización registrada',
            'mensaje'           => 'Tu cotización para el invernadero ha sido recibida y está en estado: ' . $cotizacion->estado . '.',
            'tipo'              => 'cotizacion',
        ]);
    }
}