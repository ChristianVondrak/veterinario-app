<x-app-layout>
    <x-slot name="title">Editar Paciente</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            Editar Paciente
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">
            {{ $patient->name }}
        </p>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('patients.index') }}"
               class="text-sm font-semibold text-teal-600 hover:text-teal-700 transition">
                ← Volver a pacientes
            </a>
        </div>

        <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Datos del paciente</h3>
                <p class="text-sm text-slate-600">Modifica la información y guarda los cambios.</p>
            </div>

            <form method="POST" action="{{ route('patients.update', $patient) }}" class="p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" value="Nombre" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        :value="old('name', $patient->name)"
                        required
                        autofocus
                    />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="breed" value="Raza (opcional)" />
                    <x-text-input
                        id="breed"
                        name="breed"
                        type="text"
                        :value="old('breed', $patient->breed)"
                        placeholder="Ej. Labrador"
                    />
                    <x-input-error :messages="$errors->get('breed')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="sex" value="Sexo" />
                        <select
                            id="sex"
                            name="sex"
                            required
                            class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition"
                        >
                            <option value="male" {{ old('sex', $patient->sex) === 'male' ? 'selected' : '' }}>Macho</option>
                            <option value="female" {{ old('sex', $patient->sex) === 'female' ? 'selected' : '' }}>Hembra</option>
                        </select>
                        <x-input-error :messages="$errors->get('sex')" />
                    </div>

                    <div>
                        <x-input-label for="reproductive_status" value="Estado reproductivo" />
                        <select
                            id="reproductive_status"
                            name="reproductive_status"
                            required
                            class="block w-full rounded-lg border-slate-300 bg-white text-slate-900 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition"
                        >
                            <option value="intact" {{ old('reproductive_status', $patient->reproductive_status) === 'intact' ? 'selected' : '' }}>Entero</option>
                            <option value="neutered" {{ old('reproductive_status', $patient->reproductive_status) === 'neutered' ? 'selected' : '' }}>Esterilizado</option>
                        </select>
                        <x-input-error :messages="$errors->get('reproductive_status')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="age_years" value="Edad (en años)" />
                    <x-text-input
                        id="age_years"
                        name="age_years"
                        type="number"
                        min="1"
                        max="30"
                        step="1"
                        :value="old('age_years', $ageYears)"
                        required
                    />
                    <p class="mt-1 text-xs text-slate-500">
                        Actualizaremos la fecha de nacimiento en base a esta edad.
                    </p>
                    <x-input-error :messages="$errors->get('age_years')" />
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <x-primary-button>
                        Guardar cambios
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

