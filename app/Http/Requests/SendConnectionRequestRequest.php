<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendConnectionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.max' => 'El mensaje es demasiado largo (máx. 500 caracteres).',
        ];
    }
}
