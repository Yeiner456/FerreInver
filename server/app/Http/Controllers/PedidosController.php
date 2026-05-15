<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\ProductoPedido;
use App\Models\Stock;
use App\Http\Requests\Pedidos\StorePedidoRequest;
use App\Http\Requests\Pedidos\StorePedidoCompletoRequest;
use App\Http\Requests\Pedidos\UpdatePedidoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidosController extends Controller
{
    // GET /api/pedidos
    // GET /api/pedidos?selects=1
    // GET /api/pedidos?documento=X
    public function index(Request $request)
    {
        if ($request->has('documento')) {
            $documento = $request->query('documento');
            if (!is_numeric($documento) || $documento <= 0)
                return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

            $data = Pedido::with('productos')->where('id_cliente', $documento)->get();
            return response()->json(['success' => true, 'data' => $data]);
        }

        if ($request->has('selects')) {
            $clientes = Cliente::where('estado_inicio_sesion', 'activo')->get(['documento', 'nombre']);
            return response()->json(['success' => true, 'data' => ['clientes' => $clientes]]);
        }

        $data = Pedido::with(['cliente', 'productos'])->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/pedidos
    public function create(StorePedidoRequest $request)
    {
        $pedido = Pedido::create($request->validated());

        return response()->json([
            'success'  => true,
            'message'  => 'Pedido registrado exitosamente.',
            'id_pedido' => $pedido->id_pedido,
        ], 201);
    }

    // POST /api/pedidos/completo → carrito cliente con items
    public function createCompleto(StorePedidoCompletoRequest $request)
    {
        $data  = $request->validated();
        $items = $data['items'];

        DB::beginTransaction();
        try {
            $stockErrors    = [];
            $stocksAfectados = [];

            foreach ($items as $item) {
                $stock = Stock::where('id_producto', $item['id_producto'])->lockForUpdate()->first();

                if (!$stock || $stock->cantidad < $item['cantidad']) {
                    $stockErrors[] = [
                        'nombre'     => $item['descripcion'] ?? "Producto {$item['id_producto']}",
                        'pedido'     => $item['cantidad'],
                        'disponible' => $stock ? $stock->cantidad : 0,
                    ];
                } else {
                    $stocksAfectados[] = ['stock' => $stock, 'cantidad' => $item['cantidad']];
                }
            }

            if (!empty($stockErrors)) {
                DB::rollBack();
                return response()->json([
                    'success'      => false,
                    'message'      => 'Stock insuficiente para uno o más productos.',
                    'stock_errors' => $stockErrors,
                ], 409);
            }

            $pedido = Pedido::create([
                'id_cliente'    => (int) $data['id_cliente'],
                'medio_pago'    => $data['medio_pago'],
                'estado_pedido' => 'pendiente',
            ]);

            foreach ($items as $item) {
                ProductoPedido::create([
                    'id_pedido'   => $pedido->id_pedido,
                    'id_producto' => $item['id_producto'],
                    'descripcion' => $item['descripcion'] ?? '',
                    'cantidad'    => $item['cantidad'],
                ]);
            }

            foreach ($stocksAfectados as $entrada) {
                $entrada['stock']->decrement('cantidad', $entrada['cantidad']);
            }

            DB::commit();
            return response()->json([
                'success'  => true,
                'message'  => 'Pedido registrado exitosamente.',
                'id_pedido' => $pedido->id_pedido,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar el pedido.'], 500);
        }
    }

    // PUT /api/pedidos/{id}
    public function update(UpdatePedidoRequest $request, $id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido)
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        $pedido->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Pedido actualizado exitosamente.']);
    }

    // DELETE /api/pedidos/{id} → soft delete (cancelar)
    public function cancel($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido)
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        if ($pedido->estado_pedido === 'cancelado')
            return response()->json(['success' => false, 'message' => 'El pedido ya está cancelado.'], 409);

        $pedido->update(['estado_pedido' => 'cancelado']);

        return response()->json(['success' => true, 'message' => 'Pedido cancelado exitosamente.']);
    }
}