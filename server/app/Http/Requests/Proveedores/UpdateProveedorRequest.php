<?php

namespace App\Http\Requests\Proveedores;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nit = $this->route('nit');

        return [
            'correo'   => ['required', 'email', 'max:80', "unique:proveedores,correo,{$nit},nit_proveedor"],
            'direccion' => ['required', 'string', 'max:80'],
            'telefono' => ['required', 'string', 'regex:/^[0-9\s\-\(\)\+]+$/', 'max:20'],
            'estado'   => ['required', 'in:activo,inactivo'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $telefono = $this->input('telefono', '');
            if ($telefono && strlen(preg_replace('/[^0-9]/', '', $telefono)) < 7) {
                $v->errors()->add('telefono', 'El teléfono debe tener al menos 7 dígitos.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'correo.required'    => 'Todos los campos son obligatorios.',
            'correo.email'       => 'El correo no es válido o excede 80 caracteres.',
            'correo.max'         => 'El correo no es válido o excede 80 caracteres.',
            'correo.unique'      => 'El correo ya está registrado en otro proveedor.',
            'direccion.required' => 'Todos los campos son obligatorios.',
            'direccion.max'      => 'La dirección debe tener entre 1 y 80 caracteres.',
            'telefono.required'  => 'Todos los campos son obligatorios.',
            'telefono.regex'     => 'Teléfono inválido.',
            'telefono.max'       => 'Teléfono inválido.',
            'estado.required'    => 'Todos los campos son obligatorios.',
            'estado.in'          => 'Estado inválido.',
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