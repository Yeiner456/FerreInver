<?php

namespace App\Http\Requests\Compras;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad'     => ['required', 'numeric', 'min:1'],
            'descripcion'  => ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/', 'max:150'],
            'id_producto'  => ['required', 'exists:productos,id_producto'],
            'id_proveedor' => ['required', 'exists:proveedores,nit_proveedor'],
        ];
    }

    public function messages(): array
    {
        return [
            'cantidad.required'     => 'Todos los campos son obligatorios.',
            'cantidad.numeric'      => 'La cantidad debe ser un número mayor a 0.',
            'cantidad.min'          => 'La cantidad debe ser un número mayor a 0.',
            'descripcion.required'  => 'Todos los campos son obligatorios.',
            'descripcion.regex'     => 'La descripción solo puede contener letras, números y espacios.',
            'descripcion.max'       => 'La descripción no puede exceder 150 caracteres.',
            'id_producto.required'  => 'Todos los campos son obligatorios.',
            'id_producto.exists'    => 'El producto seleccionado no existe.',
            'id_proveedor.required' => 'Todos los campos son obligatorios.',
            'id_proveedor.exists'   => 'El proveedor seleccionado no existe.',
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