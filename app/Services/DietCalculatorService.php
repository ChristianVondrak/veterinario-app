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
            'name'        => 'Dieta Renal Temprana de Pollo (IRIS I-II)',
            'protein_src' => 'Pechuga de pollo sin piel hervida',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
            'supplement_g'=> 5.0,
        ],
        'dieta_renal_temprana_res' => [
            'name'        => 'Dieta Renal Temprana de Res (IRIS I-II)',
            'protein_src' => 'Carne molida de res 70-30 asada',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
            'supplement_g'=> 5.0,
        ],

        // ──────────────── ETAPA AVANZADA (IRIS III - IV) ────────────────
        'dieta_renal_avanzada_pollo' => [
            'name'        => 'Dieta Renal Avanzada de Pollo (IRIS III-IV)',
            'protein_src' => 'Pechuga de pollo sin piel hervida',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
            'supplement_g'=> 5.0,
        ],
        'dieta_renal_avanzada_res' => [
            'name'        => 'Dieta Renal Avanzada de Res (IRIS III-IV)',
            'protein_src' => 'Carne molida de res 70-30 asada',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
            'supplement_g'=> 5.0,
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
        $ageYears  = $patient->birth_date
            ? (int) Carbon::parse($patient->birth_date)->diffInYears(now())
            : 0;
        $ageMonths = $patient->birth_date
            ? (int) Carbon::parse($patient->birth_date)->diffInMonths(now())
            : 0;

        return $this->calculateMerFromScalars(
            $rer,
            $patient->reproductive_status ?? '',
            $record->activity_level ?? 'medium',
            $ageYears,
            $record->physiological_status ?? 'normal',
            $ageMonths
        );
    }

    /**
     * Calculate MER from plain scalar values (no Eloquent required).
     * This is the real implementation — used by Unit tests directly.
     *
     * Factores de MER documentados (fuente: texto clínico de referencia):
     *
     * Fisiológico/Terapéutico:
     *   Gestación (último tercio)       → 3.0 × RER
     *   Lactancia (varía por cachorros) → 3.0 – ≥ 6.0 × RER
     *   Crecimiento < 4 meses           → 3.0 × RER
     *   Crecimiento ≥ 4 meses           → 2.0 × RER
     *   Pérdida de peso                 → 1.0 × RER
     *   Cuidados críticos               → 1.0 × RER
     *   Ganancia de peso                → 1.2–1.8 × RER
     *
     * Nivel de actividad (adulto en mantenimiento):
     *   Sedentario (low)                → 1.2 × RER (castrado) / 1.4 × RER (entero)
     *   Moderado (medium)               → 1.6 × RER
     *   Trabajo moderado (high)         → 2.0–5.0 × RER (se usa 3.0 por defecto)
     *   Trabajo pesado (very_high)      → 5.0–11.0 × RER (se usa 8.0 por defecto)
     *
     * @param  float   $rer                   RER en kcal
     * @param  string  $reproductiveStatus     'intact' | 'neutered'
     * @param  string  $activityLevel          'low' | 'medium' | 'high' | 'very_high'
     * @param  int     $ageYears               Edad del paciente en años completos
     * @param  string  $physiologicalStatus    'normal' | 'gestation' | 'lactation' | 'growth' | 'weight_loss' | 'weight_gain' | 'critical_care'
     * @param  int     $ageMonths              Edad del paciente en meses (para crecimiento)
     */
    public function calculateMerFromScalars(
        float $rer,
        string $reproductiveStatus,
        string $activityLevel,
        int $ageYears,
        string $physiologicalStatus = 'normal',
        int $ageMonths = 0
    ): float {
        $physio   = strtolower($physiologicalStatus);
        $activity = strtolower($activityLevel);
        $status   = strtolower($reproductiveStatus);

        // ── 1. Prioridad: estados fisiológicos y objetivos terapéuticos ──────────
        // Estos reemplazan el factor de actividad cuando están definidos.
        $physiologicalFactor = match($physio) {
            'gestation'    => 3.0,   // Gestación: último tercio → 3.0 × RER
            'lactation'    => 4.0,   // Lactancia promedio; Gemini ajustará según # cachorros (3–≥6)
            'growth'       => $ageMonths < 4 ? 3.0 : 2.0, // < 4 meses → 3.0; luego → 2.0
            'weight_loss'  => 1.0,   // Pérdida de peso: 1.0 × RER
            'critical_care'=> 1.0,   // Cuidados críticos: 1.0 × RER
            'weight_gain'  => 1.5,   // Ganancia de peso: 1.2–1.8 (usamos punto medio 1.5)
            default        => null,  // 'normal' → usa factores de actividad abajo
        };

        if ($physiologicalFactor !== null) {
            return round($rer * $physiologicalFactor, 2);
        }

        // ── 2. Estado reproductivo base (adulto en mantenimiento) ────────────────
        $factor = match(true) {
            str_contains($status, 'neutered') || str_contains($status, 'castrat')
                || str_contains($status, 'castrad') || str_contains($status, 'esteriliz') => 1.6,
            str_contains($status, 'intact') || str_contains($status, 'entero')
                || str_contains($status, 'entera')                                        => 1.8,
            default => 1.6,
        };

        // ── 3. Modificador por nivel de actividad ────────────────────────────────
        // Fuente: texto clínico referenciado.
        //   low       → sedentario: castrado 1.2, entero 1.4
        //   medium    → moderado: 1.6 × RER
        //   high      → trabajo moderado: 2.0–5.0 × RER (default: 3.0)
        //   very_high → trabajo pesado: 5.0–11.0 × RER (default: 8.0)
        $factor = match($activity) {
            'low'       => str_contains($status, 'neutered') || str_contains($status, 'castrat')
                            || str_contains($status, 'castrad') || str_contains($status, 'esteriliz')
                            ? 1.2 : 1.4,
            'medium'    => 1.6,
            'high'      => 3.0,      // Trabajo moderado (rango 2.0–5.0)
            'very_high' => 8.0,      // Trabajo pesado (rango 5.0–11.0)
            default     => 1.6,
        };

        // ── 4. Reducción por edad geriátrica (> 7 años) ──────────────────────────
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

        // ── TARGET MATTERS ───────────────────────────────────────────────
        $ratio = $mer / 1000.0;
        $iris  = $record->iris_stage ?? 'I';

        $targetProteinGrams = self::NRC_PER_1000_KCAL['protein_g'] * $ratio;
        if (in_array($iris, ['III', 'IV'])) {
            $targetProteinGrams *= 0.80; // Restricción proteica para etapas avanzadas
        }

        $targets = [
            'kcal'       => $mer,
            'protein_g'  => $targetProteinGrams,
            'calcium_mg' => self::NRC_PER_1000_KCAL['calcium_mg'] * $ratio,
        ];

        $scaled   = $this->scaleDynamicRecipe($recipe, $targets);

        if ($scaled === null) {
            // Graceful fallback: switch to alternate recipe
            $altKey  = array_key_first(array_filter(
                self::BASE_RECIPES,
                fn($v) => $v['name'] !== $recipe['name']
            ));
            $recipe  = self::BASE_RECIPES[$altKey];
            $scaled  = $this->scaleDynamicRecipe($recipe, $targets);
        }

        $nutrients    = $this->calculateNutrients($scaled);
        $deficiencies = $this->calculateDeficiencies($nutrients, $mer, $record);
        $alertas      = $this->buildIrisAlerts($record, $nutrients, $mer, $scaled);

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
     * Scale recipe sequentially based on target objectives.
     * 
     * Orden de formulación secuencial:
     * A) Suplementos fijos (Aceite de salmón y Fibra: Brócoli 30g).
     * B) Proteína: gramos calculados para llenar $targets['protein_g'].
     * C) Calcio: gramos calculados para llenar $targets['calcium_mg'].
     * D) Carbohidratos: gramos calculados para rellenar las calorías faltantes hasta $targets['kcal'].
     */
    private function scaleDynamicRecipe(array $recipe, array $targets): ?array
    {
        $proteinSrc = $recipe['protein_src'];
        $carbSrc    = $recipe['carb_src'];
        $fiberSrc   = $recipe['fiber_src'];
        $calciumSrc = $recipe['calcium_src'];
        $supplement = $recipe['supplement'];
        $suppGrams  = $recipe['supplement_g'];
        $fiberGrams = 30.0; // Fijo 30g de suplemento de fibra

        // Load all required ingredients from DB in one query
        $names       = [$proteinSrc, $carbSrc, $fiberSrc, $calciumSrc, $supplement];
        $dbIngredients = Ingredient::whereIn('name', $names)->get()->keyBy('name');

        // Check all ingredients are present
        foreach ($names as $name) {
            if (! $dbIngredients->has($name)) {
                Log::warning("DietCalculatorService: ingredient not found in DB: {$name}");
                return null;
            }
        }

        $scaled = [];
        $currentTotals = [
            'kcal'       => 0.0,
            'protein_g'  => 0.0,
            'calcium_mg' => 0.0,
        ];

        // Función lambda de ayuda para añadir y sumar
        $addIngredient = function ($name, $grams, $isSupplement = false) use (&$scaled, &$currentTotals, $dbIngredients) {
            if ($grams <= 0) return;

            $ing = $dbIngredients->get($name);
            $factor = $grams / 100.0;

            $currentTotals['kcal']       += $ing->energy_kcal * $factor;
            $currentTotals['protein_g']  += $ing->protein_g * $factor;
            $currentTotals['calcium_mg'] += $ing->calcium_mg * $factor;

            $displayName = $isSupplement ? $name . ' (suplemento)' : $name;

            $scaled[] = [
                'name'          => $displayName,
                'grams'         => round($grams, 1),
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
        };

        // A) Fijos: Suplemento omega-3 y Fibra (contribuyen a kcal y proteína acumuladas)
        $addIngredient($supplement, $suppGrams, true);
        $addIngredient($fiberSrc, $fiberGrams);

        // C) Calcio: fijo por objetivo de calcium_mg (contribuye muy poco a kcal/proteína)
        $calciumIng      = $dbIngredients->get($calciumSrc);
        $missingCalciumC = max(0, $targets['calcium_mg'] - $currentTotals['calcium_mg']);
        $calciumGrams    = ($calciumIng->calcium_mg > 0)
            ? ($missingCalciumC / $calciumIng->calcium_mg) * 100.0
            : 0;
        $addIngredient($calciumSrc, $calciumGrams);

        // ─────────────────────────────────────────────────────────────────────
        // B+D) Sistema lineal 2×2 + PISO CLÍNICO DE PROTEÍNA HBV
        //
        // El sistema lineal resuelve: ¿cuántos gramos de protein_src (P) y
        // carb_src (C) satisfacen simultáneamente el target de kcal y proteína?
        //
        //   Ec.1: kP·P + kC·C = R_kcal
        //   Ec.2: pP·P + pC·C = R_prot
        //
        // PISO CLÍNICO: al menos el 60% de la proteína objetivo DEBE provenir
        // de la fuente de alto valor biológico (HBV). Sin este piso, cuando
        // R_prot es bajo y R_kcal alto (IRIS III con MER alto), el sistema
        // puede elegir <5g de carne porque la papa aporta suficiente proteína
        // a escala. Eso es matemáticamente válido pero clínicamente inaceptable.
        //
        // Después de aplicar el piso, los carbohidratos se recalculan desde
        // las kcal REALES faltantes (no desde el sistema de Cramer).
        // ─────────────────────────────────────────────────────────────────────
        $proteinIng = $dbIngredients->get($proteinSrc);
        $carbIng    = $dbIngredients->get($carbSrc);

        // Coeficientes por gramo
        $kP = $proteinIng->energy_kcal / 100.0;   // kcal/g  – protein_src
        $kC = $carbIng->energy_kcal    / 100.0;   // kcal/g  – carb_src
        $pP = $proteinIng->protein_g   / 100.0;   // g_prot/g – protein_src
        $pC = $carbIng->protein_g      / 100.0;   // g_prot/g – carb_src

        // Residuos después de los ingredientes fijos
        $R_kcal = max(0, $targets['kcal']      - $currentTotals['kcal']);
        $R_prot = max(0, $targets['protein_g'] - $currentTotals['protein_g']);

        // 1. Solución del sistema de Cramer
        $det = ($kP * $pC) - ($kC * $pP);

        if (abs($det) > 1e-9) {
            $proteinGrams = (($R_kcal * $pC) - ($R_prot * $kC)) / $det;
        } else {
            $proteinGrams = ($pP > 0) ? ($R_prot / $pP) : 0;
        }

        // 2. Piso clínico HBV: mínimo 60% de proteína objetivo desde protein_src
        if ($pP > 0) {
            $minHbvGrams = ($targets['protein_g'] * 0.60) / $pP;
            if ($proteinGrams < $minHbvGrams) {
                Log::info('DietCalculatorService: HBV floor applied.', [
                    'computed_g' => round($proteinGrams, 2),
                    'floor_g'   => round($minHbvGrams, 2),
                ]);
                $proteinGrams = $minHbvGrams;
            }
        }

        // 3. Añadir protein_src con el valor definitivo
        $addIngredient($proteinSrc, max(0, $proteinGrams));

        // 4. Recalcular carbGrams desde las kcal reales faltantes
        //    (addIngredient ya actualizó $currentTotals['kcal'])
        $finalMissingKcal = max(0, $targets['kcal'] - $currentTotals['kcal']);
        $finalCarbGrams   = ($kC > 0) ? ($finalMissingKcal / $kC) : 0;
        $addIngredient($carbSrc, max(0, $finalCarbGrams));

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
            'Proteína (g)'          => $proteinRequired * 1.45, // +45%: cubre el overshoot por piso HBV clínico
            'Grasa (g)'             => 82.5 * $ratio,           // NRC SUL
            'Calcio (mg)'           => 4500.0 * $ratio,         // NRC SUL
            'Fósforo (mg)'          => self::NRC_PER_1000_KCAL['phosphorus_mg'] * $ratio * 1.15, // Estricto: +15% del objetivo
            'Potasio (mg)'          => 4000.0 * $ratio,         // Margen seguro alto
            'Sodio (mg)'            => 1500.0 * $ratio,         // SUL recomendado
            // Omega-3: el aceite de salmón es una dosis fija clínica (5g/día) NO proporcional
            // al tamaño corporal. El SUL NRC absoluto es 2800 mg/día. No se escala por ratio
            // para evitar falsos positivos de EXCESO en pacientes pequeños o de bajo MER.
            'Omega-3 EPA+DHA (mg)'  => 2800.0,
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

            // Special rule for sodium: low sodium is THERAPEUTIC in renal patients.
            // Only 'EXCESO' (high sodium) is dangerous. Below NRC min = 'CONTROLADO'
            // (intentional restriction, not a deficit to worry about).
            if ($label === 'Sodio (mg)' && $estado !== 'EXCESO') {
                $estado = 'CONTROLADO';
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
     *
     * @param  MedicalRecord  $record      Eloquent record with lab values
     * @param  array          $nutrients   Summed nutrient totals (from calculateNutrients)
     * @param  float          $merKcal     Daily energy requirement in kcal
     * @param  array          $scaled      Scaled ingredient list (from scaleRecipe) – needed for moisture %
     */
    private function buildIrisAlerts(
        MedicalRecord $record,
        array $nutrients,
        float $merKcal,
        array $scaled = []
    ): array {
        $alerts = [];
        $iris   = $record->iris_stage ?? null;

        // ── 1. Fósforo sérico (rango IRIS) ──────────────────────────────────────
        if ($iris && $record->phosphorus !== null) {
            $limit  = self::IRIS_PHOSPHORUS_LIMITS[$iris] ?? 6.0;
            $pSuero = (float) $record->phosphorus;
            if ($pSuero > $limit) {
                $alerts[] = "⚠️ IRIS {$iris}: Fósforo sérico ({$pSuero} mg/dL) supera el límite recomendado ({$limit} mg/dL). Considere quelantes de fósforo.";
            }
        }

        // ── 2. Hipopotasemia – potasio bajo (< 4.0 mmol/L) ────────────────────
        if ($record->potassium !== null && (float) $record->potassium < 4.0) {
            $alerts[] = "⚠️ Hipopotasemia: Potasio sérico ({$record->potassium} mmol/L) < 4.0 mmol/L. Se recomienda suplementación de potasio.";
        }

        // ── 3. Hiperpotasemia – potasio alto (> 5.3 mmol/L) ───────────────────
        // Fuente: estudio reportó morbilidad del 41% con potasio sérico > 5.3 mmol/L.
        if ($record->potassium !== null && (float) $record->potassium > 5.3) {
            $alerts[] = "⚠️ Hiperpotasemia: Potasio sérico ({$record->potassium} mmol/L) > 5.3 mmol/L. Se requiere una dieta con restricción de potasio. Morbilidad asociada: 41%.";
        }

        // ── 4. Acidosis metabólica – bicarbonato bajo (< 18 mmol/L) ───────────
        // Fuente: Bicarbonato debe mantenerse en 18–24 mmol/L.
        // Terapia de alcalinización cuando < 18 mmol/L.
        if ($record->bicarbonate !== null && (float) $record->bicarbonate < 18.0) {
            $alerts[] = "⚠️ Acidosis metabólica: Bicarbonato sérico ({$record->bicarbonate} mmol/L) < 18 mmol/L (normal: 18–24 mmol/L). Se recomienda implementar terapia de alcalinización.";
        }

        // ── 5. Restricción proteica IRIS III/IV ───────────────────────────────
        if (in_array($iris, ['III', 'IV'])) {
            $alerts[] = "ℹ️ IRIS {$iris}: Se aplicó restricción proteica controlada (−20% del NRC) para reducir azotemia. Monitorear signos de malnutrición.";
        }

        // ── 6. Porcentaje de humedad (debe ser ≥ 70%) ─────────────────────────
        // Fuente: "Un incremento en el consumo de agua puede alcanzarse ofreciendo
        // dietas que contengan 70% o más porcentaje de humedad."
        if (!empty($scaled)) {
            $totalFoodGrams = array_sum(array_column($scaled, 'grams'));
            if ($totalFoodGrams > 0) {
                $porcentajeHumedad = ($nutrients['water_g'] / $totalFoodGrams) * 100.0;
                if ($porcentajeHumedad < 70.0) {
                    $alerts[] = sprintf(
                        "⚠️ Humedad baja: La dieta actual aporta %.1f%% de humedad (mínimo recomendado: 70%%). "
                        . "Se sugiere añadir agua o caldos sin sal en la preparación.",
                        $porcentajeHumedad
                    );
                } else {
                    $alerts[] = sprintf(
                        "✅ Humedad adecuada: La dieta aporta %.1f%% de humedad, superando el umbral del 70%% recomendado para pacientes renales.",
                        $porcentajeHumedad
                    );
                }
            }
        }

        // ── 7. Suplemento Omega-3 (renoprotector) ────────────────────────────
        $alerts[] = "ℹ️ Aceite de salmón incluido como suplemento fijo (5 g/día) para aporte de Omega-3 EPA+DHA renoprotector.";

        // ── 8. Antioxidantes – Vitamina E y Vitamina C ────────────────────────
        // Fuente: Perros con ERC presentan estrés oxidativo elevado.
        // Se recomienda suplementación de Vitamina E y Vitamina C.
        $alerts[] = "ℹ️ Antioxidantes: Los pacientes con ERC presentan estrés oxidativo elevado. "
            . "Se recomienda suplementación de Vitamina E (10–15 UI/kg/día) y Vitamina C (10–20 mg/kg/día) "
            . "bajo supervisión veterinaria.";

        return $alerts;
    }
}
