<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TipoUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientesController extends Controller
{
    // GET /api/clientes
    public function index()
    {
        $clientes = Cliente::with('tipoUsuario')->get();
        return response()->json(['success' => true, 'data' => $clientes]);
    }

    // GET /api/clientes/tipos
    public function tipos()
    {
        $tipos = TipoUsuario::where('estado', 'activo')->get();
        return response()->json(['success' => true, 'data' => $tipos]);
    }

    // POST /api/clientes
    public function create(Request $request)
    {
        $documento          = trim($request->input('documento', ''));
        $id_tipo            = (int) trim($request->input('id_tipo_de_usuario', ''));
        $nombre             = trim($request->input('nombre', ''));
        $correo             = trim($request->input('correo', ''));
        $password           = trim($request->input('password', ''));
        $confirmar_password = trim($request->input('confirmar_password', ''));
        $estado             = trim($request->input('estado', ''));

        foreach (compact('documento', 'nombre', 'correo', 'password', 'confirmar_password', 'estado') as $campo => $valor) {
            if (empty($valor))
                return response()->json(['success' => false, 'message' => "El campo '$campo' es obligatorio."], 400);
        }
        if (!$id_tipo)
            return response()->json(['success' => false, 'message' => "El campo 'id_tipo_de_usuario' es obligatorio."], 400);

        if (!is_numeric($documento) || $documento <= 0 || strlen($documento) > 11)
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        if ($id_tipo <= 0)
            return response()->json(['success' => false, 'message' => 'Tipo de usuario inválido.'], 400);

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'Nombre inválido (solo letras, máx 30 caracteres).'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 50)
            return response()->json(['success' => false, 'message' => 'Correo inválido.'], 400);

        if ($password !== $confirmar_password)
            return response()->json(['success' => false, 'message' => 'Las contraseñas no coinciden.'], 400);

        if (strlen($password) < 6 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password))
            return response()->json(['success' => false, 'message' => 'Contraseña inválida (mín 6 caracteres, letras y números).'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 400);

        if (!TipoUsuario::find($id_tipo))
            return response()->json(['success' => false, 'message' => 'El tipo de usuario no existe.'], 400);

        if (Cliente::find($documento))
            return response()->json(['success' => false, 'message' => 'El documento ya está registrado.'], 409);

        if (Cliente::where('correo', $correo)->exists())
            return response()->json(['success' => false, 'message' => 'El correo ya está registrado.'], 409);

        Cliente::create([
            'documento'          => $documento,
            'id_tipo_de_usuario' => $id_tipo,
            'password_hash'      => Hash::make($password),
            'nombre'             => $nombre,
            'correo'             => $correo,
            'estado_inicio_sesion' => $estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Cliente registrado exitosamente.'], 201);
    }

    // PUT /api/clientes/{documento}
    public function update(Request $request, $documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $id_tipo            = (int) trim($request->input('id_tipo_de_usuario', ''));
        $nombre             = trim($request->input('nombre', ''));
        $correo             = trim($request->input('correo', ''));
        $estado             = trim($request->input('estado', ''));
        $password           = trim($request->input('password', ''));
        $confirmar_password = trim($request->input('confirmar_password', ''));

        if (!$id_tipo || !$nombre || !$correo || !$estado)
            return response()->json(['success' => false, 'message' => 'Todos los campos obligatorios deben estar llenos.'], 400);

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'Nombre inválido.'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 50)
            return response()->json(['success' => false, 'message' => 'Correo inválido.'], 400);

        if (!in_array($estado, ['activo', 'inactivo']))
            return response()->json(['success' => false, 'message' => 'Estado inválido.'], 400);

        if (Cliente::where('correo', $correo)->where('documento', '!=', $documento)->exists())
            return response()->json(['success' => false, 'message' => 'El correo ya está registrado en otro cliente.'], 409);

        $cliente = Cliente::find($documento);
        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado.'], 404);

        $datos = [
            'id_tipo_de_usuario'   => $id_tipo,
            'nombre'               => $nombre,
            'correo'               => $correo,
            'estado_inicio_sesion' => $estado,
        ];

        if (!empty($password) || !empty($confirmar_password)) {
            if ($password !== $confirmar_password)
                return response()->json(['success' => false, 'message' => 'Las contraseñas no coinciden.'], 400);
            if (strlen($password) < 6 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password))
                return response()->json(['success' => false, 'message' => 'Contraseña inválida.'], 400);
            $datos['password_hash'] = Hash::make($password);
        }

        $cliente->update($datos);

        return response()->json(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
    }

    // PATCH /api/clientes/{documento}/nombre
    public function updateNombre(Request $request, $documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $nombre = trim($request->input('nombre', ''));

        if (!$nombre)
            return response()->json(['success' => false, 'message' => 'El nombre no puede estar vacío.'], 400);
        if (strlen($nombre) < 2)
            return response()->json(['success' => false, 'message' => 'El nombre debe tener al menos 2 caracteres.'], 400);
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30)
            return response()->json(['success' => false, 'message' => 'Nombre inválido (solo letras, máx 30 caracteres).'], 400);

        $cliente = Cliente::find($documento);
        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado.'], 404);

        $cliente->update(['nombre' => $nombre]);

        return response()->json(['success' => true, 'message' => 'Nombre actualizado correctamente.', 'nombre' => $nombre]);
    }

    // DELETE /api/clientes/{documento}  → soft delete
    public function deactivate($documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $cliente = Cliente::find($documento);

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'El cliente no existe.'], 404);

        if ($cliente->estado_inicio_sesion === 'inactivo')
            return response()->json(['success' => false, 'message' => 'El cliente ya está desactivado.'], 409);

        $cliente->update(['estado_inicio_sesion' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Cliente desactivado exitosamente.']);
    }
}