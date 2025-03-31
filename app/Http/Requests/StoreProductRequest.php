<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|max:255',
            'prijs' => 'required|numeric|min:0',
            'description' => 'required'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required',
            'name.max' => 'The name cannot be longer than 255 characters',
            'prijs.required' => 'The price is required',
            'prijs.numeric' => 'The price must be a number',
            'prijs.min' => 'The price cannot be negative',
            'description.required' => 'The description is required'
        ];
    }
} 