<?php

namespace App\Http\Requests\TiposUsuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTipoUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:30', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', 'unique:tipo_de_usuarios,nombre'],
            'estado' => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'Todos los campos son obligatorios.',
            'nombre.max'      => 'El nombre no puede exceder 30 caracteres.',
            'nombre.regex'    => 'El nombre solo puede contener letras y espacios.',
            'nombre.unique'   => 'Ya existe un tipo de usuario con ese nombre.',
            'estado.required' => 'Todos los campos son obligatorios.',
            'estado.in'       => 'El estado no es válido.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $firstError = $validator->errors()->first();
        $status     = str_contains($firstError, 'Ya existe') ? 409 : 400;

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstError,
            ], $status)
        );
    }
}