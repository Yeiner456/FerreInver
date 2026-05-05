<?php

namespace App\Http\Controllers;

use App\Models\TipoUsuario;
use App\Models\Cliente;
use Illuminate\Http\Request;

class TiposUsuariosController extends Controller
{
    // GET /api/tipos-usuarios
    public function index()
    {
        return response()->json(['success' => true, 'data' => TipoUsuario::all()]);
    }

    // POST /api/tipos-usuarios
    public function create(Request $request)
    {
        $nombre = trim($request->input('nombre', ''));
        $estado = trim($request->input('estado', ''));

        if (!$nombre || !$estado)
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'El nombre no puede exceder 30 caracteres.'], 400);

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre))
            return response()->json(['success' => false, 'message' => 'El nombre solo puede contener letras y espacios.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'El estado no es válido.'], 400);

        if (TipoUsuario::where('nombre', $nombre)->exists())
            return response()->json(['success' => false, 'message' => 'Ya existe un tipo de usuario con ese nombre.'], 409);

        TipoUsuario::create(compact('nombre', 'estado'));

        return response()->json(['success' => true, 'message' => 'Tipo de usuario registrado exitosamente.'], 201);
    }

    // PUT /api/tipos-usuarios/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $nombre = trim($request->input('nombre', ''));
        $estado = trim($request->input('estado', ''));

        if (!$nombre || !$estado)
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'El nombre no puede exceder 30 caracteres.'], 400);

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre))
            return response()->json(['success' => false, 'message' => 'El nombre solo puede contener letras y espacios.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'El estado no es válido.'], 400);

        $tipo = TipoUsuario::find($id);
        if (!$tipo)
            return response()->json(['success' => false, 'message' => 'El tipo de usuario no existe.'], 404);

        if (TipoUsuario::where('nombre', $nombre)->where('id_tipo_de_usuario', '!=', $id)->exists())
            return response()->json(['success' => false, 'message' => 'Ya existe otro tipo de usuario con ese nombre.'], 409);

        $tipo->update(compact('nombre', 'estado'));

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