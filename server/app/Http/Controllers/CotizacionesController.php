<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Invernadero;
use Illuminate\Http\Request;
use App\Http\Requests\Cotizaciones\StoreCotizacionRequest;
use App\Http\Requests\Cotizaciones\UpdateCotizacionRequest;

class CotizacionesController extends Controller
{
    // GET /api/cotizaciones
    // GET /api/cotizaciones?selects=1
    // GET /api/cotizaciones?documento=X
    public function index(Request $request)
    {
        if ($request->has('selects')) {
            return response()->json([
                'success'      => true,
                'clientes'     => Cliente::where('estado_inicio_sesion', 'activo')->get(['documento', 'nombre']),
                'invernaderos' => Invernadero::where('estado', 'activo')->get(['id_invernadero', 'nombre', 'precio_m2']),
            ]);
        }

        if ($request->has('documento')) {
            $documento = $request->query('documento');
            if (!is_numeric($documento))
                return response()->json(['success' => false, 'mensaje' => 'Documento inválido.'], 400);

            $data = Cotizacion::with('invernadero')->where('cliente_id', $documento)->get();
            return response()->json(['success' => true, 'data' => $data]);
        }

        $data = Cotizacion::with(['cliente', 'invernadero'])->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    // POST /api/cotizaciones
    public function store(StoreCotizacionRequest $request)
    {
        Cotizacion::create($request->validated());

        return response()->json(['success' => true, 'message' => 'Cotización registrada exitosamente.'], 201);
    }

    // PUT /api/cotizaciones/{id}
    public function update(UpdateCotizacionRequest $request, $id)
    {
        $cotizacion = Cotizacion::find($id);
        if (!$cotizacion)
            return response()->json(['success' => false, 'message' => 'La cotización no existe.'], 404);

        $cotizacion->update($request->validated());

        return response()->json(['success' => true, 'message' => 'Cotización actualizada exitosamente.']);
    }

    // DELETE /api/cotizaciones/{id} → soft delete (rechazar)
    public function destroy($id)
    {
        $cotizacion = Cotizacion::find($id);
        if (!$cotizacion)
            return response()->json(['success' => false, 'message' => 'La cotización no existe.'], 404);

        if ($cotizacion->estado === 'rechazada')
            return response()->json(['success' => false, 'message' => 'La cotización ya está rechazada.'], 409);

        $cotizacion->update(['estado' => 'rechazada']);

        return response()->json(['success' => true, 'message' => 'Cotización rechazada exitosamente.']);
    }
}