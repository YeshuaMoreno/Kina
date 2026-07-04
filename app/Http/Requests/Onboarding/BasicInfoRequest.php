<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class BasicInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxBirthdate = Carbon::now()->subYears(18)->format('Y-m-d');

        return [
            'display_name' => ['required', 'string', 'min:2', 'max:50'],
            'birthdate' => ['required', 'date', 'before_or_equal:' . $maxBirthdate],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'confirm_adult' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Escribe cómo quieres que te llamen.',
            'birthdate.required' => 'Necesitamos tu fecha de nacimiento.',
            'birthdate.before_or_equal' => 'Kina es solo para mayores de 18 años.',
            'confirm_adult.accepted' => 'Debes confirmar que eres mayor de 18 años para continuar.',
        ];
    }
}
