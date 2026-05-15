<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documento' => ['required', 'numeric', 'min:100000', 'max:999999999999999'],
            'nombre'    => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/u'],
            'correo'    => ['required', 'email', 'unique:clientes,correo'],
            'password'  => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.required' => 'El documento es obligatorio.',
            'documento.numeric'  => 'El documento debe ser numérico.',
            'documento.min'      => 'El documento debe tener entre 6 y 15 dígitos.',
            'documento.max'      => 'El documento debe tener entre 6 y 15 dígitos.',
            'nombre.required'    => 'El nombre es obligatorio.',
            'nombre.regex'       => 'El nombre solo puede contener letras y debe tener al menos 3 caracteres.',
            'correo.required'    => 'El correo es obligatorio.',
            'correo.email'       => 'El correo electrónico no es válido.',
            'correo.unique'      => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
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