<?php

namespace App\Http\Requests\Invernaderos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateInvernaderoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'nombre'      => ['required', 'string', 'max:50', Rule::unique('invernaderos', 'nombre')->ignore($id, 'id_invernadero')],
            'descripcion' => ['nullable', 'string', 'max:150'],
            'precio_m2'   => ['required', 'numeric', 'gt:0', 'lt:9999999999.99'],
            'estado'      => ['required', 'in:activo,inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'      => 'Nombre, precio m² y estado son obligatorios.',
            'nombre.string'        => 'El nombre no es válido.',
            'nombre.max'           => 'El nombre no puede exceder 50 caracteres.',
            'nombre.unique'        => 'Ya existe otro invernadero con ese nombre.',
            'descripcion.string'   => 'La descripción no es válida.',
            'descripcion.max'      => 'La descripción no puede exceder 150 caracteres.',
            'precio_m2.required'   => 'Nombre, precio m² y estado son obligatorios.',
            'precio_m2.numeric'    => 'El precio m² debe ser un número positivo válido.',
            'precio_m2.gt'         => 'El precio m² debe ser un número positivo válido.',
            'precio_m2.lt'         => 'El precio m² debe ser un número positivo válido.',
            'estado.required'      => 'Nombre, precio m² y estado son obligatorios.',
            'estado.in'            => 'Estado inválido.',
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