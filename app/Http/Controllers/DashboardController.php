<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalPatients = Patient::count();

        $criticalPatients = Patient::query()
            ->with(['medicalRecords' => fn ($q) => $q->orderByDesc('evaluated_at')->limit(1)])
            ->get()
            ->filter(fn (Patient $p) => in_array($p->medicalRecords->first()?->iris_stage, ['III', 'IV'], true));

        $recentRecords = MedicalRecord::query()
            ->with('patient')
            ->latest('evaluated_at')
            ->limit(5)
            ->get();

        $irisStats = MedicalRecord::query()
            ->whereNotNull('iris_stage')
            ->selectRaw('iris_stage, count(*) as total')
            ->groupBy('iris_stage')
            ->pluck('total', 'iris_stage')
            ->all();

        $irisStats = array_merge(['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0], $irisStats);
        $hasIrisData = array_sum($irisStats) > 0;

        return view('dashboard', [
            'totalPatients' => $totalPatients,
            'criticalPatients' => $criticalPatients,
            'recentRecords' => $recentRecords,
            'irisStats' => $irisStats,
            'hasIrisData' => $hasIrisData,
        ]);
    }
}
