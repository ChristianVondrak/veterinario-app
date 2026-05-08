<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Plan Nutricional - {{ $patient->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
            padding: 32px 36px;
            line-height: 1.5;
        }

        /* ── Header ── */
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .header p {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
        }
        .header p strong { color: #334155; }

        /* ── Sections ── */
        .section {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 14px;
            overflow: hidden;
        }
        .section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 10px 16px 6px;
            border-bottom: 1px solid #f1f5f9;
        }
        .section-body { padding: 10px 16px 14px; }

        /* ── Alerts ── */
        .alert-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .alert-box p {
            font-size: 10px;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 3px;
        }

        /* ── Ingredients grid ── */
        .ing-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }
        .ing-row { display: table-row; }
        .ing-cell {
            display: table-cell;
            width: 50%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .ing-name { font-weight: 700; color: #1e293b; }
        .ing-meta { font-size: 10px; color: #64748b; margin-top: 2px; }

        /* ── Nutrient table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead tr { background: #f1f5f9; }
        thead th {
            text-align: left;
            padding: 7px 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.06em;
        }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }
        tbody tr { border-top: 1px solid #f1f5f9; }
        tbody td {
            padding: 7px 10px;
            color: #334155;
        }
        tbody td.right { text-align: right; }
        tbody td.center { text-align: center; }
        tbody td.bold { font-weight: 700; color: #0f172a; }

        /* ── Status badges ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
        }
        .badge-ok       { background: #d1fae5; color: #065f46; }
        .badge-leve     { background: #fef3c7; color: #92400e; }
        .badge-moderado { background: #ffedd5; color: #9a3412; }
        .badge-critico  { background: #fee2e2; color: #991b1b; }
        .badge-exceso   { background: #ffe4e6; color: #9f1239; }

        /* ── Steps (preparation) ── */
        .step-list { list-style: none; padding: 0; }
        .step-item {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .step-num {
            display: table-cell;
            width: 22px;
            height: 22px;
            background: #1e293b;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            border-radius: 50%;
        }
        .step-text {
            display: table-cell;
            padding-left: 10px;
            vertical-align: middle;
            color: #334155;
            font-size: 10px;
        }

        /* ── Tips ── */
        .tip-item {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .tip-check {
            display: table-cell;
            width: 16px;
            color: #0d9488;
            font-weight: 700;
            vertical-align: top;
        }
        .tip-text {
            display: table-cell;
            padding-left: 6px;
            color: #334155;
            font-size: 10px;
        }

        /* ── Background for tip section ── */
        .section-body.tips-bg { background: #f8fafc; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

@php
    $c       = $diet->content ?? [];
    $justif  = $c['justificacion_clinica'] ?? ($c['description'] ?? null);
    $ingreds = is_array($c['ingredientes']        ?? null) ? $c['ingredientes']        : [];
    $defics  = is_array($c['deficiencias']        ?? null) ? $c['deficiencias']        : [];
    $alertas = is_array($c['alertas_iris']        ?? null) ? $c['alertas_iris']        : [];
    $steps   = is_array($c['instrucciones_preparacion'] ?? null) ? $c['instrucciones_preparacion'] : [];
    $tips    = is_array($c['tips']                ?? null) ? $c['tips']                : [];

    $badgeClass = [
        'ADECUADO' => 'badge-ok',
        'LEVE'     => 'badge-leve',
        'MODERADO' => 'badge-moderado',
        'CRÍTICO'  => 'badge-critico',
        'EXCESO'   => 'badge-exceso',
    ];
@endphp

{{-- Header --}}
<div class="header">
    <h1>Plan Nutricional Renal</h1>
    <p>Paciente: <strong>{{ $patient->name }}</strong> &nbsp;|&nbsp; Fecha: <strong>{{ $diet->created_at->format('d/m/Y H:i') }}</strong></p>
</div>

{{-- Alertas IRIS --}}
@if (!empty($alertas))
    <div class="alert-box">
        @foreach ($alertas as $alerta)
            @php
                if (str_starts_with($alerta, '[OK]')) {
                    $pLabel = 'OK';   $pText = trim(substr($alerta, 4));
                } elseif (str_starts_with($alerta, '[INFO]')) {
                    $pLabel = 'INFO'; $pText = trim(substr($alerta, 6));
                } elseif (str_starts_with($alerta, '[!]')) {
                    $pLabel = '!';    $pText = trim(substr($alerta, 3));
                } elseif (preg_match('/^\[IRIS [IVX]+\]/', $alerta, $pm)) {
                    $pLabel = trim($pm[0], '[]');
                    $pText  = trim(substr($alerta, strlen($pm[0])));
                } else {
                    $pLabel = null;   $pText = $alerta;
                }
            @endphp
            <p>
                @if ($pLabel) <strong>[{{ $pLabel }}]</strong> @endif
                {{ $pText }}
            </p>
        @endforeach
    </div>
@endif

{{-- Justificación Clínica --}}
@if ($justif)
    <div class="section">
        <div class="section-title">Justificación Clínica</div>
        <div class="section-body">
            <p style="color:#334155;font-size:10.5px;">{{ $justif }}</p>
        </div>
    </div>
@endif

{{-- Ingredientes --}}
@if (!empty($ingreds))
    <div class="section">
        <div class="section-title">Ingredientes y Aporte</div>
        <div class="section-body" style="padding-bottom:6px;">
            <table style="border-collapse:separate;border-spacing:5px;">
                <tbody>
                    @foreach (array_chunk($ingreds, 2) as $row)
                        <tr>
                            @foreach ($row as $ing)
                                <td style="width:50%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:5px;padding:8px 12px;vertical-align:middle;">
                                    <div class="ing-name">{{ $ing['name'] ?? '—' }}</div>
                                    <div class="ing-meta">
                                        {{ $ing['grams'] ?? '?' }} g
                                        @isset($ing['kcal']) — {{ $ing['kcal'] }} kcal @endisset
                                    </div>
                                </td>
                            @endforeach
                            {{-- Pad last row if odd --}}
                            @if (count($row) === 1)
                                <td style="width:50%;"></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Estado Nutricional vs. NRC --}}
@if (!empty($defics))
    <div class="section">
        <div class="section-title">Estado Nutricional vs. NRC</div>
        <div class="section-body" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>Nutriente</th>
                        <th>Indicativo NRC</th>
                        <th class="right">Requerido</th>
                        <th class="right">Aporte</th>
                        <th class="center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($defics as $d)
                        <tr>
                            <td class="bold">{{ $d['nutriente'] }}</td>
                            <td>{{ $d['indicativo'] ?? '—' }}</td>
                            <td class="right">{{ $d['requerido'] }}</td>
                            <td class="right bold">{{ $d['aporte'] }}</td>
                            <td class="center">
                                <span class="badge {{ $badgeClass[$d['estado']] ?? 'badge-leve' }}">
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

{{-- Instrucciones de Preparación --}}
@if (!empty($steps))
    <div class="section">
        <div class="section-title">Instrucciones de Preparación</div>
        <div class="section-body">
            <ul class="step-list">
                @foreach ($steps as $n => $step)
                    <li class="step-item">
                        <span class="step-num">{{ $n + 1 }}</span>
                        <span class="step-text">{{ $step }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- Recomendaciones Adicionales --}}
@if (!empty($tips))
    <div class="section">
        <div class="section-title">Recomendaciones Adicionales</div>
        <div class="section-body tips-bg">
            @foreach ($tips as $tip)
                <div class="tip-item">
                    <span class="tip-check">+</span>
                    <span class="tip-text">{{ $tip }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="footer">
    Documento generado por VetNutri AI — Sistema de Nutrición Veterinaria Avanzada
</div>

</body>
</html>
