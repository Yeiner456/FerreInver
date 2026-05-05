<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController
{
    // POST /api/auth/login
    public function login(Request $request)
    {
        $correo   = trim($request->input('correo', ''));
        $password = trim($request->input('password', ''));

        if (empty($correo) || empty($password))
            return response()->json(['success' => false, 'message' => 'Correo y contraseña son obligatorios.'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))
            return response()->json(['success' => false, 'message' => 'El correo no es válido.'], 400);

        $cliente = Cliente::with('tipoUsuario')->where('correo', $correo)->first();

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Correo o contraseña incorrectos.'], 401);

        if ($cliente->estado_inicio_sesion === 'inactivo')
            return response()->json(['success' => false, 'message' => 'Tu cuenta está inactiva. Contacta al administrador.'], 403);

        if (!Hash::check($password, $cliente->password_hash))
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
    public function register(Request $request)
    {
        $documento = trim($request->input('documento', ''));
        $nombre    = trim($request->input('nombre', ''));
        $correo    = trim($request->input('correo', ''));
        $password  = trim($request->input('password', ''));

        if (empty($documento) || empty($nombre) || empty($correo) || empty($password))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (!is_numeric($documento) || $documento < 100000 || $documento > 999999999999999)
            return response()->json(['success' => false, 'message' => 'El documento debe tener entre 6 y 15 dígitos.'], 400);

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/u", $nombre))
            return response()->json(['success' => false, 'message' => 'El nombre solo puede contener letras y debe tener al menos 3 caracteres.'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))
            return response()->json(['success' => false, 'message' => 'El correo electrónico no es válido.'], 400);

        if (strlen($password) < 8)
            return response()->json(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'], 400);

        if (Cliente::where('correo', $correo)->exists())
            return response()->json(['success' => false, 'message' => 'Este correo ya está registrado.'], 409);

        if (Cliente::find($documento))
            return response()->json(['success' => false, 'message' => 'Este documento ya está registrado.'], 409);

        Cliente::create([
            'documento'    => (int) $documento,
            'nombre'       => $nombre,
            'correo'       => $correo,
            'password_hash' => Hash::make($password),
        ]);

        return response()->json(['success' => true, 'message' => 'Cuenta creada correctamente.'], 201);
    }

    // POST /api/auth/enviar-codigo
    public function enviarCodigo(Request $request)
    {
        $correo = trim($request->input('correo', ''));

        if (empty($correo))
            return response()->json(['success' => false, 'message' => 'El correo es obligatorio.'], 400);

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))
            return response()->json(['success' => false, 'message' => 'El correo no es válido.'], 400);

        $cliente = Cliente::where('correo', $correo)->first();

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'No existe una cuenta con ese correo.'], 404);

        $codigo     = strval(rand(100000, 999999));
        $expiracion = Carbon::now()->addMinutes(15);

        $cliente->update([
            'codigo_recuperacion' => $codigo,
            'codigo_expiracion'   => $expiracion,
        ]);

        try {
            Mail::send([], [], function ($mail) use ($correo, $codigo) {
                $mail->to($correo)
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
    public function verificarCodigo(Request $request)
    {
        $correo = trim($request->input('correo', ''));
        $codigo = trim($request->input('codigo', ''));

        if (empty($correo) || empty($codigo))
            return response()->json(['success' => false, 'message' => 'Correo y código son obligatorios.'], 400);

        $cliente = Cliente::where('correo', $correo)->first();

        if (!$cliente)
            return response()->json(['success' => false, 'message' => 'Correo no encontrado.'], 404);

        if ($cliente->codigo_recuperacion !== $codigo)
            return response()->json(['success' => false, 'message' => 'Código incorrecto.'], 400);

        if (Carbon::now()->gt($cliente->codigo_expiracion))
            return response()->json(['success' => false, 'message' => 'El código ha expirado. Solicita uno nuevo.'], 400);

        return response()->json(['success' => true, 'message' => 'Código verificado correctamente.']);
    }

    // POST /api/auth/cambiar-password
    public function cambiarPassword(Request $request)
    {
        $correo          = trim($request->input('correo', ''));
        $codigo          = trim($request->input('codigo', ''));
        $nueva_password  = trim($request->input('nueva_password', ''));

        if (empty($correo) || empty($codigo) || empty($nueva_password))
            return response()->json(['success' => false, 'message' => 'Todos los campos son obligatorios.'], 400);

        if (strlen($nueva_password) < 8)
            return response()->json(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'], 400);

        $cliente = Cliente::where('correo', $correo)->first();

        if (!$cliente || $cliente->codigo_recuperacion !== $codigo)
            return response()->json(['success' => false, 'message' => 'Código inválido.'], 400);

        if (Carbon::now()->gt($cliente->codigo_expiracion))
            return response()->json(['success' => false, 'message' => 'El código ha expirado.'], 400);

        $cliente->update([
            'password_hash'       => Hash::make($nueva_password),
            'codigo_recuperacion' => null,
            'codigo_expiracion'   => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    }
}