<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Http\Requests\Notificaciones\StoreNotificacionRequest;
use App\Http\Requests\Notificaciones\UpdateNotificacionRequest;

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
    public function create(StoreNotificacionRequest $request)
    {
        Notificacion::create($request->validated());

        return response()->json(['success' => true, 'message' => 'Notificación creada exitosamente.'], 201);
    }

    // PUT /api/notificaciones/{id}
    public function update(UpdateNotificacionRequest $request, $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion)
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada.'], 404);

        $notificacion->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Notificación actualizada exitosamente.']);
    }

    // PATCH /api/notificaciones/{id}/marcar-leida
    public function marcarLeida($id)
    {
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
        $notificacion = Notificacion::find($id);
        if (!$notificacion)
            return response()->json(['success' => false, 'message' => 'Notificación no encontrada.'], 404);

        $notificacion->delete();

        return response()->json(['success' => true, 'message' => 'Notificación eliminada correctamente.']);
    }
}