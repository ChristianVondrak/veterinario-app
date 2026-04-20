<x-guest-layout>
    <x-slot name="title">Iniciar Sesión</x-slot>
    <x-slot name="subtitle">Accede a tu cuenta de VetNutri AI</x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 p-4 bg-teal-50 border border-teal-200 rounded-lg text-teal-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" 
                class="block mt-1 w-full" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                autocomplete="username"
                placeholder="tu@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" 
                class="block mt-1 w-full"
                type="password"
                name="password"
                required 
                autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" 
                    type="checkbox" 
                    class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500 focus:ring-offset-0" 
                    name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-teal-600 hover:text-teal-700 font-medium transition" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <x-primary-button class="w-full">
                {{ __('Iniciar Sesión') }}
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="text-center pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-600">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" class="font-semibold text-teal-600 hover:text-teal-700 transition">
                    Regístrate aquí
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
