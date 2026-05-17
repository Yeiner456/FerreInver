<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Http\Requests\Productos\StoreProductoRequest;
use App\Http\Requests\Productos\UpdateProductoRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductosController extends Controller
{
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
            $imagenUrl = $this->subirImagen($request->file('imagen'));
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
            // Eliminar imagen anterior de Cloudinary si existe
            if ($imagenUrl) {
                $publicId = pathinfo(parse_url($imagenUrl, PHP_URL_PATH), PATHINFO_FILENAME);
                Cloudinary::destroy('ferreinver/productos/' . $publicId);
            }

            $imagenUrl = $this->subirImagen($request->file('imagen'));
        }

        $producto->update([
            'nombre'          => $data['nombre'],
            'precio'          => (int) $data['precio'],
            'descripcion'     => $data['descripcion'] ?? 'Producto de ferreinver disponible',
            'imagen'          => $imagenUrl,
            'estado_producto' => $request->input('estado_producto', $producto->estado_producto),
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

    // ─── HELPER: subir imagen a Cloudinary ──────────────────────────────────
    private function subirImagen($file): string
    {
        $result = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'ferreinver/productos',
        ]);

        return $result->getSecurePath();
    }
}