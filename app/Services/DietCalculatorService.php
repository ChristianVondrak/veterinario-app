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
    // Source: National Research Council (NRC), 2006.
    // All values expressed per kg BW^0.75 (Metabolic Body Weight).
    //
    // Keys:
    //   minimal     → Minimal Requirement (absolute floor)
    //   recommended → Recommended Allowance (preferred target; null = use minimal)
    //   sul_bw      → Safe Upper Limit per kg BW^0.75 (null = no explicit NRC limit)
    //   unit        → native NRC unit for the raw values
    //   label       → UI display label
    // ─────────────────────────────────────────────────────────────
    private const NRC_TABLE_15_5 = [
        'protein_g' => [
            'minimal'     => 2.62,   // g / kg BW^0.75
            'recommended' => 3.28,   // g / kg BW^0.75
            'sul_bw'      => null,   // No explicit SUL in table
            'unit'        => 'g',
            'label'       => 'Proteína (g)',
        ],
        'fat_g' => [
            'minimal'     => null,
            'recommended' => 1.8,    // g / kg BW^0.75 (Recommended Allowance)
            'sul_bw'      => 10.8,   // g / kg BW^0.75 (Safe Upper Limit)
            'unit'        => 'g',
            'label'       => 'Grasa (g)',
        ],
        'calcium_g' => [
            'minimal'     => 0.059,  // g / kg BW^0.75
            'recommended' => 0.13,   // g / kg BW^0.75
            'sul_bw'      => null,
            'unit'        => 'g',
            'label'       => 'Calcio (mg)',
        ],
        'phosphorus_g' => [
            'minimal'     => null,
            'recommended' => 0.10,   // g / kg BW^0.75 (= Adequate Intake)
            'sul_bw'      => null,
            'unit'        => 'g',
            'label'       => 'Fósforo (mg)',
        ],
        'potassium_g' => [
            'minimal'     => null,
            'recommended' => 0.14,   // g / kg BW^0.75 (= Adequate Intake)
            'sul_bw'      => null,
            'unit'        => 'g',
            'label'       => 'Potasio (mg)',
        ],
        'sodium_mg' => [
            'minimal'     => 9.85,   // mg / kg BW^0.75
            'recommended' => 26.2,   // mg / kg BW^0.75
            'sul_bw'      => null,
            'unit'        => 'mg',
            'label'       => 'Sodio (mg)',
        ],
        'omega_3_g' => [
            'minimal'     => null,
            'recommended' => 0.03,   // g / kg BW^0.75 (EPA+DHA combined)
            'sul_bw'      => 0.37,   // g / kg BW^0.75 (Safe Upper Limit)
            'unit'        => 'g',
            'label'       => 'Omega-3 EPA+DHA (mg)',
        ],
    ];

    // ─────────────────────────────────────────────────────────────
    // Densidad de omega-3 en el aceite de salmón (g omega-3 / g aceite)
    // Fuente: seeder — 31.5g omega_3 por 100g aceite
    // ─────────────────────────────────────────────────────────────
    private const SALMON_OIL_OMEGA3_PER_GRAM = 0.315;

    // ─────────────────────────────────────────────────────────────
    // Límite de ingesta diaria de alimento: 3% del peso corporal.
    // Perros consumen ~2–3% de su peso en materia seca equivalente.
    // Usamos 3% como techo máximo seguro (peso fresco total).
    // ─────────────────────────────────────────────────────────────
    private const MAX_FOOD_FRACTION = 0.03; // 3% BW

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
    // 'supplement_g' ya no se usa como dosis fija — se calcula dinámicamente.
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
        ],
        'dieta_renal_temprana_res' => [
            'name'        => 'Dieta Renal Temprana de Res (IRIS I-II)',
            'protein_src' => 'Carne molida de res 70-30 asada',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
        ],

        // ──────────────── ETAPA AVANZADA (IRIS III - IV) ────────────────
        'dieta_renal_avanzada_pollo' => [
            'name'        => 'Dieta Renal Avanzada de Pollo (IRIS III-IV)',
            'protein_src' => 'Pechuga de pollo sin piel hervida',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
        ],
        'dieta_renal_avanzada_res' => [
            'name'        => 'Dieta Renal Avanzada de Res (IRIS III-IV)',
            'protein_src' => 'Carne molida de res 70-30 asada',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'supplement'  => 'Aceite de salmón',
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
     *
     * @param  float   $rer                   RER en kcal
     * @param  string  $reproductiveStatus     'intact' | 'neutered'
     * @param  string  $activityLevel          'low' | 'medium' | 'high'
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

        // ── 2. Modificador por nivel de actividad & estado reproductivo ──────────
        // Nota: 'very_high' se eliminó del formulario por seguridad clínica.
        // Se mantiene como fallback interno al valor de 'high' por robustez.
        $factor = match($activity) {
            'low'       => str_contains($status, 'neutered') || str_contains($status, 'castrat')
                            || str_contains($status, 'castrad') || str_contains($status, 'esteriliz')
                            ? 1.2 : 1.4,
            'medium'    => 1.6,
            'high'      => 3.0,      // Trabajo moderado (rango 2.0–5.0)
            'very_high' => 3.0,      // Fallback interno — no debe llegar desde el formulario
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
        if ($weightKg <= 0) {
            throw new \InvalidArgumentException("DietCalculatorService: El peso del paciente debe ser mayor a 0.");
        }

        $rer      = $this->calculateRer($weightKg);
        $mer      = $this->calculateMer($rer, $patient, $record);

        $recipe   = $this->selectRecipe($record);

        // ── METABOLIC BODY WEIGHT ────────────────────────────────────────
        $bwMetabolic = pow($weightKg, 0.75);
        $iris        = $record->iris_stage ?? 'I';

        // Protein target: Recommended Allowance × BW^0.75
        // IRIS III/IV: −20% para reducir carga de azotemia en el riñón.
        $targetProteinGrams = self::nrcValue('protein_g') * $bwMetabolic;
        if (in_array($iris, ['III', 'IV'])) {
            $targetProteinGrams *= 0.80;
        }

        // Calcium target: Recommended Allowance × BW^0.75 (g → mg)
        $targetCalciumMg = self::nrcValue('calcium_g') * $bwMetabolic * 1000.0;

        // Omega-3 target: Recommended Allowance × BW^0.75 (g)
        // La dosis de aceite de salmón se calcula desde este target.
        $targetOmega3G = self::nrcValue('omega_3_g') * $bwMetabolic;

        $targets = [
            'kcal'        => $mer,
            'protein_g'   => $targetProteinGrams,
            'calcium_mg'  => $targetCalciumMg,
            'omega_3_g'   => $targetOmega3G,
        ];

        $scaled   = $this->scaleDynamicRecipe($recipe, $targets, $weightKg);

        if ($scaled === null) {
            // Graceful fallback: switch to alternate recipe
            $altKey  = array_key_first(array_filter(
                self::BASE_RECIPES,
                fn($v) => $v['name'] !== $recipe['name']
            )) ?? 'dieta_renal_temprana_pollo';
            $recipe  = self::BASE_RECIPES[$altKey];
            $scaled  = $this->scaleDynamicRecipe($recipe, $targets, $weightKg);

            if ($scaled === null) {
                throw new \RuntimeException("DietCalculatorService: Fallaron todos los intentos de formular la receta. Un ingrediente obligatorio puede estar ausente en la base de datos.");
            }
        }

        $nutrients    = $this->calculateNutrients($scaled);
        $deficiencies = $this->calculateDeficiencies($nutrients, $mer, $bwMetabolic, $record);
        $alertas      = $this->buildIrisAlerts($record, $nutrients, $scaled);

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

    private function selectRecipe(MedicalRecord $record): array
    {
        $considerations = strtolower($record->special_considerations ?? '');
        $irisStage      = $record->iris_stage ?? 'I';

        // Detectar alergia/intolerancia al pollo con múltiples variantes
        $avoidChicken = (bool) preg_match(
            '/alergi[ao]\s*(al?\s*)?pollo|sin\s*pollo|no\s*pollo|intolerancia\s*(al?\s*)?pollo|chicken/i',
            $considerations
        );

        $proteinSource = $avoidChicken ? 'res' : 'pollo';

        // Seleccionar etapa clínica
        $stage = in_array($irisStage, ['III', 'IV']) ? 'avanzada' : 'temprana';

        $key = "dieta_renal_{$stage}_{$proteinSource}";

        return self::BASE_RECIPES[$key] ?? self::BASE_RECIPES['dieta_renal_temprana_pollo'];
    }

    /**
     * Scale recipe sequentially based on target objectives.
     *
     * Cascada de formulación (orden clínico correcto):
     * 1. Calcular dosis de Aceite de salmón desde target Omega-3 NRC.
     * 2. Fibra fija (Brócoli 30g).
     * 3. Calcio (Cáscara de huevo) según target calcium_mg.
     * 4. Proteína (sistema lineal + piso HBV 60%).
     * 5. Carbohidratos: rellenar kcal faltantes.
     * 6. POST-CHECK: Potasio — reducir carbohidratos si excede 120% del requerimiento.
     * 7. POST-CHECK: Cap de peso total al 3% del peso corporal (reescalado proporcional).
     *
     * @param  array  $recipe    Selected recipe definition
     * @param  array  $targets   ['kcal', 'protein_g', 'calcium_mg', 'omega_3_g']
     * @param  float  $weightKg  Patient weight in kg (for 3% BW cap)
     */
    private function scaleDynamicRecipe(array $recipe, array $targets, float $weightKg): ?array
    {
        $proteinSrc = $recipe['protein_src'];
        $carbSrc    = $recipe['carb_src'];
        $fiberSrc   = $recipe['fiber_src'];
        $calciumSrc = $recipe['calcium_src'];
        $supplement = $recipe['supplement'];
        $fiberGrams = 30.0; // Brócoli fijo 30g — fuente de fibra

        // Calcular dosis de aceite de salmón desde target NRC de Omega-3
        // Dosis = target_omega3_g / densidad_omega3_del_aceite (g/g)
        // Cap: no superar el SUL de NRC (0.37 g/kg BW^0.75 ya escalado en $targets)
        $bwMetabolic      = pow($weightKg, 0.75);
        $omega3TargetG    = $targets['omega_3_g'] ?? (self::nrcValue('omega_3_g') * $bwMetabolic);
        $omega3SulG       = self::NRC_TABLE_15_5['omega_3_g']['sul_bw'] * $bwMetabolic;
        $omega3CappedG    = min($omega3TargetG, $omega3SulG);
        $suppGrams        = $omega3CappedG / self::SALMON_OIL_OMEGA3_PER_GRAM;

        // Cargar ingredientes de la BD en una única consulta
        $names         = [$proteinSrc, $carbSrc, $fiberSrc, $calciumSrc, $supplement];
        $dbIngredients = Ingredient::whereIn('name', $names)->get()->keyBy('name');

        foreach ($names as $name) {
            if (! $dbIngredients->has($name)) {
                Log::warning("DietCalculatorService: ingredient not found in DB: {$name}");
                return null;
            }
        }

        $scaled = [];
        $currentTotals = [
            'kcal'        => 0.0,
            'protein_g'   => 0.0,
            'calcium_mg'  => 0.0,
            'potassium_mg'=> 0.0,
        ];

        // Lambda: añade ingrediente y acumula totales
        $addIngredient = function ($name, $grams, $isSupplement = false) use (&$scaled, &$currentTotals, $dbIngredients) {
            if ($grams <= 0) return;

            $ing    = $dbIngredients->get($name);
            $factor = $grams / 100.0;

            $currentTotals['kcal']         += $ing->energy_kcal   * $factor;
            $currentTotals['protein_g']    += $ing->protein_g      * $factor;
            $currentTotals['calcium_mg']   += $ing->calcium_mg     * $factor;
            $currentTotals['potassium_mg'] += $ing->potassium_mg   * $factor;

            $displayName = $isSupplement ? $name . ' (suplemento)' : $name;

            $scaled[] = [
                'name'          => $displayName,
                'grams'         => round($grams, 1),
                'kcal'          => round($ing->energy_kcal   * $factor, 2),
                'protein_g'     => round($ing->protein_g     * $factor, 2),
                'fat_g'         => round($ing->fat_g         * $factor, 2),
                'carbohydrate_g'=> round($ing->carbohydrate_g * $factor, 2),
                'phosphorus_mg' => round($ing->phosphorus_mg  * $factor, 2),
                'potassium_mg'  => round($ing->potassium_mg   * $factor, 2),
                'calcium_mg'    => round($ing->calcium_mg     * $factor, 2),
                'sodium_mg'     => round($ing->sodium_mg      * $factor, 2),
                'omega_3_g'     => round($ing->omega_3_g      * $factor, 3),
                'water_g'       => round($ing->water_g        * $factor, 2),
            ];
        };

        // ── PASO 1: Suplemento omega-3 (dosis calculada por NRC) ─────────────────
        $addIngredient($supplement, $suppGrams, true);

        // ── PASO 2: Fibra fija (Brócoli 30g) ─────────────────────────────────────
        $addIngredient($fiberSrc, $fiberGrams);

        // ── PASO 3: Calcio (Cáscara de huevo — satisface target calcium_mg) ──────
        $calciumIng      = $dbIngredients->get($calciumSrc);
        $missingCalciumC = max(0, $targets['calcium_mg'] - $currentTotals['calcium_mg']);
        $calciumGrams    = ($calciumIng->calcium_mg > 0)
            ? ($missingCalciumC / $calciumIng->calcium_mg) * 100.0
            : 0;
        $addIngredient($calciumSrc, $calciumGrams);

        // ── PASO 4: Proteína (fuente HBV) + PASO 5: Carbohidratos ────────────────
        //
        // Sistema lineal 2×2 (Cramer) para satisfacer simultáneamente kcal y proteína:
        //   Ec.1: kP·P + kC·C = R_kcal
        //   Ec.2: pP·P + pC·C = R_prot
        //
        // PISO CLÍNICO HBV: mínimo 60% de proteína objetivo desde protein_src.
        // ─────────────────────────────────────────────────────────────────────────
        $proteinIng = $dbIngredients->get($proteinSrc);
        $carbIng    = $dbIngredients->get($carbSrc);

        $kP = $proteinIng->energy_kcal / 100.0;
        $kC = $carbIng->energy_kcal    / 100.0;
        $pP = $proteinIng->protein_g   / 100.0;
        $pC = $carbIng->protein_g      / 100.0;

        $R_kcal = max(0, $targets['kcal']      - $currentTotals['kcal']);
        $R_prot = max(0, $targets['protein_g'] - $currentTotals['protein_g']);

        $det = ($kP * $pC) - ($kC * $pP);

        if (abs($det) > 1e-9) {
            $proteinGrams = (($R_kcal * $pC) - ($R_prot * $kC)) / $det;
        } else {
            $proteinGrams = ($pP > 0) ? ($R_prot / $pP) : 0;
        }

        // Piso HBV: mínimo 60% de proteína objetivo desde protein_src
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

        $addIngredient($proteinSrc, max(0, $proteinGrams));

        // ── PASO 5: Carbohidratos (rellenar kcal faltantes) ──────────────────────
        $finalMissingKcal = max(0, $targets['kcal'] - $currentTotals['kcal']);
        $finalCarbGrams   = ($kC > 0) ? ($finalMissingKcal / $kC) : 0;
        $addIngredient($carbSrc, max(0, $finalCarbGrams));

        // ── PASO 6: POST-CHECK Potasio ────────────────────────────────────────────
        // Si el potasio total supera el 120% del requerimiento NRC, reducir papas
        // hasta ajustarlo. El potasio de papas = 328mg/100g.
        $potassiumRequired = self::nrcValue('potassium_g') * $bwMetabolic * 1000.0;
        $potassiumLimit    = $potassiumRequired * 1.20;

        if ($currentTotals['potassium_mg'] > $potassiumLimit) {
            // Buscar el ingrediente carb en el array $scaled para ajustarlo
            $excessK     = $currentTotals['potassium_mg'] - $potassiumLimit;
            $carbIngData = $dbIngredients->get($carbSrc);
            $kPerGram    = $carbIngData->potassium_mg / 100.0; // mg K por gramo de carb

            if ($kPerGram > 0) {
                $reduceGrams = $excessK / $kPerGram;

                // Restar del último scaled[] que sea carbSrc
                foreach (array_reverse(array_keys($scaled)) as $idx) {
                    if ($scaled[$idx]['name'] === $carbSrc) {
                        $oldGrams = $scaled[$idx]['grams'];
                        $newGrams = max(0, $oldGrams - $reduceGrams);
                        $diff     = $oldGrams - $newGrams;

                        if ($diff > 0) {
                            $factor = $diff / 100.0;
                            // Actualizar el elemento
                            $scaled[$idx]['grams']          = round($newGrams, 1);
                            $scaled[$idx]['kcal']           = round($carbIngData->energy_kcal   * ($newGrams / 100.0), 2);
                            $scaled[$idx]['protein_g']      = round($carbIngData->protein_g     * ($newGrams / 100.0), 2);
                            $scaled[$idx]['fat_g']          = round($carbIngData->fat_g         * ($newGrams / 100.0), 2);
                            $scaled[$idx]['carbohydrate_g'] = round($carbIngData->carbohydrate_g * ($newGrams / 100.0), 2);
                            $scaled[$idx]['phosphorus_mg']  = round($carbIngData->phosphorus_mg  * ($newGrams / 100.0), 2);
                            $scaled[$idx]['potassium_mg']   = round($carbIngData->potassium_mg   * ($newGrams / 100.0), 2);
                            $scaled[$idx]['calcium_mg']     = round($carbIngData->calcium_mg     * ($newGrams / 100.0), 2);
                            $scaled[$idx]['sodium_mg']      = round($carbIngData->sodium_mg      * ($newGrams / 100.0), 2);
                            $scaled[$idx]['omega_3_g']      = round($carbIngData->omega_3_g      * ($newGrams / 100.0), 3);
                            $scaled[$idx]['water_g']        = round($carbIngData->water_g        * ($newGrams / 100.0), 2);

                            Log::info('DietCalculatorService: Potassium guard reduced carbs.', [
                                'reduced_by_g' => round($diff, 1),
                                'old_carb_g'   => $oldGrams,
                                'new_carb_g'   => $newGrams,
                            ]);
                        }
                        break;
                    }
                }
            }
        }

        // ── PASO 7: CAP DE PESO TOTAL — 3% del peso corporal ─────────────────────
        // Los perros consumen un máximo de ~2–3% de su peso en alimento al día.
        // Si el total supera este límite, reescalamos todos los ingredientes
        // proporcionalmente para mantener el balance nutricional relativo.
        $maxFoodGrams   = $weightKg * 1000.0 * self::MAX_FOOD_FRACTION;
        $totalFoodGrams = array_sum(array_column($scaled, 'grams'));

        if ($totalFoodGrams > $maxFoodGrams && $totalFoodGrams > 0) {
            $scaleFactor = $maxFoodGrams / $totalFoodGrams;

            Log::info('DietCalculatorService: 3% BW food cap applied.', [
                'total_before_g' => round($totalFoodGrams, 1),
                'max_allowed_g'  => round($maxFoodGrams, 1),
                'scale_factor'   => round($scaleFactor, 4),
            ]);

            foreach ($scaled as &$item) {
                $item['grams']          = round($item['grams']          * $scaleFactor, 1);
                $item['kcal']           = round($item['kcal']           * $scaleFactor, 2);
                $item['protein_g']      = round($item['protein_g']      * $scaleFactor, 2);
                $item['fat_g']          = round($item['fat_g']          * $scaleFactor, 2);
                $item['carbohydrate_g'] = round($item['carbohydrate_g'] * $scaleFactor, 2);
                $item['phosphorus_mg']  = round($item['phosphorus_mg']  * $scaleFactor, 2);
                $item['potassium_mg']   = round($item['potassium_mg']   * $scaleFactor, 2);
                $item['calcium_mg']     = round($item['calcium_mg']     * $scaleFactor, 2);
                $item['sodium_mg']      = round($item['sodium_mg']      * $scaleFactor, 2);
                $item['omega_3_g']      = round($item['omega_3_g']      * $scaleFactor, 3);
                $item['water_g']        = round($item['water_g']        * $scaleFactor, 2);
            }
            unset($item);
        }

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

    // ─────────────────────────────────────────────────────────────
    // NRC TABLE 15-5 HELPERS
    // ─────────────────────────────────────────────────────────────

    /**
     * Returns the best available NRC value for a nutrient key.
     * Priority: Recommended Allowance → Minimal Requirement → 0.
     */
    private static function nrcValue(string $key): float
    {
        $entry = self::NRC_TABLE_15_5[$key];
        return (float) ($entry['recommended'] ?? $entry['minimal'] ?? 0.0);
    }

    /**
     * Returns a human-readable indicativo string for the results table.
     *
     * @param  string  $key         Key in NRC_TABLE_15_5
     * @param  float   $multiplier  Unit conversion factor (e.g. 1000 to convert g → mg)
     * @param  string  $displayUnit Override display unit after conversion (e.g. 'mg')
     */
    private static function nrcIndicativo(string $key, float $multiplier = 1.0, string $displayUnit = ''): string
    {
        $entry      = self::NRC_TABLE_15_5[$key];
        $isRec      = isset($entry['recommended']);
        $rawVal     = (float) ($isRec ? $entry['recommended'] : ($entry['minimal'] ?? 0.0));
        $displayVal = $rawVal * $multiplier;
        $unit       = $displayUnit ?: $entry['unit'];
        $type       = $isRec ? 'Recomendado' : 'Mínimo';
        $formatted  = ($displayVal == floor($displayVal)) ? (int) $displayVal : round($displayVal, 3);

        return "{$formatted}{$unit} / kg BW\u{2070}\u{22C5}\u{2077}\u{2075} ({$type})";
    }

    /**
     * Compare actual nutrient totals against NRC Table 15-5 requirements
     * (expressed per kg BW^0.75) and apply IRIS-specific adjustments.
     *
     * @param  array          $nutrients    Summed nutrient totals (from calculateNutrients)
     * @param  float          $merKcal      Daily energy requirement in kcal (used for SUL fallbacks)
     * @param  float          $bwMetabolic  Metabolic body weight: BW(kg)^0.75
     * @param  MedicalRecord  $record       Eloquent record with lab values and IRIS stage
     */
    private function calculateDeficiencies(
        array $nutrients,
        float $merKcal,
        float $bwMetabolic,
        MedicalRecord $record
    ): array {
        $iris  = $record->iris_stage ?? 'I';
        $ratio = $merKcal / 1000.0;  // Kept only for SULs without a BW-based NRC value

        // ── Protein: RA × BW^0.75; IRIS III/IV → −20% ───────────────────────
        $proteinRequired = self::nrcValue('protein_g') * $bwMetabolic;
        if (in_array($iris, ['III', 'IV'])) {
            $proteinRequired *= 0.80;
        }

        // ── Requirements (Recommended Allowance, fallback to Minimal) ────────
        $requirements = [
            'Proteína (g)'          => $proteinRequired,
            'Grasa (g)'             => self::nrcValue('fat_g')        * $bwMetabolic,
            'Calcio (mg)'           => self::nrcValue('calcium_g')    * $bwMetabolic * 1000.0,
            'Fósforo (mg)'          => self::nrcValue('phosphorus_g') * $bwMetabolic * 1000.0,
            'Potasio (mg)'          => self::nrcValue('potassium_g')  * $bwMetabolic * 1000.0,
            'Sodio (mg)'            => self::nrcValue('sodium_mg')    * $bwMetabolic,
            'Omega-3 EPA+DHA (mg)'  => self::nrcValue('omega_3_g')   * $bwMetabolic * 1000.0,
        ];

        // ── Actual aporte from the scaled recipe ─────────────────────────────
        $actual = [
            'Proteína (g)'          => $nutrients['protein_g'],
            'Grasa (g)'             => $nutrients['fat_g'],
            'Calcio (mg)'           => $nutrients['calcium_mg'],
            'Fósforo (mg)'          => $nutrients['phosphorus_mg'],
            'Potasio (mg)'          => $nutrients['potassium_mg'],
            'Sodio (mg)'            => $nutrients['sodium_mg'],
            'Omega-3 EPA+DHA (mg)'  => round($nutrients['omega_3_g'] * 1000, 2),
        ];

        // ── Safe Upper Limits ─────────────────────────────────────────────────
        $limits = [
            'Proteína (g)'          => $proteinRequired * 1.45,
            'Grasa (g)'             => self::NRC_TABLE_15_5['fat_g']['sul_bw'] * $bwMetabolic,
            'Calcio (mg)'           => 4500.0 * $ratio,
            'Fósforo (mg)'          => self::nrcValue('phosphorus_g') * $bwMetabolic * 1000.0 * 1.15,
            'Potasio (mg)'          => 4000.0 * $ratio,
            // Sodio: SUL real NRC > 15g/día; usamos fallback clínico conservador
            'Sodio (mg)'            => 1500.0 * $ratio,
            'Omega-3 EPA+DHA (mg)'  => self::NRC_TABLE_15_5['omega_3_g']['sul_bw'] * $bwMetabolic * 1000.0,
        ];

        // ── Indicativos NRC ───────────────────────────────────────────────────
        $indicativos = [
            'Proteína (g)'          => self::nrcIndicativo('protein_g'),
            'Grasa (g)'             => self::nrcIndicativo('fat_g'),
            'Calcio (mg)'           => self::nrcIndicativo('calcium_g',    1000.0, 'mg'),
            'Fósforo (mg)'          => self::nrcIndicativo('phosphorus_g', 1000.0, 'mg'),
            'Potasio (mg)'          => self::nrcIndicativo('potassium_g',  1000.0, 'mg'),
            'Sodio (mg)'            => self::nrcIndicativo('sodium_mg'),
            'Omega-3 EPA+DHA (mg)'  => self::nrcIndicativo('omega_3_g',   1000.0, 'mg'),
        ];

        $deficiencies = [];

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

            // Hipopotasemia: forzar CRÍTICO si potasio sérico < 4.0 mmol/L
            if ($label === 'Potasio (mg)'
                && $record->potassium !== null
                && (float) $record->potassium < 4.0
                && $estado !== 'EXCESO'
            ) {
                $estado = 'CRÍTICO';
            }

            $deficiencies[] = [
                'nutriente'   => $label,
                'indicativo'  => $indicativos[$label] ?? '—',
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
     * @param  array          $scaled      Scaled ingredient list (from scaleRecipe) – needed for moisture %
     */
    private function buildIrisAlerts(
        MedicalRecord $record,
        array $nutrients,
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
        if ($record->potassium !== null && (float) $record->potassium > 5.3) {
            $alerts[] = "⚠️ Hiperpotasemia: Potasio sérico ({$record->potassium} mmol/L) > 5.3 mmol/L. Se requiere una dieta con restricción de potasio. Morbilidad asociada: 41%.";
        }

        // ── 4. Acidosis metabólica – bicarbonato bajo (< 18 mmol/L) ───────────
        if ($record->bicarbonate !== null && (float) $record->bicarbonate < 18.0) {
            $alerts[] = "⚠️ Acidosis metabólica: Bicarbonato sérico ({$record->bicarbonate} mmol/L) < 18 mmol/L (normal: 18–24 mmol/L). Se recomienda implementar terapia de alcalinización.";
        }

        // ── 5. Restricción proteica IRIS III/IV ───────────────────────────────
        if (in_array($iris, ['III', 'IV'])) {
            $alerts[] = "ℹ️ IRIS {$iris}: Se aplicó restricción proteica controlada (−20% del NRC) para reducir azotemia. Monitorear signos de malnutrición.";
        }

        // ── 6. Porcentaje de humedad (debe ser ≥ 70%) ─────────────────────────
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

        // ── 7. Omega-3 calculado por peso (no fijo) ────────────────────────────
        $alerts[] = "ℹ️ Aceite de salmón incluido como suplemento calculado por peso metabólico (NRC Omega-3 EPA+DHA recomendado) para aporte renoprotector ajustado al paciente.";

        return $alerts;
    }
}
