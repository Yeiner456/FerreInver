<?php

namespace App\Http\Requests\Cotizaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\Invernadero;

class UpdateCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'       => ['required', 'numeric', 'exists:clientes,documento'],
            'invernadero_id'   => ['required', 'numeric', 'exists:invernaderos,id_invernadero'],
            'largo'            => ['required', 'numeric', 'gt:0'],
            'ancho'            => ['required', 'numeric', 'gt:0'],
            'metros_cuadrados' => ['required', 'numeric', 'gt:0'],
            'valor_m2'         => ['required', 'numeric', 'gt:0'],
            'total'            => ['required', 'numeric', 'gt:0'],
            'estado'           => ['required', 'in:pendiente,aprobada,rechazada'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) return;

            $b   = $this->input();
            $inv = Invernadero::find($b['invernadero_id']);

            if (abs(round((float) $b['largo'] * (float) $b['ancho'], 2) - round((float) $b['metros_cuadrados'], 2)) > 0.01) {
                $validator->errors()->add('metros_cuadrados', 'Los metros cuadrados no coinciden con largo × ancho.');
                return;
            }

            if (abs(round((float) $inv->precio_m2, 2) - round((float) $b['valor_m2'], 2)) > 0.01) {
                $validator->errors()->add('valor_m2', 'El valor m² no coincide con el precio del invernadero.');
                return;
            }

            if (abs(round((float) $b['metros_cuadrados'] * (float) $b['valor_m2'], 2) - round((float) $b['total'], 2)) > 0.01) {
                $validator->errors()->add('total', 'El total no coincide con metros cuadrados × valor m².');
            }
        });
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'       => 'Todos los campos son obligatorios.',
            'cliente_id.numeric'        => 'El cliente seleccionado no es válido.',
            'cliente_id.exists'         => 'El cliente seleccionado no existe.',
            'invernadero_id.required'   => 'Todos los campos son obligatorios.',
            'invernadero_id.numeric'    => 'El invernadero seleccionado no es válido.',
            'invernadero_id.exists'     => 'El invernadero seleccionado no existe.',
            'largo.required'            => 'Todos los campos son obligatorios.',
            'largo.numeric'             => 'El largo debe ser un número mayor a 0.',
            'largo.gt'                  => 'El largo debe ser un número mayor a 0.',
            'ancho.required'            => 'Todos los campos son obligatorios.',
            'ancho.numeric'             => 'El ancho debe ser un número mayor a 0.',
            'ancho.gt'                  => 'El ancho debe ser un número mayor a 0.',
            'metros_cuadrados.required' => 'Todos los campos son obligatorios.',
            'metros_cuadrados.numeric'  => 'Los metros cuadrados deben ser un número mayor a 0.',
            'metros_cuadrados.gt'       => 'Los metros cuadrados deben ser un número mayor a 0.',
            'valor_m2.required'         => 'Todos los campos son obligatorios.',
            'valor_m2.numeric'          => 'El valor m² debe ser un número mayor a 0.',
            'valor_m2.gt'               => 'El valor m² debe ser un número mayor a 0.',
            'total.required'            => 'Todos los campos son obligatorios.',
            'total.numeric'             => 'El total debe ser un número mayor a 0.',
            'total.gt'                  => 'El total debe ser un número mayor a 0.',
            'estado.required'           => 'Todos los campos son obligatorios.',
            'estado.in'                 => 'El estado debe ser: pendiente, aprobada o rechazada.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $firstError = $validator->errors()->first();
        $status     = str_contains($firstError, 'no existe') ? 404 : 400;

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstError,
            ], $status)
        );
    }
}