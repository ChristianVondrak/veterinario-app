<x-app-layout>
    <x-slot name="title">Pacientes</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-tight">Pacientes</h2>
                <p class="text-sm text-slate-500 mt-0.5">Gestión clínica veterinaria</p>
            </div>
            <a href="{{ route('patients.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold shadow-md hover:bg-teal-700 transition gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Nuevo Paciente
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        @if (session('status'))
            @endif

        <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-visible">

            @if ($patients->count() === 0)
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">No hay pacientes registrados.</p>
                    <a href="{{ route('patients.create') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold shadow-md hover:bg-teal-700 transition gap-2">
                        Agregar primer paciente
                    </a>
                </div>
            @else

                {{-- ════════════════════════════════════════════════════════
                     VISTA MÓVIL: lista de tarjetas (visible solo en < sm)
                     ════════════════════════════════════════════════════════ --}}
                <ul class="divide-y divide-slate-100 sm:hidden">
                    @foreach ($patients as $patient)
                        <li class="p-4">
                            {{-- Cabecera de la tarjeta --}}
                            <a href="{{ route('patients.show', $patient) }}" class="flex items-center gap-3 mb-3">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold text-sm">
                                    {{ substr($patient->name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ $patient->name }}</p>
                                    <p class="text-xs text-slate-500">ID: #{{ $patient->id }}</p>
                                </div>
                                <svg class="ml-auto w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            {{-- Datos del paciente --}}
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600 mb-3 pl-1">
                                <div>
                                    <span class="font-medium text-slate-400 uppercase tracking-wide text-[10px]">Raza</span>
                                    <p class="text-slate-800">{{ $patient->breed ?? 'Mestizo' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-slate-400 uppercase tracking-wide text-[10px]">Sexo</span>
                                    <p class="text-slate-800">{{ $patient->sex === 'male' ? 'Macho' : 'Hembra' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-slate-400 uppercase tracking-wide text-[10px]">Edad</span>
                                    <p class="text-slate-800">{{ $patient->birth_date ? $patient->birth_date->age . ' años' : 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="font-medium text-slate-400 uppercase tracking-wide text-[10px]">Estado</span>
                                    <span class="mt-0.5 inline-flex px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $patient->reproductive_status === 'intact' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $patient->reproductive_status === 'intact' ? 'Entero' : 'Esterilizado' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Acciones rápidas --}}
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                                <a href="{{ route('patients.medical-records.create', $patient) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Evaluación
                                </a>
                                <a href="{{ route('patients.edit', $patient) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </a>
                                <button
                                    type="button"
                                    x-data
                                    x-on:click.prevent.stop="$dispatch('open-confirm', 'delete-patient-{{ $patient->id }}')"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                                    </svg>
                                    Eliminar
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- ════════════════════════════════════════════════════════
                     VISTA DESKTOP: tabla (oculta en móvil)
                     ════════════════════════════════════════════════════════ --}}
                <div class="hidden sm:block relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Paciente</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Raza / Sexo</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Edad</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado reproductivo</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($patients as $patient)
                                <tr class="hover:bg-slate-50 transition group cursor-pointer"
                                    role="link"
                                    tabindex="0"
                                    aria-label="Ver dashboard de {{ $patient->name }}"
                                    onclick="window.location='{{ route('patients.show', $patient) }}'"
                                    onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location='{{ route('patients.show', $patient) }}'; }">

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 font-bold">
                                                {{ substr($patient->name, 0, 2) }}
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-semibold text-slate-900 group-hover:text-teal-600 transition">{{ $patient->name }}</p>
                                                <div class="text-xs text-slate-500">ID: #{{ $patient->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-900">{{ $patient->breed ?? 'Mestizo' }}</div>
                                        <div class="text-xs text-slate-500">{{ $patient->sex === 'male' ? 'Macho' : 'Hembra' }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        {{ $patient->birth_date ? $patient->birth_date->age . ' años' : 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $patient->reproductive_status === 'intact' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $patient->reproductive_status === 'intact' ? 'Entero' : 'Esterilizado' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">

                                            <div class="relative group/tt">
                                                <a href="{{ route('patients.medical-records.create', $patient) }}"
                                                   onclick="event.stopPropagation()"
                                                   onkeydown="event.stopPropagation()"
                                                   aria-label="Nueva evaluación"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-full text-teal-600 transition hover:bg-teal-50 hover:text-teal-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </a>
                                                <span class="pointer-events-none absolute right-0 bottom-full mb-2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition group-hover/tt:opacity-100 group-focus-within/tt:opacity-100">Nueva evaluación</span>
                                            </div>

                                            <div class="relative group/tt">
                                                <a href="{{ route('patients.edit', $patient) }}"
                                                   onclick="event.stopPropagation()"
                                                   onkeydown="event.stopPropagation()"
                                                   aria-label="Editar paciente"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <span class="pointer-events-none absolute right-0 bottom-full mb-2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition group-hover/tt:opacity-100 group-focus-within/tt:opacity-100">Editar paciente</span>
                                            </div>

                                            <div class="relative group/tt">
                                                <button
                                                    type="button"
                                                    aria-label="Eliminar paciente"
                                                    x-data
                                                    x-on:click.prevent.stop="$dispatch('open-confirm', 'delete-patient-{{ $patient->id }}')"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-red-600 transition hover:bg-red-50 hover:text-red-700">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                                                    </svg>
                                                </button>
                                                <span class="pointer-events-none absolute right-0 bottom-full mb-2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-xs text-white opacity-0 shadow transition group-hover/tt:opacity-100 group-focus-within/tt:opacity-100">Eliminar paciente</span>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="px-4 sm:px-6 py-4 border-t border-slate-100 bg-slate-50">
                    {{ $patients->links() }}
                </div>
            @endif
        </div>
    </div>

    @foreach($patients as $patient)
        <x-confirm-dialog
            id="delete-patient-{{ $patient->id }}"
            title="Eliminar Paciente"
            :body="'¿Estás seguro de que deseas eliminar a <strong>' . e($patient->name) . '</strong>? Esta acción borrará todas sus historias clínicas y dietas de forma permanente y no se podrá deshacer.'"
            confirm-label="Eliminar definitivamente"
            :action="route('patients.destroy', $patient)"
        />
    @endforeach
</x-app-layout>