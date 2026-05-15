<?php

namespace App\Http\Controllers;

use App\Models\Invernadero;
use App\Http\Requests\Invernaderos\StoreInvernaderoRequest;
use App\Http\Requests\Invernaderos\UpdateInvernaderoRequest;

class InvernaderosController extends Controller
{
    // GET /api/invernaderos
    public function index()
    {
        return response()->json(['success' => true, 'data' => Invernadero::all()]);
    }

    // POST /api/invernaderos
    public function create(StoreInvernaderoRequest $request)
    {
        Invernadero::create($request->validated());

        return response()->json(['success' => true, 'message' => 'Invernadero registrado exitosamente.'], 201);
    }

    // PUT /api/invernaderos/{id}
    public function update(UpdateInvernaderoRequest $request, $id)
    {
        $invernadero = Invernadero::find($id);
        if (!$invernadero)
            return response()->json(['success' => false, 'message' => 'El invernadero no existe.'], 404);

        $invernadero->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Invernadero actualizado exitosamente.']);
    }

    // DELETE /api/invernaderos/{id} → soft delete
    public function deactivate($id)
    {
        $invernadero = Invernadero::find($id);
        if (!$invernadero)
            return response()->json(['success' => false, 'message' => 'El invernadero no existe.'], 404);

        if ($invernadero->estado === 'inactivo')
            return response()->json(['success' => false, 'message' => 'El invernadero ya está desactivado.'], 409);

        $invernadero->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Invernadero desactivado exitosamente.']);
    }
}