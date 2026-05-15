<?php

namespace App\Http\Controllers;

use App\Models\TipoUsuario;
use App\Models\Cliente;
use App\Http\Requests\TiposUsuarios\CreateTipoUsuarioRequest;
use App\Http\Requests\TiposUsuarios\UpdateTipoUsuarioRequest;

class TiposUsuariosController extends Controller
{
    // GET /api/tipos-usuarios
    public function index()
    {
        return response()->json(['success' => true, 'data' => TipoUsuario::all()]);
    }

    // POST /api/tipos-usuarios
    public function create(CreateTipoUsuarioRequest $request)
    {
        TipoUsuario::create([
            'nombre' => $request->nombre,
            'estado' => $request->estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Tipo de usuario registrado exitosamente.'], 201);
    }

    // PUT /api/tipos-usuarios/{id}
    public function update(UpdateTipoUsuarioRequest $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $tipo = TipoUsuario::find($id);

        if (!$tipo)
            return response()->json(['success' => false, 'message' => 'El tipo de usuario no existe.'], 404);

        $tipo->update([
            'nombre' => $request->nombre,
            'estado' => $request->estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Tipo de usuario actualizado exitosamente.']);
    }

    // DELETE /api/tipos-usuarios/{id}
    public function delete($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $tipo = TipoUsuario::find($id);

        if (!$tipo)
            return response()->json(['success' => false, 'message' => 'El tipo de usuario no existe.'], 404);

        if (Cliente::where('id_tipo_de_usuario', $id)->exists())
            return response()->json(['success' => false, 'message' => 'No se puede eliminar: hay clientes asociados a este tipo de usuario.'], 409);

        $tipo->delete();

        return response()->json(['success' => true, 'message' => 'Tipo de usuario eliminado exitosamente.']);
    }
}