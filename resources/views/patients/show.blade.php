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

            <div class="flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('patients.diet.store', $patient) }}">
                    @csrf

                    <button type="submit"
                        class="relative inline-flex h-11 sm:h-12 overflow-hidden rounded-full p-[2px] focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-white group shadow-md shadow-indigo-100 transition-transform hover:scale-[1.02]">

                        <span
                            class="absolute inset-[-1000%] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#F87171_0%,#FBBF24_10%,#34D399_40%,#60A5FA_60%,#818CF8_90%,#F87171_100%)]"></span>

                        <span
                            class="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-full bg-white px-5 sm:px-6 py-1 text-sm font-semibold text-slate-700 backdrop-blur-3xl transition-all hover:bg-slate-50">

                            <svg class="w-5 h-5 mr-2 text-indigo-500 group-hover:text-indigo-600 transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>

                            <span
                                class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 group-hover:from-indigo-500 group-hover:to-pink-500">
                                Generar Dieta con IA
                            </span>
                        </span>
                    </button>
                </form>

                <a href="{{ route('patients.medical-records.create', $patient) }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-teal-600 text-white text-sm font-semibold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">
                    Nueva Evaluación
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $records = $patient->medical_records ?? $patient->medicalRecords ?? collect();
        $diets = $patient->diets ?? collect();
        $ageYears = $patient->birth_date ? $patient->birth_date->age : null;
    @endphp

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="px-4 py-3 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="px-4 py-3 rounded-xl border border-red-200 bg-red-50 text-red-800 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

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
                                        $irisBadge = match ($record->iris_stage) {
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
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $irisBadge }}">
                                                {{ $record->iris_stage ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="py-4 pr-4 text-slate-700">
                                            {{ $record->weight_kg ?? '—' }}
                                        </td>
                                        <td
                                            class="py-4 pr-4 {{ $creatinineHigh ? 'text-red-700 font-semibold' : 'text-slate-700' }}">
                                            {{ $record->creatinine ?? '—' }}
                                        </td>
                                        <td class="py-4 pr-4 {{ $bunHigh ? 'text-red-700 font-semibold' : 'text-slate-700' }}">
                                            {{ $record->bun ?? '—' }}
                                        </td>
                                        <td
                                            class="py-4 pr-4 {{ $phosphorusHigh ? 'text-red-700 font-semibold' : 'text-slate-700' }}">
                                            {{ $record->phosphorus ?? '—' }}
                                        </td>
                                        <td class="py-4 text-right">
                                            <a href="{{ route('patients.medical-records.show', [$patient, $record]) }}"
                                                class="inline-flex items-center justify-center p-2 rounded-lg text-slate-600 hover:text-teal-600 hover:bg-slate-50 transition"
                                                title="Ver Detalle">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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

        <section class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Historial Nutricional</h3>
                <p class="text-sm text-slate-500">Dietas generadas con IA para seguimiento clínico</p>
            </div>

            <div class="p-6 sm:p-8">
                @if ($diets->isEmpty())
                    <div class="text-center py-10 border border-dashed border-slate-200 rounded-xl bg-slate-50/60">
                        <p class="text-slate-600 font-medium">Aún no hay dietas generadas.</p>
                        <p class="text-sm text-slate-500 mt-1">Usa el botón “Generar Dieta con IA” para crear la primera
                            propuesta nutricional.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($diets as $diet)
                            @php
                                $content = $diet->content ?? [];
                                $description = $content['description'] ?? null;
                                $dailyPlan = is_array($content['daily_plan'] ?? null) ? $content['daily_plan'] : [];
                                $tips = is_array($content['tips'] ?? null) ? $content['tips'] : [];
                            @endphp

                            <article class="border border-slate-200 rounded-xl overflow-hidden">
                                <div
                                    class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <h4 class="text-base font-semibold text-slate-900">{{ $diet->summary }}</h4>
                                    <span class="text-xs font-medium text-slate-500">
                                        {{ $diet->created_at?->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <div class="p-5 space-y-5">
                                    @if ($description)
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Explicación
                                                clínica</p>
                                            <p class="text-sm text-slate-700 mt-1">{{ $description }}</p>
                                        </div>
                                    @endif

                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-3">Plan diario
                                        </p>
                                        @if (empty($dailyPlan))
                                            <p class="text-sm text-slate-500">No se recibieron comidas en esta dieta.</p>
                                        @else
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                                @foreach ($dailyPlan as $item)
                                                    <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                                        <p class="text-sm font-semibold text-slate-900">{{ $item['meal'] ?? 'Comida' }}
                                                        </p>
                                                        <div>
                                                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">
                                                                Ingredientes</p>
                                                            <p class="text-sm text-slate-700 mt-1">
                                                                {{ $item['ingredients'] ?? 'No especificado' }}
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">
                                                                Instrucciones</p>
                                                            <p class="text-sm text-slate-700 mt-1">
                                                                {{ $item['instructions'] ?? 'No especificado' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    @if (!empty($tips))
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-2">
                                                Recomendaciones</p>
                                            <ul class="list-disc pl-5 space-y-1 text-sm text-slate-700">
                                                @foreach ($tips as $tip)
                                                    <li>{{ $tip }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>