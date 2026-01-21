<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'VetNutri AI') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-slate-50 font-sans text-slate-800">

    <nav class="w-full bg-white/80 backdrop-blur-md border-b border-slate-200 fixed top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span class="font-bold text-xl tracking-tight text-slate-900">VetNutri<span class="text-teal-600">AI</span></span>
                </div>
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-teal-600">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-teal-600">Entrar</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2 rounded-full bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition">Registrarse</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <section class="pt-28 pb-12 lg:pt-36 lg:pb-20 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div class="text-center lg:text-left z-10">
                    <div class="inline-flex items-center px-3 py-1 rounded-full border border-teal-100 bg-teal-50 text-teal-700 text-sm font-medium mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-teal-500 mr-2 animate-pulse"></span>
                        Tecnología IRIS & AI Integrada
                    </div>
                    <h1 class="text-4xl lg:text-5xl xl:text-6xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight">
                        Nutrición Clínica <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-emerald-600">Potenciada por IA</span>
                    </h1>
                    <p class="mt-4 text-lg text-slate-500 mb-8 max-w-lg mx-auto lg:mx-0">
                        Genera planes nutricionales exactos para pacientes con Insuficiencia Renal Crónica en segundos. Automatiza la complejidad matemática.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                        <a href="{{ route('register') }}" class="px-8 py-3 rounded-lg bg-teal-600 text-white font-semibold shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition transform hover:-translate-y-1 text-center">
                            Comenzar Ahora
                        </a>
                        <a href="#features" class="px-8 py-3 rounded-lg bg-white border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition text-center">
                            Ver Demo
                        </a>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-lg lg:max-w-none" x-data="{ step: 1 }" x-init="setInterval(() => { step = step < 3 ? step + 1 : 1 }, 3500)">
                    
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl ring-1 ring-slate-900/10 group aspect-[4/3] lg:aspect-square object-cover">
                        <img 
                            src="https://images.unsplash.com/photo-1599443015574-be5fe8a05783?q=80&w=2070&auto=format&fit=crop" 
                            alt="Veterinario usando tablet" 
                            class="w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-in-out"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/30 to-transparent pointer-events-none"></div>
                    </div>

                    <div class="absolute bottom-6 -left-4 md:-left-12 w-80">
                        
                        <div x-show="step === 1" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-4"
                             class="bg-white rounded-xl p-4 shadow-xl border border-slate-100 absolute bottom-0 left-0 w-full">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 animate-spin-slow">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Paso 1/3</p>
                                    <p class="text-slate-800 font-semibold">Analizando Perfil Renal...</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 2" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-4"
                             class="bg-white rounded-xl p-4 shadow-xl border border-slate-100 absolute bottom-0 left-0 w-full">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                                </div>
                                <div class="w-full">
                                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Paso 2/3: Calculando</p>
                                    <div class="w-full bg-slate-100 rounded-full h-2">
                                        <div class="bg-amber-500 h-2 rounded-full animate-[width_2s_ease-in-out_infinite]" style="width: 70%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 3" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-4"
                             class="bg-white rounded-xl p-4 shadow-xl border border-slate-100 absolute bottom-0 left-0 w-full border-l-4 border-l-teal-500">
                            <div class="flex items-start gap-4">
                                <div class="h-10 w-10 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 shrink-0">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-teal-600 uppercase font-bold tracking-wider mb-0.5">Análisis Completado</p>
                                    <h4 class="text-slate-900 font-bold text-base">Dieta Renal Generada</h4>
                                    <p class="text-slate-500 text-xs mt-1">Paciente: "Rocky" • IRIS II</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base text-teal-600 font-semibold tracking-wide uppercase">Funcionalidades</h2>
                <p class="mt-2 text-3xl leading-8 font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Todo lo que necesitas para el manejo IRC
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition border border-slate-100">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Historial Clínico Renal</h3>
                    <p class="text-slate-500">Registro detallado de valores séricos (Fósforo, Potasio, Sodio) y clasificación automática del estadio IRIS.</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg ring-1 ring-teal-500/20 relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-teal-500 text-white text-xs px-2 py-1 rounded-bl-lg font-bold">IA Powered</div>
                    <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Generador de Dietas IA</h3>
                    <p class="text-slate-500">Algoritmos que calculan Kcal y macronutrientes al instante. Recalcula excluyendo ingredientes en segundos.</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition border border-slate-100">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Exportación PDF</h3>
                    <p class="text-slate-500">Entrega planes nutricionales claros y profesionales listos para imprimir o enviar por correo.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-200 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
                <span class="font-bold text-xl text-slate-900">VetNutri<span class="text-teal-600">AI</span></span>
                <p class="text-sm text-slate-500 mt-1">Gestión avanzada para la salud animal.</p>
            </div>
            <div class="text-slate-400 text-sm">
                &copy; {{ date('Y') }} Vondrak Studio. Todos los derechos reservados.
            </div>
        </div>
    </footer>
</body>
</html>