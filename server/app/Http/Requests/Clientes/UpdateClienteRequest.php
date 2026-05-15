<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documento = $this->route('documento');

        return [
            'id_tipo_de_usuario' => ['required', 'integer', 'min:1'],
            'nombre'             => ['required', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', 'max:30'],
            'correo'             => ['required', 'email', 'max:50', "unique:clientes,correo,{$documento},documento"],
            'estado'             => ['required', 'in:activo,inactivo'],
            'password'           => ['nullable', 'string', 'min:6', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).+$/', 'same:confirmar_password'],
            'confirmar_password' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_tipo_de_usuario.required' => 'Todos los campos obligatorios deben estar llenos.',
            'id_tipo_de_usuario.min'      => 'Tipo de usuario inválido.',
            'nombre.required'             => 'Todos los campos obligatorios deben estar llenos.',
            'nombre.regex'                => 'Nombre inválido.',
            'nombre.max'                  => 'Nombre inválido.',
            'correo.required'             => 'Todos los campos obligatorios deben estar llenos.',
            'correo.email'                => 'Correo inválido.',
            'correo.max'                  => 'Correo inválido.',
            'correo.unique'               => 'El correo ya está registrado en otro cliente.',
            'estado.required'             => 'Todos los campos obligatorios deben estar llenos.',
            'estado.in'                   => 'Estado inválido.',
            'password.min'                => 'Contraseña inválida.',
            'password.regex'              => 'Contraseña inválida.',
            'password.same'               => 'Las contraseñas no coinciden.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $firstError = $validator->errors()->first();
        $status     = str_contains($firstError, 'ya está registrado') ? 409 : 400;

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstError,
            ], $status)
        );
    }
}