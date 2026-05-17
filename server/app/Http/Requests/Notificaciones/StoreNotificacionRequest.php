<?php

namespace App\Http\Requests\Notificaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documento_cliente' => ['required', 'numeric', 'gt:0', 'exists:clientes,documento'],
            'titulo'            => ['required', 'string', 'max:100'],
            'mensaje'           => ['required', 'string'],
            'tipo'              => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento_cliente.required' => "El campo 'documento_cliente' es obligatorio.",
            'documento_cliente.numeric'  => 'Documento del cliente inválido.',
            'documento_cliente.gt'       => 'Documento del cliente inválido.',
            'documento_cliente.exists'   => 'El cliente no existe.',
            'titulo.required'            => "El campo 'titulo' es obligatorio.",
            'titulo.string'              => 'El título no es válido.',
            'titulo.max'                 => 'El título no puede superar los 100 caracteres.',
            'mensaje.required'           => "El campo 'mensaje' es obligatorio.",
            'mensaje.string'             => 'El mensaje no es válido.',
            'tipo.required'              => "El campo 'tipo' es obligatorio.",
            'tipo.string'                => 'El tipo no es válido.',
            'tipo.max'                   => 'El tipo no puede superar los 50 caracteres.',
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