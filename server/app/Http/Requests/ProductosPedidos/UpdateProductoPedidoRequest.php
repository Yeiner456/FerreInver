<?php

namespace App\Http\Requests\ProductosPedidos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s,.\-]+$/'],
            'cantidad'    => ['required', 'numeric', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion.required' => 'Descripción y cantidad son obligatorios.',
            'descripcion.string'   => 'La descripción no es válida.',
            'descripcion.max'      => 'La descripción no puede exceder 100 caracteres.',
            'descripcion.regex'    => 'La descripción contiene caracteres no permitidos.',
            'cantidad.required'    => 'Descripción y cantidad son obligatorios.',
            'cantidad.numeric'     => 'La cantidad debe ser entre 1 y 1000.',
            'cantidad.min'         => 'La cantidad debe ser entre 1 y 1000.',
            'cantidad.max'         => 'La cantidad debe ser entre 1 y 1000.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $firstError = $validator->errors()->first();

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstError,
            ], 400)
        );
    }
}