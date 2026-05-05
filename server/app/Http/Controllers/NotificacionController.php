<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    // GET /api/notificaciones
    public function index()
    {
        $notificaciones = Notificacion::with('cliente')->orderBy('fecha', 'desc')->get();
        return response()->json(['success' => true, 'data' => $notificaciones]);
    }

    // GET /api/notificaciones/cliente/{documento}
    public function porCliente($documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $notificaciones = Notificacion::where('documento_cliente', $documento)
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $notificaciones]);
    }

    // POST /api/notificaciones
    public function create(Request $request)
    {
        $documento_cliente = trim($request->input('documento_cliente', ''));
        $titulo            = trim($request->input('titulo', ''));
        $mensaje           = trim($request->input('mensaje', ''));
        $tipo              = trim($request->input('tipo', ''));

        foreach (compact('documento_cliente', 'titulo', 'mensaje', 'tipo') as $campo => $valor) {
            if (empty($valor))
                return response()->json(['success' => false, 'message' => "El campo '$campo' es obligatorio."], 400);
        }

        if (!is_numeric($documento_cliente) || $documento_cliente <= 0)
            return response()->json(['success' => false, 'message' => 'Documento del cliente inválido.'], 400);

        if (strlen($titulo) > 100)
            return response()->json(['success' => false, 'message' => 'El título no puede superar los 100 caracteres.'], 400);

        if (strlen($tipo) > 50)
            return response()->json(['success' => false, 'message' => 'El tipo no puede superar los 50 caracteres.'], 400);

        Notificacion::create([
            'documento_cliente' => $documento_cliente,
            'titulo'            => $titulo,
            'mensaje'           => $mensaje,
            'tipo'              => $tipo,
        ]);

        return response()->json(['success' => true, 'message' => 'Notificación creada exitosamente.'], 201);
    }

    // PUT /api/notificaciones/{id}
    public function update(Request $request, $id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $titulo  = trim($request->input('titulo', ''));
        $mensaje = trim($request->input('mensaje', ''));
        $tipo    = trim($request->input('tipo', ''));

        if (!$titulo || !$mensaje || !$tipo)
            return response()->json(['success' => false, 'message' => 'Todos los campos obligatorios deben estar llenos.'], 400);

        if (strlen($titulo) > 100)
            return response()->json(['success' => false, 'message' => 'El título no puede superar los 100 caracteres.'], 400);

        if (strlen($tipo) > 50)
            return response()->json(['success' => false, 'message' => 'El tipo no puede superar los 50 caracteres.'], 400);

        $notificacion = Notificacion::find($id);

        if (!$notificacion)
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada.'], 404);

        $notificacion->update([
            'titulo'  => $titulo,
            'mensaje' => $mensaje,
            'tipo'    => $tipo,
        ]);

        return response()->json(['success' => true, 'message' => 'Notificación actualizada exitosamente.']);
    }

    // PATCH /api/notificaciones/{id}/marcar-leida
    public function marcarLeida($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $notificacion = Notificacion::find($id);

        if (!$notificacion)
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada.'], 404);

        if ($notificacion->leido)
            return response()->json(['success' => false, 'message' => 'La notificación ya estaba marcada como leída.'], 409);

        $notificacion->update(['leido' => 1]);

        return response()->json(['success' => true, 'message' => 'Notificación marcada como leída.']);
    }

    // PATCH /api/notificaciones/cliente/{documento}/marcar-todas
    public function marcarTodasLeidas($documento)
    {
        if (!is_numeric($documento))
            return response()->json(['success' => false, 'message' => 'Documento inválido.'], 400);

        $actualizadas = Notificacion::where('documento_cliente', $documento)
            ->where('leido', 0)
            ->update(['leido' => 1]);

        if ($actualizadas === 0)
            return response()->json(['success' => false, 'message' => 'No hay notificaciones pendientes por leer.'], 409);

        return response()->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas.']);
    }

    // DELETE /api/notificaciones/{id}
    public function destroy($id)
    {
        if (!is_numeric($id))
            return response()->json(['success' => false, 'message' => 'ID inválido.'], 400);

        $notificacion = Notificacion::find($id);

        if (!$notificacion)
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada.'], 404);

        $notificacion->delete();

        return response()->json(['success' => true, 'message' => 'Notificación eliminada correctamente.']);
    }
}