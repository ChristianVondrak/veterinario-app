<x-guest-layout>
    @php
        $title = 'Verificar correo';
        $subtitle = 'Comprueba tu dirección de correo electrónico';
    @endphp
    <div class="mb-4 text-sm text-slate-600">
        Gracias por registrarte. Para continuar, verifica tu correo haciendo clic en el enlace que te enviamos. Si no lo recibiste, podemos enviarte otro.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-emerald-600">
            Se ha enviado un nuevo enlace de verificación al correo que indicaste al registrarte.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar correo de verificación
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-slate-600 hover:text-slate-900 rounded focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
