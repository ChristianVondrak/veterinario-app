<?php
namespace App\Services;
use App\Models\Ingredient;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
/**
 * Service responsible for formulating and validating veterinary renal diets.
 *
 * Uses NRC Table 15-5 guidelines and metabolic weight (BW^0.75) scaling to 
 * dynamically construct recipes and evaluate nutritional adequacy based on 
 * clinical parameters like IRIS stage and lab values.
 */
class DietCalculatorService
{
    private const NRC_TABLE_15_5 = [
        'protein_g' => [
            'minimal'     => 2.62,   
            'recommended' => 3.28,   
            'sul_bw'      => null,   
            'unit'        => 'g',
            'label'       => 'Proteína (g)',
        ],
        'fat_g' => [
            'minimal'     => null,
            'recommended' => 1.8,    
            'sul_bw'      => 10.8,   
            'unit'        => 'g',
            'label'       => 'Grasa (g)',
        ],
        'calcium_g' => [
            'minimal'     => 0.059,  
            'recommended' => 0.13,   
            'sul_bw'      => null,
            'unit'        => 'g',
            'label'       => 'Calcio (mg)',
        ],
        'phosphorus_g' => [
            'minimal'     => null,
            'recommended' => 0.10,   
            'sul_bw'      => null,
            'unit'        => 'g',
            'label'       => 'Fósforo (mg)',
        ],
        'potassium_g' => [
            'minimal'     => null,
            'recommended' => 0.14,   
            'sul_bw'      => null,
            'unit'        => 'g',
            'label'       => 'Potasio (mg)',
        ],
        'sodium_mg' => [
            'minimal'     => 9.85,   
            'recommended' => 26.2,   
            'sul_bw'      => null,
            'unit'        => 'mg',
            'label'       => 'Sodio (mg)',
        ],
        'omega_3_g' => [
            'minimal'     => null,
            'recommended' => 0.03,   
            'sul_bw'      => 0.37,   
            'unit'        => 'g',
            'label'       => 'Omega-3 EPA+DHA (mg)',
        ],
    ];
    private const SALMON_OIL_OMEGA3_PER_GRAM = 0.315;
    private const MAX_FOOD_FRACTION = 0.04; 
    private const IRIS_PHOSPHORUS_LIMITS = [
        'I'   => 4.5,
        'II'  => 4.5,
        'III' => 5.0,
        'IV'  => 6.0,
    ];
    private const BASE_RECIPES = [
        'dieta_renal_temprana_pollo' => [
            'name'        => 'Dieta Renal Temprana de Pollo (IRIS I-II)',
            'protein_src' => 'Pechuga de pollo sin piel hervida',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'fat_src'     => 'Aceite de oliva',
            'supplement'  => 'Aceite de salmón',
        ],
        'dieta_renal_temprana_res' => [
            'name'        => 'Dieta Renal Temprana de Res (IRIS I-II)',
            'protein_src' => 'Carne molida de res 70-30 asada',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'fat_src'     => 'Aceite de oliva',
            'supplement'  => 'Aceite de salmón',
        ],
        'dieta_renal_avanzada_pollo' => [
            'name'        => 'Dieta Renal Avanzada de Pollo (IRIS III-IV)',
            'protein_src' => 'Pechuga de pollo sin piel hervida',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'fat_src'     => 'Aceite de oliva',
            'supplement'  => 'Aceite de salmón',
        ],
        'dieta_renal_avanzada_res' => [
            'name'        => 'Dieta Renal Avanzada de Res (IRIS III-IV)',
            'protein_src' => 'Carne molida de res 70-30 asada',
            'carb_src'    => 'Papas hervidas sin piel',
            'fiber_src'   => 'Brócoli hervido',
            'calcium_src' => 'Cáscara de huevo en polvo',
            'fat_src'     => 'Aceite de oliva',
            'supplement'  => 'Aceite de salmón',
        ],
    ];
    /**
     * Calculates the Resting Energy Requirement (RER).
     *
     * Formula: RER = 70 × BW(kg)^0.75
     *
     * @param float $weightKg The patient's weight in kg.
     * @return float The calculated RER in kcal.
     */
    public function calculateRer(float $weightKg): float
    {
        return 70.0 * pow($weightKg, 0.75);
    }
    /**
     * Calculates MER from Eloquent models.
     *
     * @param float $rer The calculated Resting Energy Requirement.
     * @param Patient $patient The patient model.
     * @param MedicalRecord $record The medical record model.
     * @return float The calculated MER in kcal.
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
     * Calculates the Maintenance Energy Requirement (MER) from scalar values.
     *
     * Applies physiological and activity modifiers to the Resting Energy Requirement (RER).
     *
     * @param float $rer The calculated Resting Energy Requirement.
     * @param string $reproductiveStatus The reproductive status ('intact' or 'neutered').
     * @param string $activityLevel The activity level ('low', 'medium', 'high').
     * @param int $ageYears The patient's age in years.
     * @param string $physiologicalStatus The physiological status ('normal', 'gestation', 'lactation', 'growth', 'weight_loss', 'weight_gain', 'critical_care').
     * @param int $ageMonths The patient's age in months (used for growth factor).
     * @return float The calculated MER in kcal.
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
        $physiologicalFactor = match($physio) {
            'gestation'    => 3.0,   
            'lactation'    => 4.0,   
            'growth'       => $ageMonths < 4 ? 3.0 : 2.0, 
            'weight_loss'  => 1.0,   
            'critical_care'=> 1.0,   
            'weight_gain'  => 1.5,   
            default        => null,  
        };
        if ($physiologicalFactor !== null) {
            return round($rer * $physiologicalFactor, 2);
        }
        $factor = match($activity) {
            'low'       => str_contains($status, 'neutered') || str_contains($status, 'castrat')
                            || str_contains($status, 'castrad') || str_contains($status, 'esteriliz')
                            ? 1.2 : 1.4,
            'medium'    => 1.6,
            'high'      => 3.0,      
            'very_high' => 3.0,      
            default     => 1.6,
        };
        $mer = $rer * $factor;
        if ($ageYears > 7) {
            $mer *= 0.80;
        }
        return round($mer, 2);
    }
    /**
     * Builds the complete calculation payload for the diet engine.
     *
     * Calculates RER, MER, scales the recipe based on NRC requirements, and evaluates deficiencies.
     *
     * @param Patient $patient The patient model.
     * @param MedicalRecord $record The medical record containing weight and lab values.
     * @return array The calculated diet payload including scaled ingredients and deficiency analysis.
     * @throws \InvalidArgumentException If patient weight is invalid.
     * @throws \RuntimeException If recipe formulation fails.
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
        $bwMetabolic = pow($weightKg, 0.75);
        $iris        = $record->iris_stage ?? 'I';
        $targetProteinGrams = self::nrcValue('protein_g') * $bwMetabolic;
        if (in_array($iris, ['III', 'IV'])) {
            $targetProteinGrams *= 0.80;
        }
        $targetCalciumMg = self::nrcValue('calcium_g') * $bwMetabolic * 1000.0;
        $targetOmega3G = self::nrcValue('omega_3_g') * $bwMetabolic;
        $targets = [
            'kcal'        => $mer,
            'protein_g'   => $targetProteinGrams,
            'calcium_mg'  => $targetCalciumMg,
            'omega_3_g'   => $targetOmega3G,
        ];
        $scaled   = $this->scaleDynamicRecipe($recipe, $targets, $weightKg);
        if ($scaled === null) {
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
    /**
     * Selects the appropriate base recipe based on medical record considerations.
     *
     * Handles protein source selection (e.g., chicken allergies) and IRIS stage specific recipes.
     *
     * @param MedicalRecord $record The medical record.
     * @return array The selected base recipe definition.
     */
    private function selectRecipe(MedicalRecord $record): array
    {
        $considerations = strtolower($record->special_considerations ?? '');
        $irisStage      = $record->iris_stage ?? 'I';
        $avoidChicken = (bool) preg_match(
            '/alergi[ao]\s*(al?\s*)?pollo|sin\s*pollo|no\s*pollo|intolerancia\s*(al?\s*)?pollo|chicken/i',
            $considerations
        );
        $proteinSource = $avoidChicken ? 'res' : 'pollo';
        $stage = in_array($irisStage, ['III', 'IV']) ? 'avanzada' : 'temprana';
        $key = "dieta_renal_{$stage}_{$proteinSource}";
        return self::BASE_RECIPES[$key] ?? self::BASE_RECIPES['dieta_renal_temprana_pollo'];
    }
    /**
     * Scales a base recipe dynamically to meet target nutritional objectives.
     *
     * Uses a linear system to balance protein and carbohydrates while applying clinical constraints (e.g., HBV floors, max food volume).
     *
     * @param array $recipe The selected base recipe definition.
     * @param array $targets The nutritional targets ['kcal', 'protein_g', 'calcium_mg', 'omega_3_g'].
     * @param float $weightKg The patient's weight in kg, used for volume capping.
     * @return array|null The scaled ingredients, or null if formulation fails.
     */
    private function scaleDynamicRecipe(array $recipe, array $targets, float $weightKg): ?array
    {
        $proteinSrc = $recipe['protein_src'];
        $carbSrc    = $recipe['carb_src'];
        $fiberSrc   = $recipe['fiber_src'];
        $calciumSrc = $recipe['calcium_src'];
        $fatSrc     = $recipe['fat_src'];
        $supplement = $recipe['supplement'];
        $fiberGrams = 30.0;
        $bwMetabolic      = pow($weightKg, 0.75);
        $omega3TargetG    = $targets['omega_3_g'] ?? (self::nrcValue('omega_3_g') * $bwMetabolic);
        $omega3SulG       = self::NRC_TABLE_15_5['omega_3_g']['sul_bw'] * $bwMetabolic;
        $omega3CappedG    = min($omega3TargetG, $omega3SulG);
        $suppGrams        = $omega3CappedG / self::SALMON_OIL_OMEGA3_PER_GRAM;
        $names         = [$proteinSrc, $carbSrc, $fiberSrc, $calciumSrc, $fatSrc, $supplement];
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
            'fat_g'       => 0.0,
            'calcium_mg'  => 0.0,
            'potassium_mg'=> 0.0,
        ];
        $addIngredient = function ($name, $grams, $isSupplement = false) use (&$scaled, &$currentTotals, $dbIngredients) {
            if ($grams <= 0) return;
            $ing    = $dbIngredients->get($name);
            $factor = $grams / 100.0;
            $currentTotals['kcal']         += $ing->energy_kcal   * $factor;
            $currentTotals['protein_g']    += $ing->protein_g      * $factor;
            $currentTotals['fat_g']        += $ing->fat_g          * $factor;
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
        $addIngredient($supplement, $suppGrams, true);
        $addIngredient($fiberSrc, $fiberGrams);
        $calciumIng      = $dbIngredients->get($calciumSrc);
        $missingCalciumC = max(0, $targets['calcium_mg'] - $currentTotals['calcium_mg']);
        $calciumGrams    = ($calciumIng->calcium_mg > 0)
            ? ($missingCalciumC / $calciumIng->calcium_mg) * 100.0
            : 0;
        $addIngredient($calciumSrc, $calciumGrams);
        $fatIng        = $dbIngredients->get($fatSrc);
        $fatTargetG    = self::nrcValue('fat_g') * $bwMetabolic;
        $fatNeededG    = max(0, $fatTargetG - $currentTotals['fat_g']);
        $fatGrams      = ($fatIng->fat_g > 0) ? ($fatNeededG / ($fatIng->fat_g / 100.0)) : 0;
        $addIngredient($fatSrc, $fatGrams);
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
            $carbGrams    = (($kP * $R_prot) - ($pP * $R_kcal)) / $det;
        } else {
            $proteinGrams = ($pP > 0) ? ($R_prot / $pP) : 0;
            $carbGrams    = ($kC > 0) ? max(0, ($R_kcal - $proteinGrams * $kP) / $kC) : 0;
        }
        if ($pP > 0) {
            $minHbvGrams = ($targets['protein_g'] * 0.60) / $pP;
            if ($proteinGrams < $minHbvGrams) {
                Log::info('DietCalculatorService: HBV floor applied.', [
                    'computed_g' => round($proteinGrams, 2),
                    'floor_g'   => round($minHbvGrams, 2),
                ]);
                $proteinGrams = $minHbvGrams;
                $carbGrams = ($kC > 0)
                    ? max(0, ($R_kcal - $proteinGrams * $kP) / $kC)
                    : 0;
            }
        }
        $addIngredient($proteinSrc, max(0, $proteinGrams));
        $addIngredient($carbSrc, max(0, $carbGrams));
        $potassiumRequired = self::nrcValue('potassium_g') * $bwMetabolic * 1000.0;
        $potassiumLimit    = $potassiumRequired * 1.20;
        if ($currentTotals['potassium_mg'] > $potassiumLimit) {
            $excessK     = $currentTotals['potassium_mg'] - $potassiumLimit;
            $carbIngData = $dbIngredients->get($carbSrc);
            $kPerGram    = $carbIngData->potassium_mg / 100.0; 
            if ($kPerGram > 0) {
                $reduceGrams = $excessK / $kPerGram;
                foreach (array_reverse(array_keys($scaled)) as $idx) {
                    if ($scaled[$idx]['name'] === $carbSrc) {
                        $oldGrams = $scaled[$idx]['grams'];
                        $newGrams = max(0, $oldGrams - $reduceGrams);
                        $diff     = $oldGrams - $newGrams;
                        if ($diff > 0) {
                            $factor = $diff / 100.0;
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
     * Sums the nutritional contributions from a list of scaled ingredients.
     *
     * @param array $scaledIngredients The array of scaled ingredients.
     * @return array The aggregated nutritional totals.
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
     * Retrieves the best available NRC value for a given nutrient.
     *
     * Prioritizes 'recommended' allowance over 'minimal' requirement.
     *
     * @param string $key The nutrient key in the NRC table.
     * @return float The NRC value per kg metabolic weight.
     */
    private static function nrcValue(string $key): float
    {
        $entry = self::NRC_TABLE_15_5[$key];
        return (float) ($entry['recommended'] ?? $entry['minimal'] ?? 0.0);
    }
    /**
     * Generates a formatted display string for an NRC recommendation.
     *
     * @param string $key The nutrient key in the NRC table.
     * @param float $multiplier Factor to convert raw units to display units (e.g., 1000 for g to mg).
     * @param string $displayUnit Optional override for the display unit string.
     * @return string The formatted NRC indicativo string.
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
     * Evaluates actual nutrient intake against NRC targets to determine deficiency states.
     *
     * Computes percentages and categorizes states (ADECUADO, LEVE, MODERADO, CRÍTICO, EXCESO).
     *
     * @param array $nutrients The actual nutrient totals from the recipe.
     * @param float $merKcal The daily Maintenance Energy Requirement in kcal.
     * @param float $bwMetabolic The metabolic body weight (BW^0.75).
     * @param MedicalRecord $record The medical record, used for specific adjustments (e.g., IRIS stage restrictions).
     * @return array Detailed breakdown of each evaluated nutrient and its status.
     */
    private function calculateDeficiencies(
        array $nutrients,
        float $merKcal,
        float $bwMetabolic,
        MedicalRecord $record
    ): array {
        $iris  = $record->iris_stage ?? 'I';
        $ratio = $merKcal / 1000.0;  
        $proteinRequired = self::nrcValue('protein_g') * $bwMetabolic;
        if (in_array($iris, ['III', 'IV'])) {
            $proteinRequired *= 0.80;
        }
        $sodioMinimal     = self::NRC_TABLE_15_5['sodium_mg']['minimal'] * $bwMetabolic;
        $sodioRecomendado = self::nrcValue('sodium_mg') * $bwMetabolic;
        $requirements = [
            'Proteína (g)'          => $proteinRequired,
            'Grasa (g)'             => self::nrcValue('fat_g')        * $bwMetabolic,
            'Calcio (mg)'           => self::nrcValue('calcium_g')    * $bwMetabolic * 1000.0,
            'Fósforo (mg)'          => self::nrcValue('phosphorus_g') * $bwMetabolic * 1000.0,
            'Potasio (mg)'          => self::nrcValue('potassium_g')  * $bwMetabolic * 1000.0,
            'Sodio (mg)'            => $sodioRecomendado,  
            'Omega-3 EPA+DHA (mg)'  => self::nrcValue('omega_3_g')   * $bwMetabolic * 1000.0,
        ];
        $actual = [
            'Proteína (g)'          => $nutrients['protein_g'],
            'Grasa (g)'             => $nutrients['fat_g'],
            'Calcio (mg)'           => $nutrients['calcium_mg'],
            'Fósforo (mg)'          => $nutrients['phosphorus_mg'],
            'Potasio (mg)'          => $nutrients['potassium_mg'],
            'Sodio (mg)'            => $nutrients['sodium_mg'],
            'Omega-3 EPA+DHA (mg)'  => round($nutrients['omega_3_g'] * 1000, 2),
        ];
        $limits = [
            'Proteína (g)'          => $proteinRequired * 1.45,
            'Grasa (g)'             => self::NRC_TABLE_15_5['fat_g']['sul_bw'] * $bwMetabolic,
            'Calcio (mg)'           => 4500.0 * $ratio,
            'Fósforo (mg)'          => self::nrcValue('phosphorus_g') * $bwMetabolic * 1000.0 * 1.15,
            'Potasio (mg)'          => 4000.0 * $ratio,
            'Sodio (mg)'            => 1500.0 * $ratio,
            'Omega-3 EPA+DHA (mg)'  => self::NRC_TABLE_15_5['omega_3_g']['sul_bw'] * $bwMetabolic * 1000.0,
        ];
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
            if ($label === 'Sodio (mg)' && $estado !== 'EXCESO') {
                $pctVsMinimal = $sodioMinimal > 0
                    ? round(($aporte / $sodioMinimal) * 100, 1)
                    : 100.0;
                $estado = match(true) {
                    $pctVsMinimal >= 100 => 'ADECUADO',  
                    $pctVsMinimal >= 75  => 'LEVE',
                    $pctVsMinimal >= 50  => 'MODERADO',
                    default              => 'CRÍTICO',
                };
            }
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
     * Generates clinical alerts based on the patient's IRIS stage and lab values.
     *
     * @param MedicalRecord $record The patient's medical record.
     * @param array $nutrients The aggregated nutrient totals.
     * @param array $scaled The scaled ingredient list (used to calculate moisture percentage).
     * @return array A list of clinical alert strings.
     */
    private function buildIrisAlerts(
        MedicalRecord $record,
        array $nutrients,
        array $scaled = []
    ): array {
        $alerts = [];
        $iris   = $record->iris_stage ?? null;
        if ($iris && $record->phosphorus !== null) {
            $limit  = self::IRIS_PHOSPHORUS_LIMITS[$iris] ?? 6.0;
            $pSuero = (float) $record->phosphorus;
            if ($pSuero > $limit) {
                $alerts[] = "⚠️ IRIS {$iris}: Fósforo sérico ({$pSuero} mg/dL) supera el límite recomendado ({$limit} mg/dL). Considere quelantes de fósforo.";
            }
        }
        if ($record->potassium !== null && (float) $record->potassium < 4.0) {
            $alerts[] = "⚠️ Hipopotasemia: Potasio sérico ({$record->potassium} mmol/L) < 4.0 mmol/L. Se recomienda suplementación de potasio.";
        }
        if ($record->potassium !== null && (float) $record->potassium > 5.3) {
            $alerts[] = "⚠️ Hiperpotasemia: Potasio sérico ({$record->potassium} mmol/L) > 5.3 mmol/L. Se requiere una dieta con restricción de potasio. Morbilidad asociada: 41%.";
        }
        if ($record->bicarbonate !== null && (float) $record->bicarbonate < 18.0) {
            $alerts[] = "⚠️ Acidosis metabólica: Bicarbonato sérico ({$record->bicarbonate} mmol/L) < 18 mmol/L (normal: 18–24 mmol/L). Se recomienda implementar terapia de alcalinización.";
        }
        if (in_array($iris, ['III', 'IV'])) {
            $alerts[] = "ℹ️ IRIS {$iris}: Se aplicó restricción proteica controlada (−20% del NRC) para reducir azotemia. Monitorear signos de malnutrición.";
        }
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
        $alerts[] = "ℹ️ Aceite de salmón incluido como suplemento calculado por peso metabólico (NRC Omega-3 EPA+DHA recomendado) para aporte renoprotector ajustado al paciente.";
        return $alerts;
    }
}
