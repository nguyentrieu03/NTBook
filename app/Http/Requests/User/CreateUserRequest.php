<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Support\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array 
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'status' => ['sometimes', 'string', Rule::enum(UserStatusEnum::class)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Name must be less than 255 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.letters' => 'Password must contain at least one letter',
            'password.numbers' => 'Password must contain at least one number',
            'password.symbols' => 'Password must contain at least one symbol',
            'status.sometimes' => 'Status is required',
            'status.string' => 'Status must be a string',
            'status.enum' => 'Status must be a valid status',
            'phone.nullable' => 'Phone is not required',
            'phone.string' => 'Phone must be a string',
            'phone.max' => 'Phone must be less than 20 characters',
        ]
    }
}