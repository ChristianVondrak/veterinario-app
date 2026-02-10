<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex-1 max-w-xl">
            <label for="dashboard-search" class="sr-only">Buscar paciente</label>
            <div class="relative">
                <input
                    type="text"
                    id="dashboard-search"
                    wire:model.live.debounce.450ms="query"
                    placeholder="Buscar paciente..."
                    class="block w-full rounded-xl border-slate-300 bg-white text-slate-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 placeholder:text-slate-400 transition pr-24"
                    aria-label="Buscar paciente"
                >

                @if($query !== '')
                    <button
                        type="button"
                        wire:click="clearSearch"
                        class="absolute inset-y-1.5 right-1.5 inline-flex items-center px-4 rounded-lg bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition"
                    >
                        Limpiar
                    </button>
                @endif
            </div>
        </div>

        <a href="{{ route('patients.create') }}"
           class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-sky-600 text-white font-semibold shadow-lg shadow-sky-500/30 hover:bg-sky-700 transition shrink-0">
            Nuevo Paciente
        </a>
    </div>

    @if($query !== '')
        <section wire:key="search-results-{{ md5($query) }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70">
                <h3 class="text-base font-semibold text-slate-900">Resultados de búsqueda</h3>
                <p class="text-sm text-slate-500">"{{ $query }}" · {{ $results->count() }} resultado(s)</p>
            </div>

            <div class="p-6">
                @if($results->isEmpty())
                    <p class="text-sm text-slate-500">No se encontraron pacientes con ese criterio.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach($results as $patient)
                            <a href="{{ route('patients.show', $patient) }}"
                               class="block p-4 rounded-xl border border-slate-200 hover:border-sky-300 hover:bg-sky-50/40 transition">
                                <p class="font-semibold text-slate-900 truncate">{{ $patient->name }}</p>
                                <p class="text-sm text-slate-600 mt-0.5 truncate">{{ $patient->breed ?? 'Sin raza' }}</p>
                                <p class="text-xs text-slate-500 mt-2">
                                    {{ $patient->birth_date ? $patient->birth_date->age . ' años' : 'Edad sin dato' }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>

