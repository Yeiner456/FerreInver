<?php

namespace App\Http\Requests\ProductosPedidos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_producto' => ['required', 'numeric', 'exists:productos,id_producto'],
            'id_pedido'   => ['required', 'numeric', 'exists:pedidos,id_pedido'],
            'descripcion' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s,.\-]+$/'],
            'cantidad'    => ['required', 'numeric', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_producto.required' => 'Todos los campos son obligatorios.',
            'id_producto.numeric'  => 'El ID de producto no es válido.',
            'id_producto.exists'   => 'El producto no existe.',
            'id_pedido.required'   => 'Todos los campos son obligatorios.',
            'id_pedido.numeric'    => 'El ID de pedido no es válido.',
            'id_pedido.exists'     => 'El pedido no existe.',
            'descripcion.required' => 'Todos los campos son obligatorios.',
            'descripcion.string'   => 'La descripción no es válida.',
            'descripcion.max'      => 'La descripción no puede exceder 100 caracteres.',
            'descripcion.regex'    => 'La descripción contiene caracteres no permitidos.',
            'cantidad.required'    => 'Todos los campos son obligatorios.',
            'cantidad.numeric'     => 'La cantidad debe ser entre 1 y 1000.',
            'cantidad.min'         => 'La cantidad debe ser entre 1 y 1000.',
            'cantidad.max'         => 'La cantidad debe ser entre 1 y 1000.',
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