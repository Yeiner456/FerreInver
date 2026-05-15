<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Http\Requests\Productos\StoreProductoRequest;
use App\Http\Requests\Productos\UpdateProductoRequest;
use Illuminate\Support\Facades\Storage;

class ProductosController extends Controller
{
    private string $uploadDir = 'uploads/productos';
    private string $uploadUrl = 'storage/uploads/productos/';

    // GET /api/productos
    public function index()
    {
        $data = Producto::with('stock')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/productos  (FormData con imagen opcional)
    public function create(StoreProductoRequest $request)
    {
        $data = $request->validated();

        $imagenUrl = null;
        if ($request->hasFile('imagen')) {
            $res = $this->subirImagen($request->file('imagen'));
            $imagenUrl = $res['url'];
        }

        Producto::create([
            'nombre'      => $data['nombre'],
            'precio'      => (int) $data['precio'],
            'descripcion' => $data['descripcion'] ?? 'Producto de ferreinver disponible',
            'imagen'      => $imagenUrl,
        ]);

        return response()->json(['success' => true, 'message' => 'Producto registrado exitosamente.'], 201);
    }

    // POST /api/productos/{id}?_method=PUT  (FormData con imagen opcional)
    public function update(UpdateProductoRequest $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto)
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        $data      = $request->validated();
        $imagenUrl = $producto->imagen;

        if ($request->hasFile('imagen')) {
            if ($imagenUrl) {
                $oldPath = str_replace($this->uploadUrl, '', $imagenUrl);
                Storage::delete($this->uploadDir . '/' . $oldPath);
            }

            $res       = $this->subirImagen($request->file('imagen'));
            $imagenUrl = $res['url'];
        }

        $producto->update([
            'nombre'      => $data['nombre'],
            'precio'      => (int) $data['precio'],
            'descripcion' => $data['descripcion'] ?? 'Producto de ferreinver disponible',
            'imagen'      => $imagenUrl,
        ]);

        return response()->json(['success' => true, 'message' => 'Producto actualizado exitosamente.']);
    }

    // DELETE /api/productos/{id} → soft delete
    public function deactivate($id)
    {
        $producto = Producto::find($id);
        if (!$producto)
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        if ($producto->estado_producto === 'inactivo')
            return response()->json(['success' => false, 'message' => 'El producto ya está desactivado.'], 409);

        $producto->update(['estado_producto' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Producto desactivado exitosamente.']);
    }

    // ─── HELPER: subir imagen ────────────────────────────────────────────────
    private function subirImagen($file): array
    {
        $filename = uniqid('prod_', true) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('uploads/productos', $filename, 'public');

        return ['url' => $this->uploadUrl . $filename];
    }
}