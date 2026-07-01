<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sanctum
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Personalizar los mensajes de error en español
     */
    public function messages(): array
    {
        return [
            'items.required' => 'El carrito de compra no puede estar vacío.',
            'items.array' => 'Los ítems del pedido deben enviarse en formato de lista.',
            'items.*.product_id.required' => 'El ID del producto es obligatorio.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe en nuestro catálogo.',
            'items.*.quantity.required' => 'La cantidad es obligatoria.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad mínima para generar un pedido es de 1 unidad.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Los datos proporcionados no son válidos.',
            'errors'  => $validator->errors()
        ], 422));
    }
}