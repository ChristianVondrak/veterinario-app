<x-app-layout>
    <x-slot name="title">Nueva Evaluación</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            Nueva Evaluación para: {{ $patient->name }}
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">
            Completa los datos clínicos y laboratoriales del paciente.
        </p>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('patients.index') }}"
               class="text-sm font-semibold text-teal-600 hover:text-teal-700 transition">
                ← Volver a pacientes
            </a>
        </div>

        <form method="POST"
              action="{{ route('patients.medical-records.store', $patient) }}"
              class="space-y-6">
            @csrf

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección A: Físico</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="weight_kg" value="Peso (kg)" />
                        <x-text-input id="weight_kg" name="weight_kg" type="number" step="0.01" min="0" :value="old('weight_kg')" required />
                        <x-input-error :messages="$errors->get('weight_kg')" />
                    </div>

                    <div>
                        <x-input-label for="bcs" value="Condición Corporal (1-5)" />
                        <select id="bcs" name="bcs" required
                                class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">
                            <option value="" disabled {{ old('bcs') ? '' : 'selected' }}>Selecciona...</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ (string) old('bcs') === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                        <x-input-error :messages="$errors->get('bcs')" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección B: Estadificación</h3>
                <div>
                    <x-input-label for="iris_stage" value="Estadio IRIS (opcional)" />
                    <select id="iris_stage" name="iris_stage"
                            class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">
                        <option value="">Sin definir</option>
                        @foreach (['I', 'II', 'III', 'IV'] as $stage)
                            <option value="{{ $stage }}" {{ old('iris_stage') === $stage ? 'selected' : '' }}>{{ $stage }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('iris_stage')" />
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección C: Laboratorio Renal</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <x-input-label for="creatinine" value="Creatinina (mg/dL)" />
                        <x-text-input id="creatinine" name="creatinine" type="number" step="0.01" min="0" :value="old('creatinine')" />
                        <x-input-error :messages="$errors->get('creatinine')" />
                    </div>
                    <div>
                        <x-input-label for="bun" value="Urea / BUN (mg/dL)" />
                        <x-text-input id="bun" name="bun" type="number" step="0.01" min="0" :value="old('bun')" />
                        <x-input-error :messages="$errors->get('bun')" />
                    </div>
                    <div>
                        <x-input-label for="phosphorus" value="Fósforo (mg/dL)" />
                        <x-text-input id="phosphorus" name="phosphorus" type="number" step="0.01" min="0" :value="old('phosphorus')" />
                        <x-input-error :messages="$errors->get('phosphorus')" />
                    </div>
                    <div>
                        <x-input-label for="potassium" value="Potasio (mmol/L)" />
                        <x-text-input id="potassium" name="potassium" type="number" step="0.01" min="0" :value="old('potassium')" />
                        <x-input-error :messages="$errors->get('potassium')" />
                    </div>
                    <div>
                        <x-input-label for="sodium" value="Sodio (mmol/L)" />
                        <x-text-input id="sodium" name="sodium" type="number" step="0.01" min="0" :value="old('sodium')" />
                        <x-input-error :messages="$errors->get('sodium')" />
                    </div>
                    <div>
                        <x-input-label for="bicarbonate" value="Bicarbonato (mmol/L)" />
                        <x-text-input id="bicarbonate" name="bicarbonate" type="number" step="0.01" min="0" :value="old('bicarbonate')" />
                        <x-input-error :messages="$errors->get('bicarbonate')" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección D: Urianálisis</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="urine_density" value="Densidad Urinaria (1.000–1.100)" />
                        <x-text-input id="urine_density" name="urine_density" type="text" :value="old('urine_density')" placeholder="Ej: 1.025" />
                        <p class="mt-1 text-xs text-slate-500">Rango válido: 1.000 a 1.100</p>
                        <x-input-error :messages="$errors->get('urine_density')" />
                    </div>
                    <div>
                        <x-input-label for="proteinuria" value="Proteinuria" />
                        <select id="proteinuria" name="proteinuria" required
                                class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">
                            <option value="unknown" {{ old('proteinuria', 'unknown') === 'unknown' ? 'selected' : '' }}>Desconocido</option>
                            <option value="yes" {{ old('proteinuria') === 'yes' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ old('proteinuria') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                        <x-input-error :messages="$errors->get('proteinuria')" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección E: Estado Fisiológico y Clínico</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <x-input-label for="physiological_status" value="Estado Fisiológico" />
                        <select id="physiological_status" name="physiological_status" required
                                class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">
                            <option value="normal" {{ old('physiological_status', 'normal') === 'normal' ? 'selected' : '' }}>Adulto Normal</option>
                            <option value="gestation" {{ old('physiological_status') === 'gestation' ? 'selected' : '' }}>Gestación (Últ. tercio)</option>
                            <option value="lactation" {{ old('physiological_status') === 'lactation' ? 'selected' : '' }}>Lactancia</option>
                            <option value="growth" {{ old('physiological_status') === 'growth' ? 'selected' : '' }}>Crecimiento</option>
                            <option value="weight_loss" {{ old('physiological_status') === 'weight_loss' ? 'selected' : '' }}>Pérdida de Peso</option>
                            <option value="weight_gain" {{ old('physiological_status') === 'weight_gain' ? 'selected' : '' }}>Ganancia de Peso</option>
                            <option value="critical_care" {{ old('physiological_status') === 'critical_care' ? 'selected' : '' }}>Cuidados Críticos</option>
                        </select>
                        <x-input-error :messages="$errors->get('physiological_status')" />
                    </div>

                    <div>
                        <x-input-label for="appetite" value="Apetito" />
                        <select id="appetite" name="appetite" required
                                class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">
                            <option value="" disabled {{ old('appetite') ? '' : 'selected' }}>Selecciona...</option>
                            <option value="normal" {{ old('appetite') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="decreased" {{ old('appetite') === 'decreased' ? 'selected' : '' }}>Disminuido</option>
                            <option value="none" {{ old('appetite') === 'none' ? 'selected' : '' }}>Nulo</option>
                        </select>
                        <x-input-error :messages="$errors->get('appetite')" />
                    </div>

                    <div>
                        <x-input-label for="activity_level" value="Actividad" />
                        <select id="activity_level" name="activity_level" required
                                class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">
                            <option value="" disabled {{ old('activity_level') ? '' : 'selected' }}>Selecciona...</option>
                            <option value="low" {{ old('activity_level') === 'low' ? 'selected' : '' }}>Baja (Sedentario)</option>
                            <option value="medium" {{ old('activity_level') === 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ old('activity_level') === 'high' ? 'selected' : '' }}>Alta (Trabajo mod.)</option>
                        </select>
                        <x-input-error :messages="$errors->get('activity_level')" />
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 p-6 sm:p-8">
                <h3 class="text-base font-semibold text-slate-900 mb-4">Sección F: Notas adicionales</h3>
                <div>
                    <x-input-label for="special_considerations" value="Notas" />
                    <textarea id="special_considerations"
                              name="special_considerations"
                              rows="5"
                              class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition">{{ old('special_considerations') }}</textarea>
                    <x-input-error :messages="$errors->get('special_considerations')" />
                </div>
            </div>

            <div class="flex items-center gap-3 pb-2">
                <x-primary-button>
                    Guardar Historia Médica
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>

