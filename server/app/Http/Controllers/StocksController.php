<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Producto;
use Illuminate\Http\Request;

class StocksController extends Controller
{
    // GET /api/stocks
    // GET /api/stocks?selects=1
    public function index(Request $request)
    {
        if ($request->has('selects')) {
            $productos = Producto::where('estado_producto', 'activo')->get(['id_producto', 'nombre']);
            return response()->json(['success' => true, 'data' => ['productos' => $productos]]);
        }

        $data = Stock::with('producto')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/stocks
    public function create(Request $request)
    {
        $id_producto = $request->input('id_producto', '');
        $cantidad    = $request->input('cantidad', '');

        if ($id_producto === '' || $cantidad === '')
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($id_producto) || $id_producto <= 0)
            return response()->json(['success' => false, 'message' => 'Producto inválido.'], 400);

        if (!is_numeric($cantidad) || $cantidad < 0 || floor($cantidad) != $cantidad)
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser un entero mayor o igual a 0.'], 400);

        if (!Producto::find($id_producto))
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        if (Stock::where('id_producto', $id_producto)->exists())
            return response()->json(['success' => false, 'message' => 'Ya existe un registro de stock para este producto.'], 409);

        Stock::create(['id_producto' => (int) $id_producto, 'cantidad' => (int) $cantidad]);

        return response()->json(['success' => true, 'message' => 'Stock registrado exitosamente.'], 201);
    }

    // PUT /api/stocks/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $id_producto = $request->input('id_producto', '');
        $cantidad    = $request->input('cantidad', '');

        if ($id_producto === '' || $cantidad === '')
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($id_producto) || $id_producto <= 0)
            return response()->json(['success' => false, 'message' => 'Producto inválido.'], 400);

        if (!is_numeric($cantidad) || $cantidad < 0 || floor($cantidad) != $cantidad)
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser un entero mayor o igual a 0.'], 400);

        $stock = Stock::find($id);
        if (!$stock)
            return response()->json(['success' => false, 'message' => 'El stock no existe.'], 404);

        if (!Producto::find($id_producto))
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        if (Stock::where('id_producto', $id_producto)->where('id_stock', '!=', $id)->exists())
            return response()->json(['success' => false, 'message' => 'Ya existe otro registro de stock para este producto.'], 409);

        $stock->update(['id_producto' => (int) $id_producto, 'cantidad' => (int) $cantidad]);

        return response()->json(['success' => true, 'message' => 'Stock actualizado exitosamente.']);
    }

    // DELETE /api/stocks/{id}
    public function delete($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $stock = Stock::find($id);
        if (!$stock)
            return response()->json(['success' => false, 'message' => 'El stock no existe.'], 404);

        $stock->delete();

        return response()->json(['success' => true, 'message' => 'Stock eliminado exitosamente.']);
    }
}