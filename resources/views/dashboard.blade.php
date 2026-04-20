<x-app-layout>
    <x-slot name="title">Panel</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            Panel
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">Bienvenido a VetNutri AI</p>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <livewire:dashboard.search-patients />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Columna izquierda (2/3): Seguimiento clínico --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Tarjeta Pacientes Críticos (IRIS III / IV) --}}
                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h3 class="text-base font-semibold text-slate-900">Pacientes Críticos (IRIS III / IV)</h3>
                        <p class="text-sm text-slate-500">Últimos valores de creatinina y urea</p>
                    </div>
                    <div class="p-6">
                        @if($criticalPatients->isEmpty())
                            <p class="text-slate-500 text-sm py-4">No hay pacientes en estadio crítico.</p>
                        @else
                            {{-- VISTA MÓVIL (Tarjetas) --}}
                            <ul class="divide-y divide-slate-100 sm:hidden">
                                @foreach($criticalPatients as $patient)
                                    @php
                                        $last = $patient->medicalRecords->first();
                                        $irisBadge = $last?->iris_stage === 'IV' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700';
                                    @endphp
                                    <li class="p-4">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate">{{ $patient->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $patient->breed ?? 'Sin raza' }}</p>
                                            </div>
                                            <span class="inline-flex shrink-0 items-center px-2 py-0.5 rounded-full text-[11px] font-semibold tracking-wide {{ $irisBadge }}">
                                                IRIS {{ $last?->iris_stage ?? '—' }}
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-3 mb-3 text-sm border-t border-slate-50 pt-2 text-slate-700">
                                            <div>
                                                <span class="text-xs font-medium text-slate-400 block">Creatinina</span>
                                                {{ $last?->creatinine ?? '—' }} mg/dL
                                            </div>
                                            <div>
                                                <span class="text-xs font-medium text-slate-400 block">Urea (BUN)</span>
                                                {{ $last?->bun ?? '—' }} mg/dL
                                            </div>
                                        </div>

                                        <a href="{{ route('patients.show', $patient) }}" class="flex items-center justify-center w-full py-2 bg-slate-50 hover:bg-slate-100 text-sky-700 text-sm font-medium rounded-lg transition gap-1.5 border border-slate-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ir al Paciente
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- VISTA DESKTOP (Tabla) --}}
                            <div class="hidden sm:block overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                                            <th class="pb-3 pr-4">Nombre</th>
                                            <th class="pb-3 pr-4">Raza</th>
                                            <th class="pb-3 pr-4">IRIS</th>
                                            <th class="pb-3 pr-4">Creatinina</th>
                                            <th class="pb-3 pr-4">Urea</th>
                                            <th class="pb-3">Ver</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($criticalPatients as $patient)
                                            @php
                                                $last = $patient->medicalRecords->first();
                                                $irisBadge = $last?->iris_stage === 'IV' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700';
                                            @endphp
                                            <tr class="hover:bg-slate-50/60 transition">
                                                <td class="py-3 pr-4 font-medium text-slate-900">
                                                    {{ $patient->name }}
                                                </td>
                                                <td class="py-3 pr-4 text-slate-600">{{ $patient->breed ?? '—' }}</td>
                                                <td class="py-3 pr-4">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $irisBadge }}">
                                                        {{ $last?->iris_stage ?? '—' }}
                                                    </span>
                                                </td>
                                                <td class="py-3 pr-4 text-slate-700">{{ $last?->creatinine ?? '—' }}</td>
                                                <td class="py-3 pr-4 text-slate-700">{{ $last?->bun ?? '—' }}</td>
                                                <td class="py-3">
                                                    <a href="{{ route('patients.show', $patient) }}"
                                                       class="inline-flex items-center justify-center p-2 rounded-lg text-slate-600 hover:text-sky-600 hover:bg-slate-50 transition"
                                                       title="Ver paciente">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                {{-- Tarjeta Actividad Reciente --}}
                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h3 class="text-base font-semibold text-slate-900">Actividad Reciente</h3>
                        <p class="text-sm text-slate-500">Últimas evaluaciones realizadas</p>
                    </div>
                    <div class="p-6">
                        @if($recentRecords->isEmpty())
                            <p class="text-slate-500 text-sm py-4">Aún no hay evaluaciones registradas.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach($recentRecords as $record)
                                    <li class="flex items-start gap-3 py-2 border-b border-slate-100 last:border-0">
                                        <div class="w-9 h-9 rounded-lg bg-sky-50 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-medium text-slate-900">
                                                {{ $record->patient?->name ?? 'Paciente' }}
                                                @if($record->patient?->breed)
                                                    <span class="text-slate-400 font-normal text-xs"> · {{ $record->patient->breed }}</span>
                                                @endif
                                                <span class="text-slate-500 font-normal">— {{ $record->evaluated_at?->format('d/m/Y') ?? '—' }}</span>
                                            </p>
                                            <p class="text-sm text-slate-600 mt-0.5">
                                                @if($record->iris_stage)
                                                    IRIS {{ $record->iris_stage }}
                                                    @if($record->special_considerations)
                                                        · {{ Str::limit($record->special_considerations, 50) }}
                                                    @endif
                                                @else
                                                    {{ Str::limit($record->special_considerations ?? 'Evaluación general', 60) }}
                                                @endif
                                            </p>
                                        </div>
                                        <a href="{{ $record->patient ? route('patients.show', $record->patient) : '#' }}"
                                           class="shrink-0 text-sm font-medium text-sky-600 hover:text-sky-700">Ver</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>
            </div>

            {{-- Columna derecha (1/3): Estadísticas --}}
            <div class="space-y-6">
                {{-- KPIs --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pacientes</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalPatients }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Casos Críticos</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $criticalPatients->count() }}</p>
                    </div>
                </div>

                {{-- Población por Estadio IRIS (Chart.js) --}}
                <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                        <h3 class="text-base font-semibold text-slate-900">Población por Estadio IRIS</h3>
                        <p class="text-sm text-slate-500">Distribución de evaluaciones</p>
                    </div>
                    <div class="p-6 flex justify-center items-center min-h-[240px]">
                        @if($hasIrisData)
                            <canvas id="irisChart" width="280" height="280"></canvas>
                        @else
                            <div class="w-full text-center py-8 border border-dashed border-slate-200 rounded-xl bg-slate-50/60">
                                <p class="text-sm font-medium text-slate-600">Sin datos para graficar</p>
                                <p class="text-xs text-slate-500 mt-1">Aún no hay evaluaciones con estadio IRIS registrado.</p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                var irisStats = @json($irisStats);
                var hasIrisData = @json($hasIrisData);
                var ctx = document.getElementById('irisChart');
                if (!ctx || !hasIrisData) return;
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['I', 'II', 'III', 'IV'],
                        datasets: [{
                            data: [
                                irisStats.I || 0,
                                irisStats.II || 0,
                                irisStats.III || 0,
                                irisStats.IV || 0
                            ],
                            backgroundColor: [
                                'rgb(16, 185, 129)',
                                'rgb(245, 158, 11)',
                                'rgb(249, 115, 22)',
                                'rgb(239, 68, 68)'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            })();

        </script>
    @endpush
</x-app-layout>
