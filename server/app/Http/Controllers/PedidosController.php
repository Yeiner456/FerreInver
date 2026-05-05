<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\ProductoPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidosController extends Controller
{
    private array $mediosValidos  = ['Efectivo', 'Tarjeta Débito', 'Tarjeta Crédito', 'Transferencia', 'PSE', 'Nequi', 'Daviplata'];
    private array $estadosValidos = ['pendiente', 'recibido', 'listo para recibir', 'cancelado'];

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
    public function create(Request $request)
    {
        $id_cliente    = $request->input('id_cliente', '');
        $medio_pago    = trim($request->input('medio_pago', ''));
        $estado_pedido = trim($request->input('estado_pedido', ''));

        if (empty($id_cliente) || empty($medio_pago) || empty($estado_pedido))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($id_cliente) || $id_cliente <= 0)
            return response()->json(['success' => false, 'message' => 'ID de cliente inválido.'], 400);

        if (!in_array($medio_pago, $this->mediosValidos))
            return response()->json(['success' => false, 'message' => 'Medio de pago inválido.'], 400);

        if (!in_array($estado_pedido, $this->estadosValidos))
            return response()->json(['success' => false, 'message' => 'Estado del pedido inválido.'], 400);

        if (!Cliente::find($id_cliente))
            return response()->json(['success' => false, 'message' => 'El cliente no existe.'], 404);

        $pedido = Pedido::create([
            'id_cliente'    => (int) $id_cliente,
            'medio_pago'    => $medio_pago,
            'estado_pedido' => $estado_pedido,
        ]);

        return response()->json(['success' => true, 'message' => 'Pedido registrado exitosamente.', 'id_pedido' => $pedido->id_pedido], 201);
    }

    // POST /api/pedidos/completo  → carrito cliente con items
    public function createCompleto(Request $request)
    {
        $id_cliente = $request->input('id_cliente', '');
        $medio_pago = trim($request->input('medio_pago', ''));
        $items      = $request->input('items', []);

        if (empty($id_cliente) || empty($medio_pago) || empty($items))
            return response()->json(['success' => false, 'message' => 'Faltan datos obligatorios.'], 400);

        if (!in_array($medio_pago, $this->mediosValidos))
            return response()->json(['success' => false, 'message' => 'Medio de pago inválido.'], 400);

        if (!is_array($items) || count($items) === 0)
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.'], 400);

        if (!Cliente::find($id_cliente))
            return response()->json(['success' => false, 'message' => 'El cliente no existe.'], 404);

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'id_cliente'    => (int) $id_cliente,
                'medio_pago'    => $medio_pago,
                'estado_pedido' => 'pendiente',
            ]);

            foreach ($items as $item) {
                ProductoPedido::create([
                    'id_pedido'   => $pedido->id_pedido,
                    'id_producto' => $item['id_producto'],
                    'descripcion' => $item['descripcion'] ?? $item['nombre'] ?? '',
                    'cantidad'    => $item['cantidad'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pedido registrado exitosamente.', 'id_pedido' => $pedido->id_pedido], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar el pedido.'], 500);
        }
    }

    // PUT /api/pedidos/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $id_cliente    = $request->input('id_cliente', '');
        $medio_pago    = trim($request->input('medio_pago', ''));
        $estado_pedido = trim($request->input('estado_pedido', ''));

        if (empty($id_cliente) || empty($medio_pago) || empty($estado_pedido))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($id_cliente) || $id_cliente <= 0)
            return response()->json(['success' => false, 'message' => 'ID de cliente inválido.'], 400);

        if (!in_array($medio_pago, $this->mediosValidos))
            return response()->json(['success' => false, 'message' => 'Medio de pago inválido.'], 400);

        if (!in_array($estado_pedido, $this->estadosValidos))
            return response()->json(['success' => false, 'message' => 'Estado del pedido inválido.'], 400);

        $pedido = Pedido::find($id);
        if (!$pedido)
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        if (!Cliente::find($id_cliente))
            return response()->json(['success' => false, 'message' => 'El cliente no existe.'], 404);

        $pedido->update([
            'id_cliente'    => (int) $id_cliente,
            'medio_pago'    => $medio_pago,
            'estado_pedido' => $estado_pedido,
        ]);

        return response()->json(['success' => true, 'message' => 'Pedido actualizado exitosamente.']);
    }

    // DELETE /api/pedidos/{id}  → soft delete (cancelar)
    public function cancel($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $pedido = Pedido::find($id);
        if (!$pedido)
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        if ($pedido->estado_pedido === 'cancelado')
            return response()->json(['success' => false, 'message' => 'El pedido ya está cancelado.'], 409);

        $pedido->update(['estado_pedido' => 'cancelado']);

        return response()->json(['success' => true, 'message' => 'Pedido cancelado exitosamente.']);
    }
}