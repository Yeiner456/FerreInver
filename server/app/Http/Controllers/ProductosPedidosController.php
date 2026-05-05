<?php

namespace App\Http\Controllers;

use App\Models\ProductoPedido;
use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Http\Request;

class ProductosPedidosController extends Controller
{
    // GET /api/productos-pedidos
    // GET /api/productos-pedidos?selects=1
    public function index(Request $request)
    {
        if ($request->has('selects')) {
            return response()->json([
                'success'   => true,
                'data'      => [
                    'productos' => Producto::where('estado_producto', 'activo')->get(['id_producto', 'nombre']),
                    'pedidos'   => Pedido::where('estado_pedido', '!=', 'cancelado')->get(['id_pedido', 'id_cliente', 'medio_pago']),
                ],
            ]);
        }

        $data = ProductoPedido::with(['producto', 'pedido'])->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/productos-pedidos
    public function create(Request $request)
    {
        $id_producto = $request->input('id_producto', '');
        $id_pedido   = $request->input('id_pedido', '');
        $descripcion = trim($request->input('descripcion', ''));
        $cantidad    = $request->input('cantidad', '');

        if (empty($id_producto) || empty($id_pedido) || empty($descripcion) || $cantidad === '')
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion))
            return response()->json(['success' => false, 'message' => 'La descripción contiene caracteres no permitidos.'], 400);

        if (strlen($descripcion) > 100)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 100 caracteres.'], 400);

        if (!is_numeric($cantidad) || $cantidad <= 0 || $cantidad > 1000)
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser entre 1 y 1000.'], 400);

        if (!Producto::find($id_producto))
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        if (!Pedido::find($id_pedido))
            return response()->json(['success' => false, 'message' => 'El pedido no existe.'], 404);

        ProductoPedido::create([
            'id_producto' => (int) $id_producto,
            'id_pedido'   => (int) $id_pedido,
            'descripcion' => $descripcion,
            'cantidad'    => (int) $cantidad,
        ]);

        return response()->json(['success' => true, 'message' => 'Producto-Pedido registrado exitosamente.'], 201);
    }

    // PUT /api/productos-pedidos/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $descripcion = trim($request->input('descripcion', ''));
        $cantidad    = $request->input('cantidad', '');

        if (empty($descripcion) || $cantidad === '')
            return response()->json(['success' => false, 'message' => 'Descripción y cantidad son obligatorios.'], 400);

        if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion))
            return response()->json(['success' => false, 'message' => 'La descripción contiene caracteres no permitidos.'], 400);

        if (strlen($descripcion) > 100)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 100 caracteres.'], 400);

        if (!is_numeric($cantidad) || $cantidad <= 0 || $cantidad > 1000)
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser entre 1 y 1000.'], 400);

        $registro = ProductoPedido::find($id);
        if (!$registro)
            return response()->json(['success' => false, 'message' => 'El registro no existe.'], 404);

        $registro->update(['descripcion' => $descripcion, 'cantidad' => (int) $cantidad]);

        return response()->json(['success' => true, 'message' => 'Registro actualizado exitosamente.']);
    }

    // DELETE /api/productos-pedidos/{id}
    public function delete($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $registro = ProductoPedido::find($id);
        if (!$registro)
            return response()->json(['success' => false, 'message' => 'El registro no existe.'], 404);

        $registro->delete();

        return response()->json(['success' => true, 'message' => 'Producto eliminado del pedido exitosamente.']);
    }
}