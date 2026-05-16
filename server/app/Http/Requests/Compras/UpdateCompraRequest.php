<?php

namespace App\Http\Requests\Compras;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad'    => ['required', 'numeric', 'min:1'],
            'descripcion' => ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'cantidad.required'    => 'Cantidad y descripción son obligatorios.',
            'cantidad.numeric'     => 'La cantidad debe ser un número mayor a 0.',
            'cantidad.min'         => 'La cantidad debe ser un número mayor a 0.',
            'descripcion.required' => 'Cantidad y descripción son obligatorios.',
            'descripcion.regex'    => 'La descripción solo puede contener letras, números y espacios.',
            'descripcion.max'      => 'La descripción no puede exceder 150 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400)
        );
    }
}