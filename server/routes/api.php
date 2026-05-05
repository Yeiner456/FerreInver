<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\TiposUsuariosController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\InvernaderosController;
use App\Http\Controllers\CotizacionesController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\ProductosPedidosController;
use App\Http\Controllers\NotificacionController;

// ─────────────────────────────────────────────────────────────────
//  AUTH
// ─────────────────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// ─────────────────────────────────────────────────────────────────
//  TIPOS DE USUARIO
// ─────────────────────────────────────────────────────────────────
Route::get   ('/tipos-usuarios',      [TiposUsuariosController::class, 'index']);
Route::post  ('/tipos-usuarios',      [TiposUsuariosController::class, 'create']);
Route::put   ('/tipos-usuarios/{id}', [TiposUsuariosController::class, 'update']);
Route::delete('/tipos-usuarios/{id}', [TiposUsuariosController::class, 'delete']);
Route::post('/auth/register',         [AuthController::class, 'register']);
Route::post('/auth/enviar-codigo',    [AuthController::class, 'enviarCodigo']);
Route::post('/auth/verificar-codigo', [AuthController::class, 'verificarCodigo']);
Route::post('/auth/cambiar-password', [AuthController::class, 'cambiarPassword']);

// ─────────────────────────────────────────────────────────────────
//  CLIENTES
// ─────────────────────────────────────────────────────────────────
Route::get   ('/clientes/tipos',                     [ClientesController::class, 'tipos']);
Route::get   ('/clientes',                           [ClientesController::class, 'index']);
Route::post  ('/clientes',                           [ClientesController::class, 'create']);
Route::put   ('/clientes/{documento}',               [ClientesController::class, 'update']);
Route::patch ('/clientes/{documento}/nombre',        [ClientesController::class, 'updateNombre']);
Route::delete('/clientes/{documento}',               [ClientesController::class, 'deactivate']);

// ─────────────────────────────────────────────────────────────────
//  PRODUCTOS
// ─────────────────────────────────────────────────────────────────
Route::get   ('/productos',      [ProductosController::class, 'index']);
Route::post  ('/productos',      [ProductosController::class, 'create']);
Route::post  ('/productos/{id}', [ProductosController::class, 'update']);     // FormData usa POST + ?_method=PUT
Route::delete('/productos/{id}', [ProductosController::class, 'deactivate']);

// ─────────────────────────────────────────────────────────────────
//  STOCKS
// ─────────────────────────────────────────────────────────────────
Route::get   ('/stocks',      [StocksController::class, 'index']);   // ?selects=1 disponible
Route::post  ('/stocks',      [StocksController::class, 'create']);
Route::put   ('/stocks/{id}', [StocksController::class, 'update']);
Route::delete('/stocks/{id}', [StocksController::class, 'delete']);

// ─────────────────────────────────────────────────────────────────
//  PROVEEDORES
// ─────────────────────────────────────────────────────────────────
Route::get   ('/proveedores',       [ProveedoresController::class, 'index']);
Route::post  ('/proveedores',       [ProveedoresController::class, 'create']);
Route::put   ('/proveedores/{nit}', [ProveedoresController::class, 'update']);
Route::delete('/proveedores/{nit}', [ProveedoresController::class, 'deactivate']);

// ─────────────────────────────────────────────────────────────────
//  COMPRAS
// ─────────────────────────────────────────────────────────────────
Route::get   ('/compras',        [ComprasController::class, 'index']);
Route::get   ('/compras/selects',[ComprasController::class, 'selects']);
Route::post  ('/compras',        [ComprasController::class, 'create']);
Route::put   ('/compras/{id}',   [ComprasController::class, 'update']);
Route::delete('/compras/{id}',   [ComprasController::class, 'delete']);

// ─────────────────────────────────────────────────────────────────
//  INVERNADEROS
// ─────────────────────────────────────────────────────────────────
Route::get   ('/invernaderos',      [InvernaderosController::class, 'index']);
Route::post  ('/invernaderos',      [InvernaderosController::class, 'create']);
Route::put   ('/invernaderos/{id}', [InvernaderosController::class, 'update']);
Route::delete('/invernaderos/{id}', [InvernaderosController::class, 'deactivate']);

// ─────────────────────────────────────────────────────────────────
//  COTIZACIONES
// ─────────────────────────────────────────────────────────────────
Route::get   ('/cotizaciones',      [CotizacionesController::class, 'index']);   // ?selects=1 | ?documento=X
Route::post  ('/cotizaciones',      [CotizacionesController::class, 'store']);
Route::put   ('/cotizaciones/{id}', [CotizacionesController::class, 'update']);
Route::delete('/cotizaciones/{id}', [CotizacionesController::class, 'destroy']);

// ─────────────────────────────────────────────────────────────────
//  PEDIDOS
// ─────────────────────────────────────────────────────────────────
Route::get   ('/pedidos',           [PedidosController::class, 'index']);    // ?selects=1 | ?documento=X
Route::post  ('/pedidos',           [PedidosController::class, 'create']);
Route::post  ('/pedidos/completo',  [PedidosController::class, 'createCompleto']);
Route::put   ('/pedidos/{id}',      [PedidosController::class, 'update']);
Route::delete('/pedidos/{id}',      [PedidosController::class, 'cancel']);

// ─────────────────────────────────────────────────────────────────
//  PRODUCTOS-PEDIDOS
// ─────────────────────────────────────────────────────────────────
Route::get   ('/productos-pedidos',      [ProductosPedidosController::class, 'index']);   // ?selects=1
Route::post  ('/productos-pedidos',      [ProductosPedidosController::class, 'create']);
Route::put   ('/productos-pedidos/{id}', [ProductosPedidosController::class, 'update']);
Route::delete('/productos-pedidos/{id}', [ProductosPedidosController::class, 'delete']);

// ─────────────────────────────────────────────────────────────────
//  NOTIFICACIONES
// ─────────────────────────────────────────────────────────────────
Route::get   ('/notificaciones/cliente/{documento}',              [NotificacionController::class, 'porCliente']);
Route::patch ('/notificaciones/cliente/{documento}/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas']);
Route::get   ('/notificaciones',                                  [NotificacionController::class, 'index']);
Route::post  ('/notificaciones',                                  [NotificacionController::class, 'create']);
Route::put   ('/notificaciones/{id}',                             [NotificacionController::class, 'update']);
Route::patch ('/notificaciones/{id}/marcar-leida',                [NotificacionController::class, 'marcarLeida']);
Route::delete('/notificaciones/{id}',                             [NotificacionController::class, 'destroy']);