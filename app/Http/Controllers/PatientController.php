<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller responsible for managing patient records.
 */
class PatientController extends Controller
{
    /**
     * Display a paginated list of patients.
     */
    public function index(): View
    {
        $patients = Patient::query()
            ->latest()
            ->paginate(10);

        return view('patients.index', [
            'patients' => $patients,
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): View
    {
        return view('patients.create');
    }

    /**
     * Display the specified patient along with their medical records and diets.
     */
    public function show(Patient $patient): View
    {
        $patient->load([
            'medicalRecords' => fn ($query) => $query->latest('evaluated_at'),
            'diets' => fn ($query) => $query->latest()->limit(5),
        ]);

        return view('patients.show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Store a newly created patient in the database.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $ageYears = (int) $data['age_years'];
        unset($data['age_years']);

        // Default to dog species
        $data['species'] = 'dog';

        // Calculate birth date by subtracting years from today.
        // Note: as a date column, we use start of day for consistency.
        $data['birth_date'] = Carbon::today()->subYears($ageYears);

        Patient::create($data);

        return redirect()
            ->route('patients.index')
            ->with('status', 'Paciente registrado correctamente.');
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient): View
    {
        $ageYears = $patient->birth_date
            ? Carbon::parse($patient->birth_date)->age
            : null;

        return view('patients.edit', [
            'patient' => $patient,
            'ageYears' => $ageYears,
        ]);
    }

    /**
     * Update the specified patient in the database.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $data = $request->validated();

        $ageYears = (int) $data['age_years'];
        unset($data['age_years']);

        // Keep species as dog by default.
        $data['species'] = 'dog';
        $data['birth_date'] = Carbon::today()->subYears($ageYears);

        // We use fill+save to update only the model attributes
        // without affecting the relationships (medical_records, diets).
        $patient->fill($data)->save();

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Datos del paciente actualizados correctamente.');
    }

    /**
     * Remove the specified patient from the database.
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('status', 'Paciente eliminado correctamente.');
    }
}
