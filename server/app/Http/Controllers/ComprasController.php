<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Stock;
use App\Http\Requests\Compras\CreateCompraRequest;
use App\Http\Requests\Compras\UpdateCompraRequest;

class ComprasController extends Controller
{
    // GET /api/compras
    public function index()
    {
        $compras = Compra::with(['producto', 'proveedor'])->get();
        return response()->json(['success' => true, 'data' => $compras]);
    }

    // GET /api/compras/selects
    public function selects()
    {
        return response()->json([
            'success'     => true,
            'productos'   => Producto::where('estado_producto', 'activo')->get(['id_producto', 'nombre']),
            'proveedores' => Proveedor::where('estado', 'activo')->get(['nit_proveedor', 'correo']),
        ]);
    }

    // POST /api/compras
    public function create(CreateCompraRequest $request)
    {
        Compra::create([
            'cantidad'     => (int) $request->cantidad,
            'descripcion'  => $request->descripcion,
            'id_proveedor' => $request->id_proveedor,
            'id_producto'  => $request->id_producto,
        ]);

        // Aumentar stock del producto
        $stock = Stock::where('id_producto', $request->id_producto)->first();

        if ($stock) {
            $stock->increment('cantidad', (int) $request->cantidad);
        } else {
            Stock::create([
                'id_producto' => $request->id_producto,
                'cantidad'    => (int) $request->cantidad,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Compra registrada exitosamente.'], 201);
    }

    // PUT /api/compras/{id}
    public function update(UpdateCompraRequest $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $compra = Compra::find($id);

        if (!$compra)
            return response()->json(['success' => false, 'message' => 'La compra no existe.'], 404);

        $compra->update([
            'cantidad'    => (int) $request->cantidad,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json(['success' => true, 'message' => 'Compra actualizada exitosamente.']);
    }

    // DELETE /api/compras/{id}
    public function delete($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $compra = Compra::find($id);

        if (!$compra)
            return response()->json(['success' => false, 'message' => 'La compra no existe.'], 404);

        $compra->delete();

        return response()->json(['success' => true, 'message' => 'Compra eliminada exitosamente.']);
    }
}