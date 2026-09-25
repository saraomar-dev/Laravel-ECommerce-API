<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartitemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'cart_id' => 'sometimes|integer|exists:carts,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1|required',
            //'price' => 'required|numeric|min:0|max:999999.99',
        ];
    }
}
