<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class InterestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'interests' => ['required', 'array', 'min:1', 'max:20'],
            'interests.*' => ['integer', 'exists:interests,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'interests.required' => 'Elige al menos un interés.',
            'interests.min' => 'Elige al menos un interés.',
            'interests.max' => 'Puedes elegir hasta 20 intereses por ahora.',
        ];
    }
}
