<?php

namespace App\Http\Requests\Onboarding;

use App\Models\IdentityTag;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class TagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // Las etiquetas son opcionales.
        return [
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['integer', 'exists:identity_tags,id'],
            'consent_sensitive' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Si se seleccionó alguna etiqueta sensible, el consentimiento es obligatorio.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tagIds = $this->input('tags', []);

            if (empty($tagIds)) {
                return;
            }

            $hasSensitive = IdentityTag::whereIn('id', $tagIds)
                ->where('is_sensitive', true)
                ->exists();

            if ($hasSensitive && ! $this->boolean('consent_sensitive')) {
                $validator->errors()->add(
                    'consent_sensitive',
                    'Para incluir etiquetas sensibles necesitamos tu consentimiento explícito.'
                );
            }
        });
    }
}
