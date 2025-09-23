<?php

namespace App\Http\Requests\Habit;

use App\Enums\FrequencyHabitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateHabitRequest extends FormRequest
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
            'title' => 'sometimes|min:3|max:255',
            'description' => 'nullable|string|min:6|max:3000',
            'frequency' => 'sometimes|in:' . implode(',', $frequency),
        ];
    }
}
