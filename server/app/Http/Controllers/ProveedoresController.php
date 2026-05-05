<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedoresController extends Controller
{
    // GET /api/proveedores
    public function index()
    {
        return response()->json(['success' => true, 'data' => Proveedor::all()]);
    }

    // POST /api/proveedores
    public function create(Request $request)
    {
        $nit       = trim($request->input('nit', ''));
        $correo    = trim($request->input('correo', ''));
        $direccion = trim($request->input('direccion', ''));
        $telefono  = trim($request->input('telefono', ''));
        $estado    = trim($request->input('estado', ''));

        if (empty($nit) || empty($correo) || empty($direccion) || empty($telefono) || empty($estado))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($nit) || $nit <= 0 || strlen($nit) > 11)
            return response()->json(['success' => false, 'message' => 'El NIT debe ser un número válido de máximo 11 dígitos.'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 80)
            return response()->json(['success' => false, 'message' => 'El correo no es válido o excede 80 caracteres.'], 400);

        if (strlen($direccion) > 80)
            return response()->json(['success' => false, 'message' => 'La dirección debe tener entre 1 y 80 caracteres.'], 400);

        if (!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono) || strlen($telefono) > 20)
            return response()->json(['success' => false, 'message' => 'Teléfono inválido. Solo números, espacios, guiones, paréntesis y +'], 400);

        if (strlen(preg_replace("/[^0-9]/", "", $telefono)) < 7)
            return response()->json(['success' => false, 'message' => 'El teléfono debe tener al menos 7 dígitos.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 400);

        if (Proveedor::find((int) $nit))
            return response()->json(['success' => false, 'message' => 'El NIT ya está registrado.'], 409);

        if (Proveedor::where('correo', $correo)->exists())
            return response()->json(['success' => false, 'message' => 'El correo ya está registrado.'], 409);

        Proveedor::create([
            'nit_proveedor' => (int) $nit,
            'correo'        => $correo,
            'direccion'     => $direccion,
            'telefono'      => $telefono,
            'estado'        => $estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Proveedor registrado exitosamente.'], 201);
    }

    // PUT /api/proveedores/{nit}
    public function update(Request $request, $nit)
    {
        if (!$nit || !is_numeric($nit))
            return response()->json(['success' => false, 'message' => 'NIT inválido.'], 400);

        $correo    = trim($request->input('correo', ''));
        $direccion = trim($request->input('direccion', ''));
        $telefono  = trim($request->input('telefono', ''));
        $estado    = trim($request->input('estado', ''));

        if (empty($correo) || empty($direccion) || empty($telefono) || empty($estado))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 80)
            return response()->json(['success' => false, 'message' => 'El correo no es válido o excede 80 caracteres.'], 400);

        if (strlen($direccion) > 80)
            return response()->json(['success' => false, 'message' => 'La dirección debe tener entre 1 y 80 caracteres.'], 400);

        if (!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono) || strlen($telefono) > 20)
            return response()->json(['success' => false, 'message' => 'Teléfono inválido.'], 400);

        if (strlen(preg_replace("/[^0-9]/", "", $telefono)) < 7)
            return response()->json(['success' => false, 'message' => 'El teléfono debe tener al menos 7 dígitos.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 400);

        $proveedor = Proveedor::find((int) $nit);
        if (!$proveedor)
            return response()->json(['success' => false, 'message' => 'El proveedor no existe.'], 404);

        if (Proveedor::where('correo', $correo)->where('nit_proveedor', '!=', $nit)->exists())
            return response()->json(['success' => false, 'message' => 'El correo ya está registrado en otro proveedor.'], 409);

        $proveedor->update(compact('correo', 'direccion', 'telefono', 'estado'));

        return response()->json(['success' => true, 'message' => 'Proveedor actualizado exitosamente.']);
    }

    // DELETE /api/proveedores/{nit}  → soft delete
    public function deactivate($nit)
    {
        if (!$nit || !is_numeric($nit))
            return response()->json(['success' => false, 'message' => 'NIT inválido.'], 400);

        $proveedor = Proveedor::find((int) $nit);
        if (!$proveedor)
            return response()->json(['success' => false, 'message' => 'El proveedor no existe.'], 404);

        if ($proveedor->estado === 'inactivo')
            return response()->json(['success' => false, 'message' => 'El proveedor ya está desactivado.'], 409);

        $proveedor->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Proveedor desactivado exitosamente.']);
    }
}