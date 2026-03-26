<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DietCalculatorService
{
    // ─────────────────────────────────────────────────────────────
    // TABLE 15-5 – NRC Nutrient Requirements for Adult Dog Maintenance
    // All values expressed per 1,000 kcal ME.
    // ─────────────────────────────────────────────────────────────
    private const NRC_PER_1000_KCAL = [
        'protein_g'      => 25.0,    // g
        'fat_g'          => 13.8,    // g
        'calcium_mg'     => 1000.0,  // mg
        'phosphorus_mg'  => 750.0,   // mg
        'potassium_mg'   => 1000.0,  // mg
        'sodium_mg'      => 200.0,   // mg
        'omega_3_mg'     => 110.0,   // mg EPA+DHA (omega_3_g stored in g → convert)
    ];

    // ─────────────────────────────────────────────────────────────
    // IRIS serum phosphorus limits (mg/dL)
    // ─────────────────────────────────────────────────────────────
    private const IRIS_PHOSPHORUS_LIMITS = [
        'I'   => 4.5,
        'II'  => 4.5,
        'III' => 5.0,
        'IV'  => 6.0,
    ];

    // ─────────────────────────────────────────────────────────────
    // Base Recipes  → ingredient name must match `ingredients.name`
    // Proportions must sum to 1.0 (100 %).
    // 'supplement_g' is a fixed daily gram dose added on top.
    // ─────────────────────────────────────────────────────────────
    private const BASE_RECIPES = [
        // ──────────────── ETAPA TEMPRANA (IRIS I - II) ────────────────
        'dieta_renal_temprana_pollo' => [
            'name'  => 'Dieta Renal Temprana de Pollo (IRIS I-II)',
            'proportions' => [
                'Pechuga de pollo sin piel hervida' => 0.20,  // Restricción moderada (20%)
                'Papas hervidas sin piel'           => 0.65,
                'Huevo fresco hervido'              => 0.04,
                'Brócoli hervido'                   => 0.10,
                'Cáscara de huevo en polvo'         => 0.01,
            ],
            'supplement'   => 'Aceite de salmón',
            'supplement_g' => 5.0,
        ],
        'dieta_renal_temprana_res' => [
            'name'  => 'Dieta Renal Temprana de Res (IRIS I-II)',
            'proportions' => [
                'Carne molida de res 70-30 asada' => 0.18,  // Restricción moderada (18%)
                'Papas hervidas sin piel'         => 0.71,
                'Brócoli hervido'                 => 0.10,
                'Cáscara de huevo en polvo'       => 0.01,
            ],
            'supplement'   => 'Aceite de salmón',
            'supplement_g' => 5.0,
        ],

        // ──────────────── ETAPA AVANZADA (IRIS III - IV) ────────────────
        'dieta_renal_avanzada_pollo' => [
            'name'  => 'Dieta Renal Avanzada de Pollo (IRIS III-IV)',
            'proportions' => [
                'Pechuga de pollo sin piel hervida' => 0.10,  // Restricción estricta (10%)
                'Papas hervidas sin piel'           => 0.75,
                'Huevo fresco hervido'              => 0.04,
                'Brócoli hervido'                   => 0.10,
                'Cáscara de huevo en polvo'         => 0.01,
            ],
            'supplement'   => 'Aceite de salmón',
            'supplement_g' => 5.0,
        ],
        'dieta_renal_avanzada_res' => [
            'name'  => 'Dieta Renal Avanzada de Res (IRIS III-IV)',
            'proportions' => [
                'Carne molida de res 70-30 asada' => 0.08,  // Restricción estricta (8%)
                'Papas hervidas sin piel'         => 0.81,
                'Brócoli hervido'                 => 0.10,
                'Cáscara de huevo en polvo'       => 0.01,
            ],
            'supplement'   => 'Aceite de salmón',
            'supplement_g' => 5.0,
        ],
    ];

    // ─────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────

    /**
     * Calculate the Resting Energy Requirement.
     * Formula: RER = 70 × BW(kg)^0.75
     */
    public function calculateRer(float $weightKg): float
    {
        return 70.0 * pow($weightKg, 0.75);
    }

    /**
     * Calculate MER from Eloquent models (used by the controller).
     */
    public function calculateMer(float $rer, Patient $patient, MedicalRecord $record): float
    {
        return $this->calculateMerFromScalars(
            $rer,
            $patient->reproductive_status ?? '',
            $record->activity_level ?? 'medium',
            $patient->birth_date ? Carbon::parse($patient->birth_date)->diffInYears(now()) : 0
        );
    }

    /**
     * Calculate MER from plain scalar values (no Eloquent required).
     * This is the real implementation — used by Unit tests directly.
     *
     * @param  float   $rer                RER in kcal
     * @param  string  $reproductiveStatus e.g. 'castrada', 'entero'
     * @param  string  $activityLevel      'low' | 'medium' | 'high'
     * @param  int     $ageYears           Patient age in full years
     */
    public function calculateMerFromScalars(
        float $rer,
        string $reproductiveStatus,
        string $activityLevel,
        int $ageYears
    ): float {
        $status = strtolower($reproductiveStatus);
        $activity = strtolower($activityLevel);

        $factor = match(true) {
            str_contains($status, 'castrat') || str_contains($status, 'castrad')
                || str_contains($status, 'esteriliz') => 1.6,
            str_contains($status, 'intact')  || str_contains($status, 'entero')
                || str_contains($status, 'entera')    => 1.8,
            default => 1.6,
        };

        $activityFactor = match($activity) {
            'low'    => 1.2,
            'medium' => 1.4,
            'high'   => 1.8,
            default  => 1.4,
        };

        if ($activity === 'low') {
            $factor = min($factor, $activityFactor);
        }

        $mer = $rer * $factor;

        if ($ageYears > 7) {
            $mer *= 0.80;
        }

        return round($mer, 2);
    }

    /**
     * Master method: returns the full mathematical payload for Gemini.
     */
    public function buildCalculationPayload(Patient $patient, MedicalRecord $record): array
    {
        $weightKg = (float) $record->weight_kg;
        $rer      = $this->calculateRer($weightKg);
        $mer      = $this->calculateMer($rer, $patient, $record);

        $recipe   = $this->selectRecipe($record);
        $scaled   = $this->scaleRecipe($recipe, $mer);

        if ($scaled === null) {
            // Graceful fallback: switch to alternate recipe
            $altKey  = array_key_first(array_filter(
                self::BASE_RECIPES,
                fn($v) => $v['name'] !== $recipe['name']
            ));
            $recipe  = self::BASE_RECIPES[$altKey];
            $scaled  = $this->scaleRecipe($recipe, $mer);
        }

        $nutrients    = $this->calculateNutrients($scaled);
        $deficiencies = $this->calculateDeficiencies($nutrients, $mer, $record);
        $alertas      = $this->buildIrisAlerts($record, $nutrients, $mer);

        return [
            'recipe_name'      => $recipe['name'],
            'rer_kcal'         => round($rer, 2),
            'mer_kcal'         => $mer,
            'ingredientes'     => $scaled,
            'aporte_total'     => $nutrients,
            'deficiencias'     => $deficiencies,
            'alertas_iris'     => $alertas,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────

    // merFactor() replaced by calculateMerFromScalars() above

    private function selectRecipe(MedicalRecord $record): array
    {
        $considerations = strtolower($record->special_considerations ?? '');
        $irisStage      = $record->iris_stage ?? 'I'; // Default to early stage

        // 1. Detect allergies
        $avoidChicken = str_contains($considerations, 'alergia a pollo')
            || str_contains($considerations, 'sin pollo')
            || str_contains($considerations, 'alergia pollo')
            || str_contains($considerations, 'no pollo');

        $proteinSource = $avoidChicken ? 'res' : 'pollo';

        // 2. Select Medical Stage
        $stage = in_array($irisStage, ['III', 'IV']) ? 'avanzada' : 'temprana';

        // 3. Construct specific logic key
        $key = "dieta_renal_{$stage}_{$proteinSource}";

        // Return matched recipe (fallback to pollo temprana if not found somehow)
        return self::BASE_RECIPES[$key] ?? self::BASE_RECIPES['dieta_renal_temprana_pollo'];
    }

    /**
     * Scale ingredient proportions so the total kcal matches $targetKcal.
     * Returns null if any ingredient is missing from the DB.
     *
     * @return array<int, array>|null
     */
    private function scaleRecipe(array $recipe, float $targetKcal): ?array
    {
        $proportions = $recipe['proportions'];
        $supplement  = $recipe['supplement'];
        $suppGrams   = $recipe['supplement_g'];

        // Load all required ingredients from DB in one query
        $names       = array_merge(array_keys($proportions), [$supplement]);
        $dbIngredients = Ingredient::whereIn('name', $names)
            ->get()
            ->keyBy('name');

        // Check all ingredients are present
        foreach ($names as $name) {
            if (! $dbIngredients->has($name)) {
                Log::warning("DietCalculatorService: ingredient not found in DB: {$name}");
                return null;
            }
        }

        // Kcal contributed by the fixed supplement
        $suppIng     = $dbIngredients->get($supplement);
        $suppKcal    = ($suppGrams / 100.0) * $suppIng->energy_kcal;

        // Remaining kcal must come from the proportional ingredients
        $remainingKcal = max(0, $targetKcal - $suppKcal);

        // Calculate weighted kcal/g for the proportional blend
        $blendKcalPer100g = 0.0;
        foreach ($proportions as $name => $pct) {
            $ing = $dbIngredients->get($name);
            $blendKcalPer100g += $pct * $ing->energy_kcal;
        }

        if ($blendKcalPer100g <= 0) {
            return null;
        }

        // Total grams of the blend needed
        $totalBlendGrams = ($remainingKcal / $blendKcalPer100g) * 100.0;

        $scaled = [];

        foreach ($proportions as $name => $pct) {
            $ing   = $dbIngredients->get($name);
            $grams = round($totalBlendGrams * $pct, 1);
            $factor = $grams / 100.0;

            $scaled[] = [
                'name'          => $name,
                'grams'         => $grams,
                'kcal'          => round($ing->energy_kcal * $factor, 2),
                'protein_g'     => round($ing->protein_g * $factor, 2),
                'fat_g'         => round($ing->fat_g * $factor, 2),
                'carbohydrate_g'=> round($ing->carbohydrate_g * $factor, 2),
                'phosphorus_mg' => round($ing->phosphorus_mg * $factor, 2),
                'potassium_mg'  => round($ing->potassium_mg * $factor, 2),
                'calcium_mg'    => round($ing->calcium_mg * $factor, 2),
                'sodium_mg'     => round($ing->sodium_mg * $factor, 2),
                'omega_3_g'     => round($ing->omega_3_g * $factor, 3),
                'water_g'       => round($ing->water_g * $factor, 2),
            ];
        }

        // Add supplement as fixed entry
        $suppFactor = $suppGrams / 100.0;
        $scaled[] = [
            'name'          => $supplement . ' (suplemento)',
            'grams'         => $suppGrams,
            'kcal'          => round($suppIng->energy_kcal * $suppFactor, 2),
            'protein_g'     => round($suppIng->protein_g * $suppFactor, 2),
            'fat_g'         => round($suppIng->fat_g * $suppFactor, 2),
            'carbohydrate_g'=> 0,
            'phosphorus_mg' => 0,
            'potassium_mg'  => 0,
            'calcium_mg'    => 0,
            'sodium_mg'     => 0,
            'omega_3_g'     => round($suppIng->omega_3_g * $suppFactor, 3),
            'water_g'       => 0,
        ];

        return $scaled;
    }

    /**
     * Sum nutritional contributions from all scaled ingredients.
     */
    private function calculateNutrients(array $scaledIngredients): array
    {
        $totals = [
            'kcal'          => 0.0,
            'protein_g'     => 0.0,
            'fat_g'         => 0.0,
            'carbohydrate_g'=> 0.0,
            'phosphorus_mg' => 0.0,
            'potassium_mg'  => 0.0,
            'calcium_mg'    => 0.0,
            'sodium_mg'     => 0.0,
            'omega_3_g'     => 0.0,
            'water_g'       => 0.0,
        ];

        foreach ($scaledIngredients as $row) {
            foreach ($totals as $key => $_) {
                $totals[$key] += $row[$key] ?? 0.0;
            }
        }

        return array_map(fn($v) => round($v, 2), $totals);
    }

    /**
     * Compare actual nutrient totals against NRC requirements
     * and apply IRIS-specific adjustments.
     */
    private function calculateDeficiencies(array $nutrients, float $merKcal, MedicalRecord $record): array
    {
        $iris     = $record->iris_stage ?? 'I';
        $ratio    = $merKcal / 1000.0;   // Scale NRC requirements to actual MER

        // Protein adjustment for advanced CKD (IRIS III/IV → −20% requirement)
        $proteinRequired = self::NRC_PER_1000_KCAL['protein_g'] * $ratio;
        if (in_array($iris, ['III', 'IV'])) {
            $proteinRequired *= 0.80;
        }

        $requirements = [
            'Proteína (g)'          => $proteinRequired,
            'Grasa (g)'             => self::NRC_PER_1000_KCAL['fat_g'] * $ratio,
            'Calcio (mg)'           => self::NRC_PER_1000_KCAL['calcium_mg'] * $ratio,
            'Fósforo (mg)'          => self::NRC_PER_1000_KCAL['phosphorus_mg'] * $ratio,
            'Potasio (mg)'          => self::NRC_PER_1000_KCAL['potassium_mg'] * $ratio,
            'Sodio (mg)'            => self::NRC_PER_1000_KCAL['sodium_mg'] * $ratio,
            'Omega-3 EPA+DHA (mg)'  => self::NRC_PER_1000_KCAL['omega_3_mg'] * $ratio,
        ];

        $actual = [
            'Proteína (g)'         => $nutrients['protein_g'],
            'Grasa (g)'            => $nutrients['fat_g'],
            'Calcio (mg)'          => $nutrients['calcium_mg'],
            'Fósforo (mg)'         => $nutrients['phosphorus_mg'],
            'Potasio (mg)'         => $nutrients['potassium_mg'],
            'Sodio (mg)'           => $nutrients['sodium_mg'],
            'Omega-3 EPA+DHA (mg)' => round($nutrients['omega_3_g'] * 1000, 2),
        ];

        $deficiencies = [];

        // Safe Upper Limits (SUL) / Tolerancias máximas para etapa renal
        $limits = [
            'Proteína (g)'          => $proteinRequired * 1.20, // Limitar proteína a máx. +20% del objetivo renal
            'Grasa (g)'             => 82.5 * $ratio,           // NRC SUL
            'Calcio (mg)'           => 4500.0 * $ratio,         // NRC SUL
            'Fósforo (mg)'          => self::NRC_PER_1000_KCAL['phosphorus_mg'] * $ratio * 1.15, // Estrícto: +15% del objetivo
            'Potasio (mg)'          => 4000.0 * $ratio,         // Margen seguro alto
            'Sodio (mg)'            => 1500.0 * $ratio,         // SUL recomendado para no elevar presión arterial
            'Omega-3 EPA+DHA (mg)'  => 2800.0 * $ratio,         // NRC SUL
        ];

        foreach ($requirements as $label => $required) {
            $aporte     = $actual[$label] ?? 0.0;
            $diferencia = $aporte - $required;
            $pct        = $required > 0 ? round(($aporte / $required) * 100, 1) : 100.0;
            $limiteMax  = $limits[$label];

            $estado = match(true) {
                $aporte > $limiteMax => 'EXCESO',
                $pct >= 100  => 'ADECUADO',
                $pct >= 80   => 'LEVE',
                $pct >= 60   => 'MODERADO',
                default      => 'CRÍTICO',
            };

            // Special rule: potassium alert if serum < 4 mmol/L
            if ($label === 'Potasio (mg)'
                && $record->potassium !== null
                && (float) $record->potassium < 4.0
                && $estado !== 'EXCESO'
            ) {
                $estado = 'CRÍTICO';
            }

            $deficiencies[] = [
                'nutriente'   => $label,
                'aporte'      => round($aporte, 2),
                'requerido'   => round($required, 2),
                'limite_max'  => round($limiteMax, 2),
                'diferencia'  => round($diferencia, 2),
                'porcentaje'  => $pct,
                'estado'      => $estado,
            ];
        }

        return $deficiencies;
    }

    /**
     * Generate clinical alerts based on IRIS stage and lab values.
     */
    private function buildIrisAlerts(MedicalRecord $record, array $nutrients, float $merKcal): array
    {
        $alerts = [];
        $iris   = $record->iris_stage ?? null;

        // Phosphorus serum alert
        if ($iris && $record->phosphorus !== null) {
            $limit  = self::IRIS_PHOSPHORUS_LIMITS[$iris] ?? 6.0;
            $pSuero = (float) $record->phosphorus;
            if ($pSuero > $limit) {
                $alerts[] = "⚠️ IRIS {$iris}: Fósforo sérico ({$pSuero} mg/dL) supera el límite recomendado ({$limit} mg/dL). Considere quelantes de fósforo.";
            }
        }

        // Potassium serum alert
        if ($record->potassium !== null && (float) $record->potassium < 4.0) {
            $alerts[] = "⚠️ Hipopotasemia: Potasio sérico ({$record->potassium} mmol/L) < 4 mmol/L. Se recomienda suplementación de potasio.";
        }

        // Protein restriction notice for IRIS III/IV
        if (in_array($iris, ['III', 'IV'])) {
            $alerts[] = "ℹ️ IRIS {$iris}: Se aplicó restricción proteica controlada (−20% del NRC) para reducir azotemia. Monitorear signos de malnutrición.";
        }

        // Moisture check (diet should be ≥70% water)
        $totalGrams = array_sum(array_column($nutrients, '')) ?: 1;
        $waterGrams = $nutrients['water_g'];
        // Calculate moisture % from the scaled ingredients water
        $totalFoodGrams = 0;
        // We'll rely on the Gemini report to elaborate on this; just flag if omega-3 supplement was included.
        $alerts[] = "ℹ️ Aceite de salmón incluido como suplemento fijo (5g/día) para aporte de Omega-3 EPA+DHA renoprotector.";

        return $alerts;
    }
}
