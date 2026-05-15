<?php

namespace App\Http\Requests\Notificaciones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateNotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo'  => ['required', 'string', 'max:100'],
            'mensaje' => ['required', 'string'],
            'tipo'    => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required'  => 'Todos los campos obligatorios deben estar llenos.',
            'titulo.string'    => 'El título no es válido.',
            'titulo.max'       => 'El título no puede superar los 100 caracteres.',
            'mensaje.required' => 'Todos los campos obligatorios deben estar llenos.',
            'mensaje.string'   => 'El mensaje no es válido.',
            'tipo.required'    => 'Todos los campos obligatorios deben estar llenos.',
            'tipo.string'      => 'El tipo no es válido.',
            'tipo.max'         => 'El tipo no puede superar los 50 caracteres.',
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