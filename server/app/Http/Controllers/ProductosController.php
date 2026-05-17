<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Http\Requests\Productos\StoreProductoRequest;
use App\Http\Requests\Productos\UpdateProductoRequest;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class ProductosController extends Controller
{
    private function getCloudinary(): Cloudinary
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true],
        ]);

        return new Cloudinary();
    }

    public function index()
    {
        $data = Producto::with('stock')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

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

    public function update(UpdateProductoRequest $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto)
            return response()->json(['success' => false, 'message' => 'El producto no existe.'], 404);

        $data      = $request->validated();
        $imagenUrl = $producto->imagen;

        if ($request->hasFile('imagen')) {
            if ($imagenUrl) {
                $publicId = 'ferreinver/productos/' . pathinfo(parse_url($imagenUrl, PHP_URL_PATH), PATHINFO_FILENAME);
                $this->getCloudinary()->uploadApi()->destroy($publicId);
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

    private function subirImagen($file): string
    {
        $cloudinary = $this->getCloudinary();

        $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => 'ferreinver/productos',
        ]);

        return $result['secure_url'];
    }
}