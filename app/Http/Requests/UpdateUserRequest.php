<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', 'in:agent,accounts,issuance,manager,admin,operations'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'status'    => ['nullable', 'in:active,inactive,suspended'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Name is required.',
            'email.required' => 'Email address is required.',
            'email.unique'   => 'This email is already taken.',
            'password.min'   => 'Password must be at least 8 characters.',
            'role.required'  => 'Please select a role.',
            'role.in'        => 'Invalid role selected.',
        ];
    }
}
