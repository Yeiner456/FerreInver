<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CambiarPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo'          => ['required', 'email'],
            'codigo'          => ['required', 'string', 'digits:6'],
            'nueva_password'  => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required'         => 'El correo es obligatorio.',
            'correo.email'            => 'El correo no es válido.',
            'codigo.required'         => 'El código es obligatorio.',
            'codigo.digits'           => 'El código debe tener exactamente 6 dígitos.',
            'nueva_password.required' => 'La nueva contraseña es obligatoria.',
            'nueva_password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
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