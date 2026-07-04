<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['reviewed', 'resolved', 'dismissed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Elige un estado para el reporte.',
            'status.in' => 'Estado de reporte no válido.',
        ];
    }
}
