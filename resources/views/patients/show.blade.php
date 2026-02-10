<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-tight">
                    Dashboard del Paciente
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Seguimiento clínico renal
                </p>
            </div>

            <a href="{{ route('patients.medical-records.create', $patient) }}"
               class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-teal-600 text-white text-sm font-semibold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">
                Nueva Evaluación
            </a>
        </div>
    </x-slot>

    @php
        $records = $patient->medical_records ?? $patient->medicalRecords ?? collect();
        $ageYears = $patient->birth_date ? $patient->birth_date->age : null;
    @endphp

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <section class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Ficha del Paciente</h3>
                <p class="text-sm text-slate-600">Resumen general</p>
            </div>

            <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Nombre</p>
                    <p class="text-base font-semibold text-slate-900 mt-1">{{ $patient->name }}</p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Especie / Raza</p>
                    <p class="text-base text-slate-900 mt-1">
                        {{ ucfirst($patient->species) }} / {{ $patient->breed ?? 'No especificada' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Sexo</p>
                    <p class="text-base text-slate-900 mt-1">
                        {{ $patient->sex === 'male' ? 'Macho' : 'Hembra' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Edad</p>
                    <p class="text-base text-slate-900 mt-1">
                        {{ $ageYears !== null ? $ageYears . ' años' : 'Sin dato' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Historial Clínico</h3>
                <p class="text-sm text-slate-500">Timeline de evaluaciones médicas</p>
            </div>

            <div class="p-6 sm:p-8">
                @if ($records->isEmpty())
                    <div class="text-center py-10 border border-dashed border-slate-200 rounded-xl bg-slate-50/60">
                        <p class="text-slate-600 font-medium">Aún no hay historias clínicas registradas.</p>
                        <p class="text-sm text-slate-500 mt-1">Crea la primera evaluación para iniciar el seguimiento.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 pr-4">Fecha</th>
                                    <th class="py-3 pr-4">Estadio IRIS</th>
                                    <th class="py-3 pr-4">Peso (kg)</th>
                                    <th class="py-3 pr-4">Creatinina</th>
                                    <th class="py-3 pr-4">Urea/BUN</th>
                                    <th class="py-3 pr-4">Fósforo</th>
                                    <th class="py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($records as $record)
                                    @php
                                        $irisBadge = match($record->iris_stage) {
                                            'I' => 'bg-emerald-100 text-emerald-700',
                                            'II' => 'bg-amber-100 text-amber-700',
                                            'III' => 'bg-orange-100 text-orange-700',
                                            'IV' => 'bg-red-100 text-red-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };

                                        // Resaltado simple de valor alto (orientativo)
                                        $creatinineHigh = $record->creatinine !== null && (float) $record->creatinine >= 1.4;
                                        $bunHigh = $record->bun !== null && (float) $record->bun >= 30;
                                        $phosphorusHigh = $record->phosphorus !== null && (float) $record->phosphorus >= 5.0;
                                    @endphp

                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-4 pr-4 text-slate-700">
                                            {{ optional($record->evaluated_at)->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="py-4 pr-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $irisBadge }}">
                                                {{ $record->iris_stage ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="py-4 pr-4 text-slate-700">
                                            {{ $record->weight_kg ?? '—' }}
                                        </td>
                                        <td class="py-4 pr-4 {{ $creatinineHigh ? 'text-red-700 font-semibold' : 'text-slate-700' }}">
                                            {{ $record->creatinine ?? '—' }}
                                        </td>
                                        <td class="py-4 pr-4 {{ $bunHigh ? 'text-red-700 font-semibold' : 'text-slate-700' }}">
                                            {{ $record->bun ?? '—' }}
                                        </td>
                                        <td class="py-4 pr-4 {{ $phosphorusHigh ? 'text-red-700 font-semibold' : 'text-slate-700' }}">
                                            {{ $record->phosphorus ?? '—' }}
                                        </td>
                                        <td class="py-4 text-right">
                                            <a href="{{ route('patients.medical-records.show', [$patient, $record]) }}"
                                               class="inline-flex items-center justify-center p-2 rounded-lg text-slate-600 hover:text-teal-600 hover:bg-slate-50 transition"
                                               title="Ver Detalle">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>

