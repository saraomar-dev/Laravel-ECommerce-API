<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'payment_method' => [
                'required',
                Rule::in([
                    'cash_on_delivery',
                    'card',
                ]),
            ],

            'shipping_address' => [
                'required',
                'array',
            ],

            'shipping_address.name' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_address.phone' => [
                'required',
                'string',
                'max:30',
            ],

            'shipping_address.city' => [
                'required',
                'string',
                'max:100',
            ],

            'shipping_address.area' => [
                'required',
                'string',
                'max:100',
            ],

            'shipping_address.street' => [
                'required',
                'string',
                'max:255',
            ],

            'shipping_address.building' => [
                'required',
                'string',
                'max:50',
            ],

            'shipping_address.floor' => [
                'nullable',
                'string',
                'max:50',
            ],

            'shipping_address.apartment' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }
}
