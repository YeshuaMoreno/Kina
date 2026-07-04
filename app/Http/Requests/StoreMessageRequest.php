<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** Recorta espacios antes de validar para impedir mensajes "vacíos". */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => is_string($this->body) ? trim($this->body) : $this->body,
        ]);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Escribe un mensaje antes de enviar.',
            'body.max' => 'El mensaje es demasiado largo (máx. 2000 caracteres).',
        ];
    }
}
