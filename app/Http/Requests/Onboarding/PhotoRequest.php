<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class PhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        // La foto es opcional en el onboarding.
        return [
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'Usa una imagen JPG, PNG o WEBP.',
            'photo.max' => 'La imagen no debe pesar más de 4 MB.',
        ];
    }
}
