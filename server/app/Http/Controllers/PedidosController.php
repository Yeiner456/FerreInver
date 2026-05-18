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
            'success'   => true,
            'message'   => 'Pedido registrado exitosamente.',
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
            $stockErrors = [];

            // Solo verificar que haya stock suficiente, NO descontarlo
            foreach ($items as $item) {
                $stock = Stock::where('id_producto', $item['id_producto'])->first();

                if (!$stock || $stock->cantidad < $item['cantidad']) {
                    $stockErrors[] = [
                        'nombre'     => $item['descripcion'] ?? "Producto {$item['id_producto']}",
                        'pedido'     => $item['cantidad'],
                        'disponible' => $stock ? $stock->cantidad : 0,
                    ];
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

            // ← Stock NO se descuenta aquí, solo cuando el admin confirme

            DB::commit();
            return response()->json([
                'success'   => true,
                'message'   => 'Pedido registrado exitosamente.',
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

    // PATCH /api/pedidos/{id}/confirmar → admin confirma el pedido y descuenta stock
    public function confirmar($id)
    {
        $pedido = Pedido::with('productos')->find($id);

        if (!$pedido)
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        if ($pedido->estado_pedido !== 'pendiente')
            return response()->json(['success' => false, 'message' => 'Solo se pueden confirmar pedidos pendientes.'], 409);

        DB::beginTransaction();
        try {
            $stockErrors = [];

            // Verificar stock antes de descontar
            foreach ($pedido->productos as $producto) {
                $stock = Stock::where('id_producto', $producto->id_producto)->lockForUpdate()->first();
                $cantidad = $producto->pivot->cantidad;

                if (!$stock || $stock->cantidad < $cantidad) {
                    $stockErrors[] = [
                        'nombre'     => $producto->nombre,
                        'pedido'     => $cantidad,
                        'disponible' => $stock ? $stock->cantidad : 0,
                    ];
                }
            }

            if (!empty($stockErrors)) {
                DB::rollBack();
                return response()->json([
                    'success'      => false,
                    'message'      => 'Stock insuficiente para confirmar el pedido.',
                    'stock_errors' => $stockErrors,
                ], 409);
            }

            // Descontar stock
            foreach ($pedido->productos as $producto) {
                $stock = Stock::where('id_producto', $producto->id_producto)->first();
                $stock->decrement('cantidad', $producto->pivot->cantidad);
            }

            $pedido->update(['estado_pedido' => 'confirmado']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pedido confirmado y stock actualizado.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al confirmar el pedido.'], 500);
        }
    }

    // DELETE /api/pedidos/{id} → cancelar
    public function cancel($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido)
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        if ($pedido->estado_pedido === 'cancelado')
            return response()->json(['success' => false, 'message' => 'El pedido ya está cancelado.'], 409);

        // Si estaba confirmado, devolver el stock
        if ($pedido->estado_pedido === 'confirmado') {
            $pedido->load('productos');
            foreach ($pedido->productos as $producto) {
                $stock = Stock::where('id_producto', $producto->id_producto)->first();
                if ($stock) {
                    $stock->increment('cantidad', $producto->pivot->cantidad);
                }
            }
        }

        $pedido->update(['estado_pedido' => 'cancelado']);

        return response()->json(['success' => true, 'message' => 'Pedido cancelado exitosamente.']);
    }
}