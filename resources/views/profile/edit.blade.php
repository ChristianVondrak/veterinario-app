<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-slate-900 leading-tight">
                Perfil
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Gestiona tu cuenta y seguridad
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="space-y-6">
            <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 sm:px-8 py-6 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-900">Información del perfil</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Actualiza los datos de tu perfil y tu correo electrónico.</p>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 sm:px-8 py-6 bg-gradient-to-r from-teal-50 to-emerald-50 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-900">Cambiar contraseña</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Usa una contraseña larga y aleatoria para mayor seguridad.</p>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-6 sm:px-8 py-6 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-900">Eliminar cuenta</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Al eliminar tu cuenta, todos sus datos se borrarán de forma permanente.</p>
                </div>
                <div class="p-6 sm:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
