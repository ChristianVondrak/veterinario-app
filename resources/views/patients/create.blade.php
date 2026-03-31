<x-app-layout>
    <x-slot name="title">Nuevo Paciente</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            Registrar Paciente
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">
            Nuevo perro (VetNutri AI)
        </p>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-200 rounded-xl text-teal-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Datos del paciente</h3>
                <p class="text-sm text-slate-600">Completa la información para registrar un nuevo perro.</p>
            </div>

            <form method="POST" action="{{ route('patients.store') }}" class="p-6 sm:p-8 space-y-6">
                @csrf

                <div>
                    <x-input-label for="name" value="Nombre" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        :value="old('name')"
                        required
                        autofocus
                        placeholder="Ej. Rocky"
                    />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="breed" value="Raza (opcional)" />
                    <x-text-input
                        id="breed"
                        name="breed"
                        type="text"
                        :value="old('breed')"
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
                            <option value="" disabled {{ old('sex') ? '' : 'selected' }}>Selecciona...</option>
                            <option value="male" {{ old('sex') === 'male' ? 'selected' : '' }}>Macho</option>
                            <option value="female" {{ old('sex') === 'female' ? 'selected' : '' }}>Hembra</option>
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
                            <option value="" disabled {{ old('reproductive_status') ? '' : 'selected' }}>Selecciona...</option>
                            <option value="intact" {{ old('reproductive_status') === 'intact' ? 'selected' : '' }}>Entero</option>
                            <option value="neutered" {{ old('reproductive_status') === 'neutered' ? 'selected' : '' }}>Esterilizado</option>
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
                        min="0"
                        step="1"
                        :value="old('age_years')"
                        required
                        placeholder="Ej. 5"
                    />
                    <p class="mt-1 text-xs text-slate-500">
                        Usaremos esta edad para calcular automáticamente la fecha de nacimiento.
                    </p>
                    <x-input-error :messages="$errors->get('age_years')" />
                </div>

                <div class="pt-2">
                    <x-primary-button>
                        Guardar paciente
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

