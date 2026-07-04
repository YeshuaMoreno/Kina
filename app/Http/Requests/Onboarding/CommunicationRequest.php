<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'social_battery' => ['required', Rule::in(['baja', 'media', 'alta'])],
            'prefers_text' => ['nullable', 'boolean'],
            'direct_communication' => ['nullable', 'boolean'],
            'slow_responder' => ['nullable', 'boolean'],
            'prefers_quiet_plans' => ['nullable', 'boolean'],
            'chat_before_meeting' => ['nullable', 'boolean'],
            'no_surprise_calls' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'social_battery.required' => 'Cuéntanos cómo suele estar tu batería social.',
        ];
    }
}
