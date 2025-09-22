<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|min:3|max:25',
            'email' => 'required|email|unique:users,email',
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
            'name.required' => 'O campo nome deve ser preenchido!',
            'email.required' => 'O campo e-mail deve ser preenchido!',
            'password.required' => 'O campo senha deve ser preenchido!',
            'name.min' => 'O campo nome deve ter no mínimo 3 caracteres!',
            'password.min' => 'O campo senha deve ter no mínimo 8 caracteres!',
            'email.email' => 'O campo e-mail é inválido!',
            'name.max' => 'O campo nome deve ter no máximo 25 caracteres!',
            'password.max' => 'O campo senha deve ter no máximo 16 caracteres!',
            'email.unique' => 'Já existe um usuário com este e-mail!',
        ];
    }
}
