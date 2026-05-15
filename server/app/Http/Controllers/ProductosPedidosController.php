<?php

namespace App\Http\Controllers;

use App\Models\ProductoPedido;
use App\Models\Producto;
use App\Models\Pedido;
use App\Http\Requests\ProductosPedidos\StoreProductoPedidoRequest;
use App\Http\Requests\ProductosPedidos\UpdateProductoPedidoRequest;
use Illuminate\Http\Request;

class ProductosPedidosController extends Controller
{
    // GET /api/productos-pedidos
    // GET /api/productos-pedidos?selects=1
    public function index(Request $request)
    {
        if ($request->has('selects')) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'productos' => Producto::where('estado_producto', 'activo')->get(['id_producto', 'nombre']),
                    'pedidos'   => Pedido::where('estado_pedido', '!=', 'cancelado')->get(['id_pedido', 'id_cliente', 'medio_pago']),
                ],
            ]);
        }

        $data = ProductoPedido::with(['producto', 'pedido'])->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/productos-pedidos
    public function create(StoreProductoPedidoRequest $request)
    {
        $data = $request->validated();

        ProductoPedido::create([
            'id_producto' => (int) $data['id_producto'],
            'id_pedido'   => (int) $data['id_pedido'],
            'descripcion' => $data['descripcion'],
            'cantidad'    => (int) $data['cantidad'],
        ]);

        return response()->json(['success' => true, 'message' => 'Producto-Pedido registrado exitosamente.'], 201);
    }

    // PUT /api/productos-pedidos/{id}
    public function update(UpdateProductoPedidoRequest $request, $id)
    {
        $registro = ProductoPedido::find($id);
        if (!$registro)
            return response()->json(['success' => false, 'message' => 'El registro no existe.'], 404);

        $data = $request->validated();

        $registro->update([
            'descripcion' => $data['descripcion'],
            'cantidad'    => (int) $data['cantidad'],
        ]);

        return response()->json(['success' => true, 'message' => 'Registro actualizado exitosamente.']);
    }

    // DELETE /api/productos-pedidos/{id}
    public function delete($id)
    {
        $registro = ProductoPedido::find($id);
        if (!$registro)
            return response()->json(['success' => false, 'message' => 'El registro no existe.'], 404);

        $registro->delete();

        return response()->json(['success' => true, 'message' => 'Producto eliminado del pedido exitosamente.']);
    }
}