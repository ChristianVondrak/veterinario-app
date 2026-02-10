<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-tight">
                    Pacientes
                </h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Lista de perros registrados
                </p>
            </div>

            <a href="{{ route('patients.create') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">
                + Registrar Paciente
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 p-4 bg-teal-50 border border-teal-200 rounded-xl text-teal-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-xl sm:rounded-2xl border border-slate-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                @if ($patients->count() === 0)
                    <div class="text-center py-12">
                        <div class="mx-auto w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Aún no hay pacientes</h3>
                        <p class="text-sm text-slate-500 mt-1">Registra tu primer perro para comenzar.</p>
                        <div class="mt-6">
                            <a href="{{ route('patients.create') }}"
                               class="inline-flex items-center px-6 py-3 rounded-lg bg-teal-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition">
                                Registrar Paciente
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead>
                                <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-3 pr-4">Nombre</th>
                                    <th class="py-3 pr-4">Raza</th>
                                    <th class="py-3 pr-4">Sexo</th>
                                    <th class="py-3 pr-4">Estado</th>
                                    <th class="py-3 pr-4">Edad</th>
                                    <th class="py-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($patients as $patient)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 pr-4 font-semibold text-slate-900">
                                            {{ $patient->name }}
                                        </td>
                                        <td class="py-4 pr-4 text-slate-600">
                                            {{ $patient->breed ?? '—' }}
                                        </td>
                                        <td class="py-4 pr-4 text-slate-600">
                                            {{ $patient->sex === 'male' ? 'Macho' : 'Hembra' }}
                                        </td>
                                        <td class="py-4 pr-4 text-slate-600">
                                            {{ $patient->reproductive_status === 'intact' ? 'Entero' : 'Esterilizado' }}
                                        </td>
                                        <td class="py-4 pr-4 text-slate-600">
                                            {{ $patient->birth_date ? $patient->birth_date->age . ' años' : '—' }}
                                        </td>
                                        <td class="py-4 text-right whitespace-nowrap">
                                            <a href="{{ route('patients.show', $patient) }}"
                                               class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition">
                                                Ver Dashboard
                                            </a>
                                            <a href="{{ route('patients.medical-records.create', $patient) }}"
                                               class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-teal-700 hover:bg-teal-50 transition">
                                                Evaluación
                                            </a>
                                            <a href="{{ route('patients.edit', $patient) }}"
                                               class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:text-teal-600 hover:bg-slate-50 transition">
                                                Editar
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('patients.destroy', $patient) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('¿Eliminar este paciente?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50 transition">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $patients->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

