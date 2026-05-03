<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $editor = $this->user();

        $targetUserId = $editor->id;
        if ($editor->is_admin && $this->filled('user_id')) {
            $targetUserId = (int) $this->input('user_id');
        }

        $isAdminResettingOtherPassword = $editor->is_admin
            && $this->filled('user_id')
            && (int) $this->input('user_id') !== $editor->id;

        $rules = [
            'user_id' => $editor->is_admin
                ? ['nullable', 'integer', 'exists:users,id']
                : ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($targetUserId)],
            'UsernameDummy' => ['nullable', 'string', 'max:255'],
            'verjaardag' => ['nullable', 'date'],
            'overMij' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];

        if ($isAdminResettingOtherPassword) {
            $rules['password'] = ['nullable', 'string', 'confirmed', Password::defaults()];
        } else {
            $rules['password'] = ['prohibited'];
            $rules['password_confirmation'] = ['prohibited'];
        }

        return $rules;
    }
}
