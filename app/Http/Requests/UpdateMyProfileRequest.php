<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateMyProfileRequest extends FormRequest
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
        'email' => [
            'sometimes',
            'email',
            'max:40',
            Rule::unique('users')->ignore(auth()->id()),
        ],

        'name' => 'sometimes|string|max:40',

        'phone' => 'sometimes|string|max:15',

        'address' => 'sometimes|string|max:40',
    ];
    }
}
