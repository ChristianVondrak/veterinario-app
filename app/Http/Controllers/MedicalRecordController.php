<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function create(Patient $patient): View
    {
        return view('medical_records.create', [
            'patient' => $patient,
        ]);
    }

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'weight_kg' => ['required', 'numeric', 'min:0'],
            'bcs' => ['required', 'integer', 'between:1,5'],
            'iris_stage' => ['nullable', Rule::in(['I', 'II', 'III', 'IV'])],

            'creatinine' => ['nullable', 'numeric', 'min:0'],
            'bun' => ['nullable', 'numeric', 'min:0'],
            'phosphorus' => ['nullable', 'numeric', 'min:0'],
            'potassium' => ['nullable', 'numeric', 'min:0'],
            'sodium' => ['nullable', 'numeric', 'min:0'],

            'urine_density' => ['nullable', 'numeric', 'between:1,1.2'],
            'proteinuria' => ['required', Rule::in(['yes', 'no', 'unknown'])],

            'appetite' => ['required', Rule::in(['normal', 'decreased', 'none'])],
            'activity_level' => ['required', Rule::in(['low', 'medium', 'high'])],

            'special_considerations' => ['nullable', 'string'],
        ], [
            'bcs.between' => 'La condición corporal (BCS) debe estar entre 1 y 5.',
            'urine_density.between' => 'La densidad urinaria debe estar entre 1.000 y 1.200.',
        ]);

        $patient->medicalRecords()->create($validated);

        return redirect()
            ->route('patients.index')
            ->with('status', "Historia médica registrada para {$patient->name}.");
    }
}

