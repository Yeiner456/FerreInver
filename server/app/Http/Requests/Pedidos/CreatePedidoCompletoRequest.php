<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePedidoCompletoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cliente'          => ['required', 'numeric', 'gt:0', 'exists:clientes,documento'],
            'medio_pago'          => ['required', 'string', 'in:Efectivo,Tarjeta Débito,Tarjeta Crédito,Transferencia,PSE,Nequi,Daviplata'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.id_producto' => ['required', 'numeric', 'exists:productos,id_producto'],
            'items.*.cantidad'    => ['required', 'numeric', 'min:1'],
            'items.*.descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_cliente.required'          => 'Faltan datos obligatorios.',
            'id_cliente.numeric'           => 'ID de cliente inválido.',
            'id_cliente.gt'                => 'ID de cliente inválido.',
            'id_cliente.exists'            => 'El cliente no existe.',
            'medio_pago.required'          => 'Faltan datos obligatorios.',
            'medio_pago.in'                => 'Medio de pago inválido.',
            'items.required'               => 'Faltan datos obligatorios.',
            'items.array'                  => 'El carrito tiene un formato inválido.',
            'items.min'                    => 'El carrito está vacío.',
            'items.*.id_producto.required' => 'Cada ítem debe tener un producto.',
            'items.*.id_producto.numeric'  => 'El ID de producto no es válido.',
            'items.*.id_producto.exists'   => 'Uno o más productos del carrito no existen.',
            'items.*.cantidad.required'    => 'Cada ítem debe tener una cantidad.',
            'items.*.cantidad.numeric'     => 'La cantidad de cada ítem debe ser un número.',
            'items.*.cantidad.min'         => 'La cantidad de cada ítem debe ser mayor a 0.',
            'items.*.descripcion.string'   => 'La descripción del ítem no es válida.',
            'items.*.descripcion.max'      => 'La descripción del ítem no puede exceder 255 caracteres.',
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