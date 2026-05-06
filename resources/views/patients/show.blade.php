<x-app-layout>
    <x-slot name="title">Detalle de Paciente</x-slot>
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
                <form method="POST" action="{{ route('patients.diet.store', $patient) }}"
                    x-data="{ loading: false }"
                    @submit="loading = true">
                    @csrf

                    {{-- ── LOADING OVERLAY (fixed, full screen) ───────────────── --}}
                    <div x-show="loading"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        style="display:none"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm">
                        <div class="bg-white rounded-2xl shadow-2xl px-8 py-7 flex flex-col items-center gap-4 max-w-xs w-full mx-4">
                            {{-- Animated rings --}}
                            <div class="relative w-14 h-14">
                                <div class="absolute inset-0 rounded-full border-4 border-indigo-100"></div>
                                <div class="absolute inset-0 rounded-full border-4 border-indigo-600 border-t-transparent animate-spin"></div>
                                <div class="absolute inset-[6px] rounded-full bg-indigo-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-slate-900">Generando dieta con IA</p>
                                <p class="text-xs text-slate-500 mt-1">Calculando calorías y nutrientes…</p>
                            </div>
                            {{-- Progress dots --}}
                            <div class="flex gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:0ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:150ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:300ms"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ── BUTTON ─────────────────────────────────────────────── --}}
                    <button type="submit"
                        :disabled="loading"
                        :class="loading ? 'opacity-60 cursor-not-allowed pointer-events-none' : 'hover:scale-[1.02]'"
                        class="relative inline-flex h-11 sm:h-12 overflow-hidden rounded-full p-[2px] focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-white group shadow-md shadow-indigo-100 transition-all">

                        <span class="absolute inset-[-1000%] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#F87171_0%,#FBBF24_10%,#34D399_40%,#60A5FA_60%,#818CF8_90%,#F87171_100%)]"></span>

                        <span class="inline-flex h-full w-full cursor-pointer items-center justify-center rounded-full bg-white px-5 sm:px-6 py-1 text-sm font-semibold text-slate-700 backdrop-blur-3xl transition-all hover:bg-slate-50">

                            {{-- Idle icon --}}
                            <svg x-show="!loading" class="w-5 h-5 mr-2 text-indigo-500 group-hover:text-indigo-600 transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>

                            {{-- Loading spinner (inline) --}}
                            <svg x-show="loading" style="display:none" class="w-4 h-4 mr-2 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
                            </svg>

                            <span x-text="loading ? 'Calculando…' : 'Generar Dieta con IA'"
                                class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600">
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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
                        @php
                            $especieLabel = match(strtolower($patient->species)) {
                                'dog' => 'Canino',
                                'cat' => 'Felino',
                                default => ucfirst($patient->species),
                            };
                        @endphp
                        {{ $especieLabel }} / {{ $patient->breed ?? 'No especificada' }}
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
                    {{-- VISTA MÓVIL (Tarjetas) --}}
                    <ul class="divide-y divide-slate-100 sm:hidden">
                        @foreach ($records as $record)
                            @php
                                $irisBadge = match ($record->iris_stage) {
                                    'I' => 'bg-emerald-100 text-emerald-700',
                                    'II' => 'bg-amber-100 text-amber-700',
                                    'III' => 'bg-orange-100 text-orange-700',
                                    'IV' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                                $creatinineHigh = $record->creatinine !== null && (float) $record->creatinine >= 1.4;
                                $bunHigh = $record->bun !== null && (float) $record->bun >= 30;
                                $phosphorusHigh = $record->phosphorus !== null && (float) $record->phosphorus >= 5.0;
                            @endphp
                            <li class="py-4">
                                <div class="flex items-center justify-between mb-3 border-b border-slate-50 pb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ optional($record->evaluated_at)->format('d/m/Y') ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $irisBadge }}">
                                        {{ $record->iris_stage ?? '—' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-slate-700 mb-4 px-1">
                                    <div>
                                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Peso</span>
                                        {{ $record->weight_kg ?? '—' }}
                                    </div>
                                    <div>
                                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Creatinina</span>
                                        <span class="{{ $creatinineHigh ? 'text-red-700 font-semibold' : '' }}">{{ $record->creatinine ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Urea (BUN)</span>
                                        <span class="{{ $bunHigh ? 'text-red-700 font-semibold' : '' }}">{{ $record->bun ?? '—' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Fósforo</span>
                                        <span class="{{ $phosphorusHigh ? 'text-red-700 font-semibold' : '' }}">{{ $record->phosphorus ?? '—' }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('patients.medical-records.show', [$patient, $record]) }}" class="flex items-center justify-center w-full py-2 bg-slate-50 hover:bg-slate-100 text-teal-700 text-sm font-medium rounded-lg transition gap-1.5 border border-slate-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    Ver Detalle
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- VISTA DESKTOP (Tabla) --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 pr-4">Fecha</th>
                                    <th class="py-3 pr-4">Estadio IRIS</th>
                                    <th class="py-3 pr-4">Peso (kg)</th>
                                    <th class="py-3 pr-4">Creatinina</th>
                                    <th class="py-3 pr-4">Urea/BUN</th>
                                    <th class="py-3 pr-4">Fósforo</th>
                                    <th class="py-3 text-right">Otros</th>
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
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Historial Nutricional</h3>
                    <p class="text-sm text-slate-500">Últimas {{ $diets->count() }} dieta{{ $diets->count() !== 1 ? 's' : '' }} generadas</p>
                </div>
                @if ($diets->isNotEmpty())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                        {{ $diets->count() }} registro{{ $diets->count() !== 1 ? 's' : '' }}
                    </span>
                @endif
            </div>

            <div class="p-6 sm:p-8">
                @if ($diets->isEmpty())
                    <div class="text-center py-12 border border-dashed border-slate-200 rounded-xl bg-slate-50/60">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-slate-600 font-medium">Aún no hay dietas generadas.</p>
                        <p class="text-sm text-slate-500 mt-1">Usa el botón "Generar Dieta con IA" para crear la primera propuesta nutricional.</p>
                    </div>
                @else
                    @php
                        $totalDietsCount = $patient->diets()->count();
                    @endphp
                    <div class="space-y-3" x-data="{ open: 0 }">
                        @foreach ($diets as $i => $diet)
                            @php
                                $dietNumber = $totalDietsCount - $i;
                                $c       = $diet->content ?? [];
                                $justif  = $c['justificacion_clinica'] ?? ($c['description'] ?? null);
                                $ingreds = is_array($c['ingredientes'] ?? null) ? $c['ingredientes'] : [];
                                $aporte  = is_array($c['aporte_nutricional'] ?? null) ? $c['aporte_nutricional'] : ($c['aporte_total'] ?? []);
                                $defics  = is_array($c['deficiencias'] ?? null) ? $c['deficiencias'] : [];
                                $alertas = is_array($c['alertas_iris'] ?? null) ? $c['alertas_iris'] : [];
                                $steps   = is_array($c['instrucciones_preparacion'] ?? null) ? $c['instrucciones_preparacion'] : [];
                                $daily   = is_array($c['daily_plan'] ?? null) ? $c['daily_plan'] : [];
                                $tips    = is_array($c['tips'] ?? null) ? $c['tips'] : [];
                                $recipe  = $c['recipe_name'] ?? null;
                                $mer     = isset($c['mer_kcal']) ? number_format($c['mer_kcal'], 0) : null;
                                $badgeColors = [
                                    'ADECUADO' => 'bg-emerald-100 text-emerald-700',
                                    'LEVE'     => 'bg-amber-100 text-amber-700',
                                    'MODERADO' => 'bg-orange-100 text-orange-700',
                                    'CRÍTICO'  => 'bg-red-100 text-red-700',
                                    'EXCESO'   => 'bg-rose-100 text-rose-700'
                                ];
                            @endphp

                            <div class="border border-slate-200 rounded-xl overflow-hidden transition-shadow hover:shadow-md">

                                {{-- Accordion header --}}
                                <button type="button"
                                    @click="open = (open === {{ $i }}) ? null : {{ $i }}"
                                    class="w-full flex items-center justify-between px-5 py-4 bg-slate-50 hover:bg-slate-100/70 transition text-left gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex-shrink-0 w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">
                                            {{ $dietNumber }}
                                        </span>
                                        <div class="min-w-0">
                                            @php
                                                $dietTitle = 'Dieta ' . $dietNumber . ' - ' . $patient->name . ' - (' . ($diet->created_at?->format('d/m/y') ?? '') . ')';
                                            @endphp
                                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $dietTitle }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-2">
                                                <span>{{ $diet->created_at?->format('d/m/Y H:i') }}</span>
                                                @if (isset($aporte['kcal']))
                                                    <span class="px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 font-medium" title="Aporte calórico real de los ingredientes">
                                                        🍽 Dieta: {{ number_format($aporte['kcal'], 0) }} kcal
                                                    </span>
                                                @endif
                                                @if ($mer)
                                                    <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 font-medium" title="Requerimiento Energético de Mantenimiento del paciente">
                                                        ⚡ Paciente (MER): {{ $mer }} kcal
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-200"
                                        :class="open === {{ $i }} ? 'rotate-180' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- Accordion body --}}
                                <div x-show="open === {{ $i }}"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    @if ($i !== 0) style="display:none" @endif
                                    class="divide-y divide-slate-100">

                                    {{-- IRIS alerts --}}
                                    @if (!empty($alertas))
                                        <div class="px-5 py-4 bg-amber-50/60 space-y-1.5">
                                            @foreach ($alertas as $alerta)
                                                <p class="text-xs text-amber-800 leading-relaxed">{{ $alerta }}</p>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- justificación clínica --}}
                                    @if ($justif)
                                        <div class="px-5 py-5">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-2">Justificación Clínica</p>
                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $justif }}</p>
                                        </div>
                                    @endif

                                    {{-- ingredientes --}}
                                    @if (!empty($ingreds))
                                        <div class="px-5 py-5">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-3">Ingredientes y Aporte</p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach ($ingreds as $ing)
                                                    <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-3 py-2.5 gap-2">
                                                        <span class="text-sm text-slate-800 font-medium truncate">{{ $ing['name'] ?? '—' }}</span>
                                                        <div class="flex-shrink-0 text-xs text-slate-500 text-right">
                                                            <span class="font-semibold text-slate-700">{{ $ing['grams'] ?? '?' }}g</span>
                                                            @if (isset($ing['kcal'])) · {{ $ing['kcal'] }} kcal @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- aporte total --}}
                                    @if (!empty($aporte))
                                        <div class="px-5 py-5">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-3">Aporte Nutricional Total</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ([
                                                    'Proteína'      => [$aporte['protein_g']      ?? null, 'g',  'bg-blue-50 text-blue-700'],
                                                    'Grasa'         => [$aporte['fat_g']           ?? null, 'g',  'bg-orange-50 text-orange-700'],
                                                    'Carbohidratos' => [$aporte['carbohydrate_g']  ?? null, 'g',  'bg-yellow-50 text-yellow-700'],
                                                    'Fósforo'       => [$aporte['phosphorus_mg']   ?? null, 'mg', 'bg-purple-50 text-purple-700'],
                                                    'Potasio'       => [$aporte['potassium_mg']    ?? null, 'mg', 'bg-teal-50 text-teal-700'],
                                                    'Omega-3'       => [isset($aporte['omega_3_g']) ? round($aporte['omega_3_g'] * 1000) : null, 'mg', 'bg-cyan-50 text-cyan-700'],
                                                    'Agua'          => [$aporte['water_g']         ?? null, 'g',  'bg-slate-100 text-slate-600'],
                                                ] as $label => [$val, $unit, $cls])
                                                    @if ($val !== null)
                                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $cls }}">
                                                            {{ $label }}: {{ $val }}{{ $unit }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- deficiencias --}}
                                    @if (!empty($defics))
                                        <div class="px-5 py-5">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-3">Estado Nutricional vs. NRC</p>
                                            <div class="overflow-x-auto rounded-lg border border-slate-100">
                                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                                    <thead class="bg-slate-50">
                                                        <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                                            <th class="py-2.5 px-4 text-left">Nutriente</th>
                                                            <th class="py-2.5 px-4 text-left" title="Regla base del NRC (National Research Council)">Indicativo NRC</th>
                                                            <th class="py-2.5 px-4 text-right" title="Requerimiento ajustado a las kcal del paciente">Requerido</th>
                                                            <th class="py-2.5 px-4 text-right font-bold text-slate-700">Aporte</th>
                                                            <th class="py-2.5 px-4 text-center">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-50 bg-white">
                                                        @foreach ($defics as $d)
                                                            @php $cls = $badgeColors[$d['estado']] ?? 'bg-slate-100 text-slate-600'; @endphp
                                                            <tr class="hover:bg-slate-50/50">
                                                                <td class="py-2.5 px-4 text-slate-700 font-medium">{{ $d['nutriente'] }}</td>
                                                                <td class="py-2.5 px-4 text-left text-slate-500">{{ $d['indicativo'] ?? '—' }}</td>
                                                                <td class="py-2.5 px-4 text-right text-slate-500">{{ $d['requerido'] }}</td>
                                                                <td class="py-2.5 px-4 text-right text-slate-800 font-semibold">{{ $d['aporte'] }}</td>
                                                                <td class="py-2.5 px-4 text-center">
                                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls }}">
                                                                        {{ $d['estado'] }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- preparación --}}
                                    @if (!empty($steps))
                                        <div class="px-5 py-5">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-3">Instrucciones de Preparación</p>
                                            <ol class="space-y-2">
                                                @foreach ($steps as $n => $step)
                                                    <li class="flex gap-3 text-sm text-slate-700">
                                                        <span class="flex-shrink-0 w-5 h-5 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center mt-0.5">{{ $n + 1 }}</span>
                                                        <span>{{ $step }}</span>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @endif

                                    {{-- plan diario --}}
                                    @if (!empty($daily))
                                        <div class="px-5 py-5">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-3">Plan Diario de Comidas</p>
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                                @foreach ($daily as $item)
                                                    <div class="rounded-lg border border-slate-200 bg-white p-4 space-y-2">
                                                        <p class="text-sm font-semibold text-slate-900">{{ $item['meal'] ?? 'Comida' }}</p>
                                                        <div>
                                                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Ingredientes</p>
                                                            <p class="text-sm text-slate-700 mt-1">{{ $item['ingredients'] ?? '—' }}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide">Instrucciones</p>
                                                            <p class="text-sm text-slate-700 mt-1">{{ $item['instructions'] ?? '—' }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- tips --}}
                                    @if (!empty($tips))
                                        <div class="px-5 py-5 bg-slate-50/40">
                                            <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold mb-2">💡 Recomendaciones para el propietario</p>
                                            <ul class="space-y-1.5">
                                                @foreach ($tips as $tip)
                                                    <li class="flex gap-2 text-sm text-slate-700">
                                                        <span class="text-teal-500 mt-0.5">✓</span>
                                                        <span>{{ $tip }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>{{-- /accordion body --}}
                            </div>
                        @endforeach

                        @if ($diets->count() >= 5)
                            <p class="text-center text-xs text-slate-400 pt-1">
                                Mostrando las 5 dietas más recientes. Las anteriores siguen guardadas en la base de datos.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>