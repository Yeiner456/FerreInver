<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\EnviarCodigoRequest;
use App\Http\Requests\Auth\VerificarCodigoRequest;
use App\Http\Requests\Auth\CambiarPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    // POST /api/auth/login
    public function login(LoginRequest $request)
    {
        $cliente = Cliente::with('tipoUsuario')
            ->where('correo', $request->correo)
            ->first();

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Correo o contraseña incorrectos.'], 401);

        if ($cliente->estado_inicio_sesion === 'inactivo')
            return response()->json(['success' => false, 'message' => 'Tu cuenta está inactiva. Contacta al administrador.'], 403);

        if (!Hash::check($request->password, $cliente->password_hash))
            return response()->json(['success' => false, 'message' => 'Correo o contraseña incorrectos.'], 401);

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente.',
            'usuario' => [
                'documento'            => $cliente->documento,
                'nombre'               => $cliente->nombre,
                'correo'               => $cliente->correo,
                'tipo_usuario'         => $cliente->tipoUsuario->nombre ?? null,
                'estado_inicio_sesion' => $cliente->estado_inicio_sesion,
            ]
        ]);
    }

    // POST /api/auth/register
    public function register(RegisterRequest $request)
    {
        if (Cliente::find($request->documento))
            return response()->json(['success' => false, 'message' => 'Este documento ya está registrado.'], 409);

        Cliente::create([
            'documento'     => (int) $request->documento,
            'nombre'        => $request->nombre,
            'correo'        => $request->correo,
            'password_hash' => Hash::make($request->password),
        ]);

        return response()->json(['success' => true, 'message' => 'Cuenta creada correctamente.'], 201);
    }

    // POST /api/auth/enviar-codigo
    public function enviarCodigo(EnviarCodigoRequest $request)
    {
        $cliente = Cliente::where('correo', $request->correo)->first();

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'No existe una cuenta con ese correo.'], 404);

        $codigo     = strval(rand(100000, 999999));
        $expiracion = Carbon::now()->addMinutes(15);

        $cliente->update([
            'codigo_recuperacion' => $codigo,
            'codigo_expiracion'   => $expiracion,
        ]);

        try {
            Mail::send([], [], function ($mail) use ($request, $codigo) {
                $mail->to($request->correo)
                    ->subject('Código de recuperación - Ferreinver')
                    ->html("
                        <div style='font-family: DM Sans, sans-serif; max-width: 400px; margin: auto; padding: 30px; border-radius: 12px; border: 1px solid #e0e0e0;'>
                            <h2 style='color: #00185a;'>Recuperar contraseña</h2>
                            <p>Tu código de verificación es:</p>
                            <h1 style='letter-spacing: 8px; color: #00185a; font-size: 40px;'>$codigo</h1>
                            <p style='color: #999; font-size: 13px;'>Este código expira en 15 minutos.</p>
                        </div>
                    ");
            });

            return response()->json(['success' => true, 'message' => 'Código enviado a tu correo.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
        }
    }

    // POST /api/auth/verificar-codigo
    public function verificarCodigo(VerificarCodigoRequest $request)
    {
        $cliente = Cliente::where('correo', $request->correo)->first();

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.'], 404);

        if ($cliente->codigo_recuperacion !== $request->codigo)
            return response()->json(['success' => false, 'message' => 'Código incorrecto.'], 400);

        if (Carbon::now()->gt($cliente->codigo_expiracion))
            return response()->json(['success' => false, 'message' => 'El código ha expirado. Solicita uno nuevo.'], 400);

        return response()->json(['success' => true, 'message' => 'Código verificado correctamente.']);
    }

    // POST /api/auth/cambiar-password
    public function cambiarPassword(CambiarPasswordRequest $request)
    {
        $cliente = Cliente::where('correo', $request->correo)->first();

        if (!$cliente || $cliente->codigo_recuperacion !== $request->codigo)
            return response()->json(['success' => false, 'message' => 'Código inválido.'], 400);

        if (Carbon::now()->gt($cliente->codigo_expiracion))
            return response()->json(['success' => false, 'message' => 'El código ha expirado.'], 400);

        $cliente->update([
            'password_hash'       => Hash::make($request->nueva_password),
            'codigo_recuperacion' => null,
            'codigo_expiracion'   => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    }
}