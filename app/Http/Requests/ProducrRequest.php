<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProducrRequest extends FormRequest
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
            'name' => 'string|max:40|required',
            'description'=>'string|max:250|required',
            'brand' => 'string|max:40|required',
            'status' => 'string|max:40|required|in:active,inactive',
            'sku' => 'string|max:40|required|unique:products,sku',
            'price' => 'required|numeric|min:0|max:999999.99',
            'discount'=>'integer|min:0|sometimes|max:100',
            'stock'=>'integer|min:0|required',
            'subcategory_id'=>'integer|min:0|required|exists:subcategories,id',
            'cover_image'=>'required|mimes:png,jpg,jpeg,gif|max:2048',
        ];
    }
}
