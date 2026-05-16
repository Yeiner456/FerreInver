<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documento'          => ['required', 'numeric', 'digits_between:1,11'],
            'id_tipo_de_usuario' => ['required', 'integer', 'min:1', 'exists:tipos_usuarios,id_tipo_de_usuario'],
            'nombre'             => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', 'max:30'],
            'correo'             => ['required', 'email', 'max:50', 'unique:clientes,correo'],
            'password'           => ['required', 'string', 'min:6', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/', 'same:confirmar_password'],
            'confirmar_password' => ['required', 'string'],
            'estado'             => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento.required'          => "El campo 'documento' es obligatorio.",
            'documento.numeric'           => 'El documento debe ser numérico.',
            'documento.digits_between'    => 'Documento inválido.',
            'id_tipo_de_usuario.required' => "El campo 'id_tipo_de_usuario' es obligatorio.",
            'id_tipo_de_usuario.min'      => 'Tipo de usuario inválido.',
            'id_tipo_de_usuario.exists'   => 'El tipo de usuario no existe.',
            'nombre.required'             => "El campo 'nombre' es obligatorio.",
            'nombre.regex'                => 'Nombre inválido (solo letras, máx 30 caracteres).',
            'nombre.max'                  => 'Nombre inválido (solo letras, máx 30 caracteres).',
            'correo.required'             => "El campo 'correo' es obligatorio.",
            'correo.email'                => 'Correo inválido.',
            'correo.max'                  => 'Correo inválido.',
            'correo.unique'               => 'El correo ya está registrado.',
            'password.required'           => "El campo 'password' es obligatorio.",
            'password.min'                => 'Contraseña inválida (mín 6 caracteres, letras y números).',
            'password.regex'              => 'Contraseña inválida (mín 6 caracteres, letras y números).',
            'password.same'               => 'Las contraseñas no coinciden.',
            'confirmar_password.required' => "El campo 'confirmar_password' es obligatorio.",
            'estado.required'             => "El campo 'estado' es obligatorio.",
            'estado.in'                   => 'Estado inválido.',
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