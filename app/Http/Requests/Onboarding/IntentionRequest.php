<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IntentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'looking_for' => ['required', Rule::in(['amistad', 'pareja_formal', 'algo_casual', 'comunidad'])],
        ];
    }

    public function messages(): array
    {
        return [
            'looking_for.required' => 'Elige qué estás buscando en Kina.',
            'looking_for.in' => 'Selecciona una opción válida.',
        ];
    }
}
