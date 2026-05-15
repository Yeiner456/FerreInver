<?php

namespace App\Http\Requests\Productos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:30'],
            'precio'      => ['required', 'numeric', 'gt:0', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:100'],
            'imagen'      => ['nullable', 'file', 'mimes:jpeg,png,webp,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre y el precio son obligatorios.',
            'nombre.string'     => 'El nombre no es válido.',
            'nombre.max'        => 'El nombre debe tener entre 1 y 30 caracteres.',
            'precio.required'   => 'El nombre y el precio son obligatorios.',
            'precio.numeric'    => 'El precio debe ser un número mayor a 0.',
            'precio.gt'         => 'El precio debe ser un número mayor a 0.',
            'precio.integer'    => 'El precio debe ser un número entero.',
            'descripcion.string' => 'La descripción no es válida.',
            'descripcion.max'   => 'La descripción no puede exceder 100 caracteres.',
            'imagen.file'       => 'El archivo de imagen no es válido.',
            'imagen.mimes'      => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.',
            'imagen.max'        => 'La imagen no puede superar 2MB.',
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