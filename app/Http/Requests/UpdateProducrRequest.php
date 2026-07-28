<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProducrRequest extends FormRequest
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
            'name' => 'string|max:40|sometimes',
            'description'=>'string|max:250|sometimes',
            'brand' => 'string|max:40|sometimes',
            'status' => 'string|max:40|sometimes|in:active,inactive',
            'sku' => 'string|max:40|sometimes|unique:products,sku',
            'price' => 'sometimes|numeric|min:0|max:999999.99',
            'discount'=>'integer|min:0|sometimes|max:100',
            'stock'=>'integer|min:0|sometimes',
            'subcategory_id'=>'integer|min:0|sometimes|exists:subcategories,id',
            'cover_image'=>'sometimes|mimes:png,jpg,jpeg,gif|max:2048',
        ];
    }
}
