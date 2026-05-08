<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller responsible for managing patient medical records.
 */
class MedicalRecordController extends Controller
{
    /**
     * Show the form for creating a new medical record for the given patient.
     */
    public function create(Patient $patient): View
    {
        return view('medical_records.create', [
            'patient' => $patient,
        ]);
    }

    /**
     * Display the specified medical record.
     */
    public function show(Patient $patient, MedicalRecord $medical_record): View
    {
        abort_if($medical_record->patient_id !== $patient->id, 404);

        return view('medical_records.show', [
            'patient' => $patient,
            'medical_record' => $medical_record,
        ]);
    }

    /**
     * Store a newly created medical record for the specified patient.
     */
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
            'bicarbonate' => ['nullable', 'numeric', 'min:0', 'max:50'],

            'urine_density' => ['nullable', 'numeric', 'regex:/^1\.(0[0-9]{2}|100)$/'],
            'proteinuria' => ['required', Rule::in(['yes', 'no', 'unknown'])],

            'appetite' => ['required', Rule::in(['normal', 'decreased', 'none'])],
            'activity_level' => ['required', Rule::in(['low', 'medium', 'high'])],
            'physiological_status' => [
                'nullable',
                Rule::in(['normal', 'gestation', 'lactation', 'weight_loss', 'weight_gain', 'critical_care']),
                function ($attribute, $value, $fail) use ($patient) {
                    if ($patient->sex === 'male' && in_array($value, ['gestation', 'lactation'])) {
                        $fail('Un paciente macho no puede estar en gestación ni lactancia.');
                    }
                },
            ],

            'special_considerations' => ['nullable', 'string'],
        ], [
            'bcs.between' => 'La condición corporal (BCS) debe estar entre 1 y 5.',
            'urine_density.regex' => 'La densidad urinaria debe tener el formato exacto de 3 decimales (ej. 1.025) y estar entre 1.000 y 1.100.',
            'activity_level.in' => 'El nivel de actividad no es válido.',
        ]);

        $patient->medicalRecords()->create($validated);

        return redirect()
            ->route('patients.index')
            ->with('status', "Historia médica registrada para {$patient->name}.");
    }
}
