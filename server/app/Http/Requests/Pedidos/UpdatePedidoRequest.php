<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_cliente'    => ['required', 'numeric', 'gt:0', 'exists:clientes,documento'],
            'medio_pago'    => ['required', 'string', 'in:Efectivo,Tarjeta Débito,Tarjeta Crédito,Transferencia,PSE,Nequi,Daviplata'],
            'estado_pedido' => ['required', 'string', 'in:pendiente,recibido,listo para recibir,cancelado'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_cliente.required'    => 'Todos los campos son obligatorios.',
            'id_cliente.numeric'     => 'ID de cliente inválido.',
            'id_cliente.gt'          => 'ID de cliente inválido.',
            'id_cliente.exists'      => 'El cliente no existe.',
            'medio_pago.required'    => 'Todos los campos son obligatorios.',
            'medio_pago.in'          => 'Medio de pago inválido.',
            'estado_pedido.required' => 'Todos los campos son obligatorios.',
            'estado_pedido.in'       => 'Estado del pedido inválido.',
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