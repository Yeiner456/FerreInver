<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
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
    public function create(Request $request)
    {
        $nombre      = trim($request->input('nombre', ''));
        $precio      = $request->input('precio', '');
        $descripcion = trim($request->input('descripcion', '')) ?: 'Producto de ferreinver disponible';

        if (empty($nombre) || $precio === '')
            return response()->json(['success' => false, 'message' => 'El nombre y el precio son obligatorios.'], 400);

        if (strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'El nombre debe tener entre 1 y 30 caracteres.'], 400);

        if (!is_numeric($precio) || $precio <= 0)
            return response()->json(['success' => false, 'message' => 'El precio debe ser un número mayor a 0.'], 400);

        if (floor($precio) != $precio)
            return response()->json(['success' => false, 'message' => 'El precio debe ser un número entero.'], 400);

        if (strlen($descripcion) > 100)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 100 caracteres.'], 400);

        $imagenUrl = null;
        if ($request->hasFile('imagen')) {
            $res = $this->subirImagen($request->file('imagen'));
            if (!$res['ok'])
                return response()->json(['success' => false, 'message' => $res['msg']], 400);
            $imagenUrl = $res['url'];
        }

        Producto::create([
            'nombre'      => $nombre,
            'precio'      => (int) $precio,
            'descripcion' => $descripcion,
            'imagen'      => $imagenUrl,
        ]);

        return response()->json(['success' => true, 'message' => 'Producto registrado exitosamente.'], 201);
    }

    // POST /api/productos/{id}?_method=PUT  (FormData con imagen opcional)
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $nombre      = trim($request->input('nombre', ''));
        $precio      = $request->input('precio', '');
        $descripcion = trim($request->input('descripcion', '')) ?: 'Producto de ferreinver disponible';

        if (empty($nombre) || $precio === '')
            return response()->json(['success' => false, 'message' => 'El nombre y el precio son obligatorios.'], 400);

        if (strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'El nombre no puede exceder 30 caracteres.'], 400);

        if (!is_numeric($precio) || $precio <= 0)
            return response()->json(['success' => false, 'message' => 'El precio debe ser un número mayor a 0.'], 400);

        if (floor($precio) != $precio)
            return response()->json(['success' => false, 'message' => 'El precio debe ser un número entero.'], 400);

        if (strlen($descripcion) > 100)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 100 caracteres.'], 400);

        $producto = Producto::find($id);
        if (!$producto)
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        $imagenUrl = $producto->imagen;

        if ($request->hasFile('imagen')) {
            // Borrar imagen vieja
            if ($imagenUrl) {
                $oldPath = str_replace($this->uploadUrl, '', $imagenUrl);
                Storage::delete($this->uploadDir . '/' . $oldPath);
            }

            $res = $this->subirImagen($request->file('imagen'));
            if (!$res['ok'])
                return response()->json(['success' => false, 'message' => $res['msg']], 400);
            $imagenUrl = $res['url'];
        }

        $producto->update([
            'nombre'      => $nombre,
            'precio'      => (int) $precio,
            'descripcion' => $descripcion,
            'imagen'      => $imagenUrl,
        ]);

        return response()->json(['success' => true, 'message' => 'Producto actualizado exitosamente.']);
    }

    // DELETE /api/productos/{id}  → soft delete
    public function deactivate($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

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
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize      = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file->getMimeType(), $allowedTypes))
            return ['ok' => false, 'msg' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];

        if ($file->getSize() > $maxSize)
            return ['ok' => false, 'msg' => 'La imagen no puede superar 2MB.'];

        $filename = uniqid('prod_', true) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('uploads/productos', $filename, 'public');

        return ['ok' => true, 'url' => $this->uploadUrl . $filename];
    }
}