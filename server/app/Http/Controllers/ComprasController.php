<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;

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
    public function create(Request $request)
    {
        $cantidad     = $request->input('cantidad', '');
        $descripcion  = trim($request->input('descripcion', ''));
        $id_producto  = $request->input('id_producto', '');
        $id_proveedor = $request->input('id_proveedor', '');

        if (empty($cantidad) || empty($descripcion) || empty($id_producto) || empty($id_proveedor))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($cantidad) || $cantidad <= 0)
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser un número mayor a 0.'], 400);

        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion))
            return response()->json(['success' => false, 'message' => 'La descripción solo puede contener letras, números y espacios.'], 400);

        if (strlen($descripcion) > 150)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 150 caracteres.'], 400);

        if (!Producto::find($id_producto))
            return response()->json(['success' => false, 'message' => 'El producto seleccionado no existe.'], 404);

        if (!Proveedor::find($id_proveedor))
            return response()->json(['success' => false, 'message' => 'El proveedor seleccionado no existe.'], 404);

        Compra::create([
            'cantidad'     => (int) $cantidad,
            'descripcion'  => $descripcion,
            'id_proveedor' => $id_proveedor,
            'id_producto'  => $id_producto,
        ]);

        return response()->json(['success' => true, 'message' => 'Compra registrada exitosamente.'], 201);
    }

    // PUT /api/compras/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $cantidad    = $request->input('cantidad', '');
        $descripcion = trim($request->input('descripcion', ''));

        if (empty($cantidad) || empty($descripcion))
            return response()->json(['success' => false, 'message' => 'Cantidad y descripción son obligatorios.'], 400);

        if (!is_numeric($cantidad) || $cantidad <= 0)
            return response()->json(['success' => false, 'message' => 'La cantidad debe ser un número mayor a 0.'], 400);

        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion))
            return response()->json(['success' => false, 'message' => 'La descripción solo puede contener letras, números y espacios.'], 400);

        if (strlen($descripcion) > 150)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 150 caracteres.'], 400);

        $compra = Compra::find($id);
        if (!$compra)
            return response()->json(['success' => false, 'message' => 'La compra no existe.'], 404);

        $compra->update(['cantidad' => (int) $cantidad, 'descripcion' => $descripcion]);

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