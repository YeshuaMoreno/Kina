<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrivacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'profile_visibility' => ['required', Rule::in(['publico', 'solo_conexiones', 'nunca'])],
            'sensitive_tags_visibility' => ['required', Rule::in(['nunca', 'solo_conexiones', 'publico'])],
            'show_sensitive_tags' => ['nullable', 'boolean'],
            'consent_privacy' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_privacy.accepted' => 'Necesitas aceptar el aviso de privacidad para continuar.',
        ];
    }
}
