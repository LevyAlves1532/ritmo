<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|max:16',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.required' => 'O campo e-mail deve ser preenchido!',
            'password.required' => 'O campo senha deve ser preenchido!',
            'email.email' => 'O campo e-mail é inválido!',
            'password.min' => 'O campo senha deve ter no mínimo 8 caracteres!',
            'email.exists' => 'Este e-mail não tem conta!',
            'password.max' => 'O campo senha deve ter no máximo 16 caracteres!',
        ];
    }
}
