<x-app-layout>
    <x-slot name="title">Detalle de Evaluación</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            Detalle de Evaluación — {{ $patient->name }}
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">
            {{ $medical_record->evaluated_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
        </p>
    </x-slot>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('patients.show', $patient) }}"
               class="text-sm font-semibold text-teal-600 hover:text-teal-700 transition">
                ← Volver al paciente
            </a>
        </div>

        @php
            $r = $medical_record;
            $proteinuriaLabels = ['yes' => 'Sí', 'no' => 'No', 'unknown' => 'Desconocido'];
            $appetiteLabels = ['normal' => 'Normal', 'decreased' => 'Disminuido', 'none' => 'Nulo'];
            $activityLabels = ['low' => 'Baja (Sedentario)', 'medium' => 'Media', 'high' => 'Alta (Trabajo mod.)', 'very_high' => 'Muy Alta (Pesado)'];
            $physiologicalLabels = [
                'normal' => 'Adulto Normal',
                'gestation' => 'Gestación',
                'lactation' => 'Lactancia',
                'growth' => 'Crecimiento',
                'weight_loss' => 'Pérdida de Peso',
                'weight_gain' => 'Ganancia de Peso',
                'critical_care' => 'Cuidados Críticos'
            ];
        @endphp

        <div class="space-y-6">
            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Fecha de evaluación</h3>
                <p class="text-slate-700">{{ $r->evaluated_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección A: Físico</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Peso (kg)</p>
                        <p class="text-slate-900 mt-1">{{ $r->weight_kg ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Condición Corporal (1-5)</p>
                        <p class="text-slate-900 mt-1">{{ $r->bcs ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección B: Estadificación</h3>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Estadio IRIS</p>
                    <p class="text-slate-900 mt-1">{{ $r->iris_stage ?? 'Sin definir' }}</p>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección C: Laboratorio Renal</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Creatinina (mg/dL)</p>
                        <p class="text-slate-900 mt-1">{{ $r->creatinine ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Urea (BUN)</p>
                        <p class="text-slate-900 mt-1">{{ $r->bun ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Fósforo (mg/dL)</p>
                        <p class="text-slate-900 mt-1">{{ $r->phosphorus ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Potasio (mmol/L)</p>
                        <p class="text-slate-900 mt-1">{{ $r->potassium ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Sodio (mmol/L)</p>
                        <p class="text-slate-900 mt-1">{{ $r->sodium ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Bicarbonato (mmol/L)</p>
                        <p class="text-slate-900 mt-1">{{ $r->bicarbonate ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección D: Urianálisis</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Densidad Urinaria</p>
                        <p class="text-slate-900 mt-1">{{ $r->urine_density ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Proteinuria</p>
                        <p class="text-slate-900 mt-1">{{ $proteinuriaLabels[$r->proteinuria ?? 'unknown'] ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección E: Estado Fisiológico y Clínico</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Estado Fisiológico</p>
                        <p class="text-slate-900 mt-1">{{ $physiologicalLabels[$r->physiological_status ?? ''] ?? ($r->physiological_status ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Apetito</p>
                        <p class="text-slate-900 mt-1">{{ $appetiteLabels[$r->appetite ?? ''] ?? ($r->appetite ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Actividad</p>
                        <p class="text-slate-900 mt-1">{{ $activityLabels[$r->activity_level ?? ''] ?? ($r->activity_level ?? '—') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección F: Notas adicionales</h3>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Consideraciones especiales</p>
                    <p class="text-slate-900 mt-1 whitespace-pre-wrap">{{ $r->special_considerations ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
