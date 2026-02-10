<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-slate-900 leading-tight">
            {{ __('Dashboard') }}
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ __('Bienvenido a VetNutri AI') }}</p>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-slate-100">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __("Has iniciado sesión correctamente") }}</h3>
                        <p class="text-sm text-slate-500">{{ __("Desde aquí podrás gestionar la nutrición clínica de tus pacientes.") }}</p>
                    </div>
                </div>
                <div class="border-t border-slate-100 pt-6">
                    <p class="text-slate-600">
                        {{ __("You're logged in!") }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
