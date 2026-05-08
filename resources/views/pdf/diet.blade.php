<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dieta - {{ $patient->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white p-8 antialiased text-slate-800">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8 border-b border-slate-200 pb-4">
            <h1 class="text-3xl font-bold text-slate-900">Plan Nutricional Renal</h1>
            <p class="text-sm text-slate-500 mt-1">Paciente: <span class="font-semibold text-slate-700">{{ $patient->name }}</span> | Fecha de generación: {{ $diet->created_at->format('d/m/Y H:i') }}</p>
        </div>
        
        @php
            $c       = $diet->content ?? [];
            $justif  = $c['justificacion_clinica'] ?? ($c['description'] ?? null);
            $ingreds = is_array($c['ingredientes'] ?? null) ? $c['ingredientes'] : [];
            $aporte  = is_array($c['aporte_nutricional'] ?? null) ? $c['aporte_nutricional'] : ($c['aporte_total'] ?? []);
            $defics  = is_array($c['deficiencias'] ?? null) ? $c['deficiencias'] : [];
            $alertas = is_array($c['alertas_iris'] ?? null) ? $c['alertas_iris'] : [];
            $steps   = is_array($c['instrucciones_preparacion'] ?? null) ? $c['instrucciones_preparacion'] : [];
            $daily   = is_array($c['daily_plan'] ?? null) ? $c['daily_plan'] : [];
            $tips    = is_array($c['tips'] ?? null) ? $c['tips'] : [];
            
            $badgeColors = [
                'ADECUADO' => 'bg-emerald-100 text-emerald-700',
                'LEVE'     => 'bg-amber-100 text-amber-700',
                'MODERADO' => 'bg-orange-100 text-orange-700',
                'CRÍTICO'  => 'bg-red-100 text-red-700',
                'EXCESO'   => 'bg-rose-100 text-rose-700'
            ];
        @endphp

        <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden">
            
            {{-- Alertas --}}
            @if (!empty($alertas))
                <div class="px-6 py-5 bg-amber-50 space-y-2">
                    @foreach ($alertas as $alerta)
                        <p class="text-sm text-amber-800 leading-relaxed font-medium">{{ $alerta }}</p>
                    @endforeach
                </div>
            @endif
            
            {{-- Justificación --}}
            @if ($justif)
                <div class="px-6 py-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-bold mb-2">Justificación Clínica</p>
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $justif }}</p>
                </div>
            @endif

            {{-- Ingredientes --}}
            @if (!empty($ingreds))
                <div class="px-6 py-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-bold mb-3">Ingredientes y Aporte</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($ingreds as $ing)
                            <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 gap-2">
                                <span class="text-sm text-slate-800 font-semibold">{{ $ing['name'] ?? '—' }}</span>
                                <div class="flex-shrink-0 text-xs text-slate-500 text-right">
                                    <span class="font-bold text-slate-700 text-sm">{{ $ing['grams'] ?? '?' }}g</span>
                                    @if (isset($ing['kcal'])) <br> {{ $ing['kcal'] }} kcal @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Deficiencias (Tabla NRC) --}}
            @if (!empty($defics))
                <div class="px-6 py-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-bold mb-3">Estado Nutricional vs. NRC</p>
                    <div class="rounded-lg border border-slate-200 overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-100">
                                <tr class="text-xs font-bold uppercase tracking-wider text-slate-600">
                                    <th class="py-3 px-4 text-left">Nutriente</th>
                                    <th class="py-3 px-4 text-left">Indicativo NRC</th>
                                    <th class="py-3 px-4 text-right">Requerido</th>
                                    <th class="py-3 px-4 text-right">Aporte</th>
                                    <th class="py-3 px-4 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($defics as $d)
                                    @php $cls = $badgeColors[$d['estado']] ?? 'bg-slate-100 text-slate-600'; @endphp
                                    <tr>
                                        <td class="py-3 px-4 text-slate-800 font-semibold">{{ $d['nutriente'] }}</td>
                                        <td class="py-3 px-4 text-left text-slate-600">{{ $d['indicativo'] ?? '—' }}</td>
                                        <td class="py-3 px-4 text-right text-slate-600">{{ $d['requerido'] }}</td>
                                        <td class="py-3 px-4 text-right text-slate-900 font-bold">{{ $d['aporte'] }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $cls }}">
                                                {{ $d['estado'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            
            {{-- Preparación --}}
            @if (!empty($steps))
                <div class="px-6 py-5">
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-bold mb-3">Instrucciones de Preparación</p>
                    <ol class="space-y-3">
                        @foreach ($steps as $n => $step)
                            <li class="flex gap-3 text-sm text-slate-700">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-800 text-white text-xs font-bold flex items-center justify-center">{{ $n + 1 }}</span>
                                <span class="pt-0.5 leading-relaxed">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif

            {{-- Tips --}}
            @if (!empty($tips))
                <div class="px-6 py-5 bg-slate-50">
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-bold mb-3">Recomendaciones Adicionales</p>
                    <ul class="space-y-2">
                        @foreach ($tips as $tip)
                            <li class="flex gap-2 text-sm text-slate-700">
                                <span class="text-teal-600 font-bold">✓</span>
                                <span class="leading-relaxed">{{ $tip }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
        
        <div class="mt-8 text-center text-xs text-slate-400">
            Documento generado por VetNutri AI - Sistema de Nutrición Veterinaria Avanzada
        </div>
    </div>
</body>
</html>
