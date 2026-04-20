<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'breed' => ['nullable', 'string', 'max:255'],
            'sex' => ['required', Rule::in(['male', 'female'])],
            'reproductive_status' => ['required', Rule::in(['intact', 'neutered'])],
            // Campo del formulario (no existe en la tabla): se usa para calcular birth_date
            'age_years' => ['required', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'sex.in' => 'El sexo debe ser macho o hembra.',
            'reproductive_status.in' => 'El estado reproductivo debe ser entero o esterilizado.',
            'age_years.required' => 'La edad es obligatoria.',
            'age_years.integer' => 'La edad debe ser un número entero.',
            'age_years.min'     => 'La edad mínima es 1 año. No se registran pacientes menores de 1 año.',
            'age_years.max'     => 'La edad máxima es 30 años.',
        ];
    }
}

