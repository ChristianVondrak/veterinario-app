<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
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
            'age_years' => ['required', 'integer', 'min:0', 'max:40'],
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
        ];
    }
}

