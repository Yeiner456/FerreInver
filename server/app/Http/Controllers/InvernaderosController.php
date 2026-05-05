<?php

namespace App\Http\Controllers;

use App\Models\Invernadero;
use Illuminate\Http\Request;

class InvernaderosController extends Controller
{
    // GET /api/invernaderos
    public function index()
    {
        return response()->json(['success' => true, 'data' => Invernadero::all()]);
    }

    // POST /api/invernaderos
    public function create(Request $request)
    {
        $nombre      = trim($request->input('nombre', ''));
        $descripcion = trim($request->input('descripcion', ''));
        $precio_m2   = $request->input('precio_m2', '');
        $estado      = trim($request->input('estado', ''));

        if (empty($nombre) || $precio_m2 === '' || empty($estado))
            return response()->json(['success' => false, 'message' => 'Nombre, precio m² y estado son obligatorios.'], 400);

        if (strlen($nombre) > 50)
            return response()->json(['success' => false, 'message' => 'El nombre no puede exceder 50 caracteres.'], 400);

        if (strlen($descripcion) > 150)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 150 caracteres.'], 400);

        if (!is_numeric($precio_m2) || $precio_m2 <= 0 || $precio_m2 >= 9999999999.99)
            return response()->json(['success' => false, 'message' => 'El precio m² debe ser un número positivo válido.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 400);

        if (Invernadero::where('nombre', $nombre)->exists())
            return response()->json(['success' => false, 'message' => 'Ya existe un invernadero con ese nombre.'], 409);

        Invernadero::create([
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
            'precio_m2'   => (float) $precio_m2,
            'estado'      => $estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Invernadero registrado exitosamente.'], 201);
    }

    // PUT /api/invernaderos/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $nombre      = trim($request->input('nombre', ''));
        $descripcion = trim($request->input('descripcion', ''));
        $precio_m2   = $request->input('precio_m2', '');
        $estado      = trim($request->input('estado', ''));

        if (empty($nombre) || $precio_m2 === '' || empty($estado))
            return response()->json(['success' => false, 'message' => 'Nombre, precio m² y estado son obligatorios.'], 400);

        if (strlen($nombre) > 50)
            return response()->json(['success' => false, 'message' => 'El nombre no puede exceder 50 caracteres.'], 400);

        if (strlen($descripcion) > 150)
            return response()->json(['success' => false, 'message' => 'La descripción no puede exceder 150 caracteres.'], 400);

        if (!is_numeric($precio_m2) || $precio_m2 <= 0 || $precio_m2 >= 9999999999.99)
            return response()->json(['success' => false, 'message' => 'El precio m² debe ser un número positivo válido.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 400);

        $invernadero = Invernadero::find($id);
        if (!$invernadero)
            return response()->json(['success' => false, 'message' => 'El invernadero no existe.'], 404);

        if (Invernadero::where('nombre', $nombre)->where('id_invernadero', '!=', $id)->exists())
            return response()->json(['success' => false, 'message' => 'Ya existe otro invernadero con ese nombre.'], 409);

        $invernadero->update([
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
            'precio_m2'   => (float) $precio_m2,
            'estado'      => $estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Invernadero actualizado exitosamente.']);
    }

    // DELETE /api/invernaderos/{id}  → soft delete
    public function deactivate($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $invernadero = Invernadero::find($id);
        if (!$invernadero)
            return response()->json(['success' => false, 'message' => 'El invernadero no existe.'], 404);

        if ($invernadero->estado === 'inactivo')
            return response()->json(['success' => false, 'message' => 'El invernadero ya está desactivado.'], 409);

        $invernadero->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Invernadero desactivado exitosamente.']);
    }
}