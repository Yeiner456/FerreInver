<?php

namespace App\Http\Requests\Stocks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_producto' => ['required', 'integer', 'min:1', 'exists:productos,id_producto'],
            'cantidad'    => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_producto.required' => 'Todos los campos son obligatorios.',
            'id_producto.integer'  => 'Producto inválido.',
            'id_producto.min'      => 'Producto inválido.',
            'id_producto.exists'   => 'El producto no existe.',
            'cantidad.required'    => 'Todos los campos son obligatorios.',
            'cantidad.integer'     => 'La cantidad debe ser un entero mayor o igual a 0.',
            'cantidad.min'         => 'La cantidad debe ser un entero mayor o igual a 0.',
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