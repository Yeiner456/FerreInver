<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Observers\CotizacionObserver;
use App\Observers\PedidoObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Cotizacion::observe(CotizacionObserver::class);
        Pedido::observe(PedidoObserver::class);
    }
}