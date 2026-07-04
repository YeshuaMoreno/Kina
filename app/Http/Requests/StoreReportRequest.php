<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(array_keys(self::reasons()))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Elige un motivo del reporte.',
            'reason.in' => 'Selecciona un motivo válido.',
        ];
    }

    /**
     * Motivos de reporte disponibles (clave => etiqueta).
     *
     * @return array<string,string>
     */
    public static function reasons(): array
    {
        return [
            'contenido_inapropiado' => 'Contenido inapropiado',
            'acoso' => 'Acoso o mensajes molestos',
            'perfil_falso' => 'Perfil falso o suplantación',
            'spam' => 'Spam o publicidad',
            'menor_de_edad' => 'Sospecha de menor de edad',
            'otro' => 'Otro',
        ];
    }
}
