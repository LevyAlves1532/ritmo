<?php

namespace App\Http\Requests\Habit;

use App\Enums\FrequencyHabitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreHabitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $frequency = array_keys(FrequencyHabitEnum::getLabels());
        return [
            'title' => 'required|min:3|max:255',
            'description' => 'nullable|string|min:6|max:3000',
            'frequency' => 'required|in:' . implode(',', $frequency),
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
            'title.required' => 'O campo título deve ser preenchido!',
            'frequency.required' => 'O campo frequência deve ser preenchido!',
            'title.min' => 'O campo título deve ter no mínimo 3 caracteres!',
            'description.min' => 'O campo descrição deve ter no mínimo 6 caracteres!',
            'frequency.in' => 'O valor do campo de frequência é inválido!',
            'title.max' => 'O campo título deve ter no máximo 255 caracteres!',
            'description.max' => 'O campo descrição deve ter no máximo 3000 caracteres!',
        ];
    }
}
