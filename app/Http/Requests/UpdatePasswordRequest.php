<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Vérification du mot de passe actuel indépendante du guard (token Sanctum).
            'current_password' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Hash::check($value, $this->user()->password)) {
                        $fail('Le mot de passe actuel est incorrect.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation ne correspond pas.',
            'password.different' => 'Le nouveau mot de passe doit différer de l\'actuel.',
        ];
    }
}
