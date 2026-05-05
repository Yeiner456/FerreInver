<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Invernadero;
use Illuminate\Http\Request;

class CotizacionesController extends Controller
{
    private function validarCampos(array $b): ?string
    {
        $requeridos = ['cliente_id', 'invernadero_id', 'largo', 'ancho', 'metros_cuadrados', 'valor_m2', 'total', 'estado'];

        foreach ($requeridos as $campo) {
            if (!isset($b[$campo]) || $b[$campo] === '' || $b[$campo] === null)
                return 'Todos los campos son obligatorios.';
        }

        foreach (['largo', 'ancho', 'metros_cuadrados', 'valor_m2', 'total'] as $campo) {
            if (!is_numeric($b[$campo]) || (float) $b[$campo] <= 0)
                return "El campo $campo debe ser un número mayor a 0.";
        }

        if (abs(round((float) $b['largo'] * (float) $b['ancho'], 2) - round((float) $b['metros_cuadrados'], 2)) > 0.01)
            return 'Los metros cuadrados no coinciden con largo × ancho.';

        if (!in_array($b['estado'], ['pendiente', 'aprobada', 'rechazada']))
            return 'Estado inválido.';

        return null;
    }

    private function validarRelaciones(array $b): ?string
    {
        if (!Cliente::find($b['cliente_id']))
            return 'El cliente no existe.';

        $inv = Invernadero::find($b['invernadero_id']);
        if (!$inv)
            return 'El invernadero no existe.';

        if (abs(round((float) $inv->precio_m2, 2) - round((float) $b['valor_m2'], 2)) > 0.01)
            return 'El valor m² no coincide con el precio del invernadero.';

        if (abs(round((float) $b['metros_cuadrados'] * (float) $b['valor_m2'], 2) - round((float) $b['total'], 2)) > 0.01)
            return 'El total no coincide con metros cuadrados × valor m².';

        return null;
    }

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
    public function store(Request $request)
    {
        $b = $request->all();

        $error = $this->validarCampos($b);
        if ($error)
            return response()->json(['success' => false, 'message' => $error], 400);

        $error = $this->validarRelaciones($b);
        if ($error)
            return response()->json(['success' => false, 'message' => $error], 400);

        Cotizacion::create($b);

        return response()->json(['success' => true, 'message' => 'Cotización registrada exitosamente.'], 201);
    }

    // PUT /api/cotizaciones/{id}
    public function update(Request $request, $id)
    {
        $cotizacion = Cotizacion::find($id);
        if (!$cotizacion)
            return response()->json(['success' => false, 'message' => 'La cotización no existe.'], 404);

        $b = $request->all();

        $error = $this->validarCampos($b);
        if ($error)
            return response()->json(['success' => false, 'message' => $error], 400);

        $error = $this->validarRelaciones($b);
        if ($error)
            return response()->json(['success' => false, 'message' => $error], 400);

        $cotizacion->update($b);

        return response()->json(['success' => true, 'message' => 'Cotización actualizada exitosamente.']);
    }

    // DELETE /api/cotizaciones/{id}  → soft delete (rechazar)
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