<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\TipoUsuario;
use App\Http\Requests\Clientes\CreateClienteRequest;
use App\Http\Requests\Clientes\UpdateClienteRequest;
use App\Http\Requests\Clientes\UpdateNombreClienteRequest;
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
    public function create(CreateClienteRequest $request)
    {
        if (Cliente::find($request->documento))
            return response()->json(['success' => false, 'message' => 'El documento ya está registrado.'], 409);

        Cliente::create([
            'documento'            => $request->documento,
            'id_tipo_de_usuario'   => $request->id_tipo_de_usuario,
            'password_hash'        => Hash::make($request->password),
            'nombre'               => $request->nombre,
            'correo'               => $request->correo,
            'estado_inicio_sesion' => $request->estado,
        ]);

        return response()->json(['success' => true, 'message' => 'Cliente registrado exitosamente.'], 201);
    }

    // PUT /api/clientes/{documento}
    public function update(UpdateClienteRequest $request, $documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $cliente = Cliente::find($documento);

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado.'], 404);

        $datos = [
            'id_tipo_de_usuario'   => $request->id_tipo_de_usuario,
            'nombre'               => $request->nombre,
            'correo'               => $request->correo,
            'estado_inicio_sesion' => $request->estado,
        ];

        if ($request->filled('password')) {
            $datos['password_hash'] = Hash::make($request->password);
        }

        $cliente->update($datos);

        return response()->json(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
    }

    // PATCH /api/clientes/{documento}/nombre
    public function updateNombre(UpdateNombreClienteRequest $request, $documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $cliente = Cliente::find($documento);

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado.'], 404);

        $cliente->update(['nombre' => $request->nombre]);

        return response()->json(['success' => true, 'message' => 'Nombre actualizado correctamente.', 'nombre' => $request->nombre]);
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