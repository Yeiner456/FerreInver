<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Http\Requests\Proveedores\CreateProveedorRequest;
use App\Http\Requests\Proveedores\UpdateProveedorRequest;

class ProveedoresController extends Controller
{
    // GET /api/proveedores
    public function index()
    {
        return response()->json(['success' => true, 'data' => Proveedor::all()]);
    }

    // POST /api/proveedores
    public function create(CreateProveedorRequest $request)
    {
        Proveedor::create([
            'nit_proveedor' => (int) $request->nit,
            'correo'        => $request->correo,
            'direccion'     => $request->direccion,
            'telefono'      => $request->telefono,
            'estado'        => $request->estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Proveedor registrado exitosamente.'], 201);
    }

    // PUT /api/proveedores/{nit}
    public function update(UpdateProveedorRequest $request, $nit)
    {
        if (!is_numeric($nit))
            return response()->json(['success' => false, 'message' => 'NIT inválido.'], 400);

        $proveedor = Proveedor::find((int) $nit);

        if (!$proveedor)
            return response()->json(['success' => false, 'message' => 'El proveedor no existe.'], 404);

        $proveedor->update([
            'correo'    => $request->correo,
            'direccion' => $request->direccion,
            'telefono'  => $request->telefono,
            'estado'    => $request->estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Proveedor actualizado exitosamente.']);
    }

    // DELETE /api/proveedores/{nit}  → soft delete
    public function deactivate($nit)
    {
        if (!is_numeric($nit))
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