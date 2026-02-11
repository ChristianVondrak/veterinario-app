<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(): View
    {
        $patients = Patient::query()
            ->latest()
            ->paginate(10);

        return view('patients.index', [
            'patients' => $patients,
        ]);
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'medicalRecords' => fn ($query) => $query->latest('evaluated_at'),
            'diets' => fn ($query) => $query->latest(),
        ]);

        return view('patients.show', [
            'patient' => $patient,
        ]);
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $ageYears = (int) $data['age_years'];
        unset($data['age_years']);

        // Guardamos siempre como perro por defecto.
        $data['species'] = 'dog';

        // Calcula la fecha de nacimiento restando años desde hoy.
        // Nota: al ser un date, usamos el inicio del día para consistencia.
        $data['birth_date'] = Carbon::today()->subYears($ageYears);

        Patient::create($data);

        return redirect()
            ->route('patients.index')
            ->with('status', 'Paciente registrado correctamente.');
    }

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

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $data = $request->validated();

        $ageYears = (int) $data['age_years'];
        unset($data['age_years']);

        // Mantener especie como perro por defecto.
        $data['species'] = 'dog';
        $data['birth_date'] = Carbon::today()->subYears($ageYears);

        $patient->update($data);

        return redirect()
            ->route('patients.index')
            ->with('status', 'Paciente actualizado correctamente.');
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('status', 'Paciente eliminado correctamente.');
    }
}

