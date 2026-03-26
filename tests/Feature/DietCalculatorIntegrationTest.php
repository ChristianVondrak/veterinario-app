<?php

/**
 * Integration Feature tests for DietCalculatorService.
 *
 * These require a DB (SQLite in-memory via phpunit.xml) and run with
 * the full Laravel application booted via RefreshDatabase.
 */

use App\Models\Ingredient;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\DietCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Fixture helpers ──────────────────────────────────────────────────────────

function createPatient(array $attrs = []): Patient
{
    return Patient::create(array_merge([
        'name'                => 'Perla',
        'species'             => 'canino',
        'breed'               => 'Mestizo',
        'sex'                 => 'female',
        'reproductive_status' => 'castrada',
        'birth_date'          => now()->subYears(3)->toDateString(),
    ], $attrs));
}

function createRecord(Patient $patient, array $attrs = []): MedicalRecord
{
    return MedicalRecord::create(array_merge([
        'patient_id'             => $patient->id,
        'evaluated_at'           => now(),
        'weight_kg'              => 10.0,
        'bcs'                    => 3,
        'iris_stage'             => 'II',
        'creatinine'             => 2.5,
        'bun'                    => 35,
        'phosphorus'             => 3.5,
        'potassium'              => 4.5,
        'sodium'                 => 145,
        'urine_density'          => 1.020,
        'proteinuria'            => 'unknown',
        'appetite'               => 'normal',
        'activity_level'         => 'medium',
        'special_considerations' => null,
    ], $attrs));
}

/**
 * Seed only the 6 ingredients used by the two BASE_RECIPES.
 * This keeps tests fast (no full IngredientSeeder).
 */
function seedTestIngredients(): void
{
    $base = [
        'fiber_g' => 0, 'calcium_mg' => 0, 'iron_mg' => 0, 'magnesium_mg' => 0,
        'ca_p_ratio' => 0, 'zinc_mg' => 0, 'copper_mg' => 0, 'manganese_mg' => 0,
        'selenium_mcg' => 0, 'vit_c_mg' => 0, 'thiamin_mg' => 0, 'riboflavin_mg' => 0,
        'niacin_mg' => 0, 'pantothenic_acid_mg' => 0, 'vit_b6_mg' => 0,
        'folate_mcg' => 0, 'vit_b12_mcg' => 0, 'vit_a_rae' => 0, 'vit_e_mg' => 0,
        'vit_d_mcg' => 0, 'sodium_mg' => 0,
    ];

    $rows = [
        ['name' => 'Pechuga de pollo sin piel hervida', 'category' => 'POLLO',
         'energy_kcal' => 151,  'protein_g' => 28.98, 'fat_g' => 3.03,  'carbohydrate_g' => 0,
         'phosphorus_mg' => 165, 'potassium_mg' => 187, 'calcium_mg' => 13, 'sodium_mg' => 63,
         'omega_3_g' => 0, 'water_g' => 68.27],

        ['name' => 'Papas hervidas sin piel', 'category' => 'VERDURAS',
         'energy_kcal' => 86,   'protein_g' => 1.71,  'fat_g' => 0.1,   'carbohydrate_g' => 20.01,
         'phosphorus_mg' => 40,  'potassium_mg' => 328, 'calcium_mg' => 8,  'sodium_mg' => 5,
         'omega_3_g' => 0, 'water_g' => 77.46],

        ['name' => 'Huevo fresco hervido', 'category' => 'HUEVOS',
         'energy_kcal' => 155,  'protein_g' => 12.58, 'fat_g' => 10.61, 'carbohydrate_g' => 1.12,
         'phosphorus_mg' => 172, 'potassium_mg' => 126, 'calcium_mg' => 50, 'sodium_mg' => 124,
         'omega_3_g' => 0.04, 'water_g' => 74.62],

        ['name' => 'Brócoli hervido', 'category' => 'VERDURAS',
         'energy_kcal' => 35,   'protein_g' => 2.38,  'fat_g' => 0.41,  'carbohydrate_g' => 7.18,
         'phosphorus_mg' => 67,  'potassium_mg' => 293, 'calcium_mg' => 40, 'sodium_mg' => 41,
         'omega_3_g' => 0, 'water_g' => 89.25],

        ['name' => 'Aceite de salmón', 'category' => 'GRASAS Y ACEITES',
         'energy_kcal' => 902,  'protein_g' => 0,      'fat_g' => 100,   'carbohydrate_g' => 0,
         'phosphorus_mg' => 0,   'potassium_mg' => 0,   'calcium_mg' => 0,  'sodium_mg' => 0,
         'omega_3_g' => 31.5, 'water_g' => 0],

        ['name' => 'Carne molida de res 70-30 asada', 'category' => 'RES',
         'energy_kcal' => 270,  'protein_g' => 25.56, 'fat_g' => 17.86, 'carbohydrate_g' => 0,
         'phosphorus_mg' => 202, 'potassium_mg' => 328, 'calcium_mg' => 41, 'sodium_mg' => 96,
         'omega_3_g' => 0, 'water_g' => 55.78],
    ];

    foreach ($rows as $row) {
        Ingredient::create(array_merge($base, $row));
    }
}

// ── Tests ────────────────────────────────────────────────────────────────────

test('scaled recipe kcal is within 5% of MER', function () {
    seedTestIngredients();
    $patient = createPatient();
    $record  = createRecord($patient);

    $svc     = new DietCalculatorService();
    $payload = $svc->buildCalculationPayload($patient, $record);

    $aporteKcal = $payload['aporte_total']['kcal'];
    $mer        = $payload['mer_kcal'];

    expect($aporteKcal)->toBeBetween($mer * 0.95, $mer * 1.05);
});

test('scaled recipe has all required keys in each ingredient row', function () {
    seedTestIngredients();
    $patient = createPatient();
    $record  = createRecord($patient);

    $svc     = new DietCalculatorService();
    $payload = $svc->buildCalculationPayload($patient, $record);

    foreach ($payload['ingredientes'] as $row) {
        expect($row)->toHaveKeys(['name', 'grams', 'kcal', 'protein_g', 'fat_g', 'phosphorus_mg', 'potassium_mg', 'omega_3_g']);
        expect($row['grams'])->toBeGreaterThan(0);
    }
});

test('deficiency flags high serum phosphorus for IRIS III', function () {
    seedTestIngredients();
    $patient = createPatient();
    $record  = createRecord($patient, ['iris_stage' => 'III', 'phosphorus' => 6.0, 'potassium' => 4.5]);

    $svc     = new DietCalculatorService();
    $payload = $svc->buildCalculationPayload($patient, $record);

    $alertas = implode(' ', $payload['alertas_iris']);
    expect($alertas)->toContain('IRIS III')->toContain('Fósforo');
});

test('hypokalaemia (K < 4 mmol/L) marks potassium deficiency as CRÍTICO', function () {
    seedTestIngredients();
    $patient = createPatient();
    $record  = createRecord($patient, ['potassium' => 3.2]);

    $svc     = new DietCalculatorService();
    $payload = $svc->buildCalculationPayload($patient, $record);

    $potRow = collect($payload['deficiencias'])->firstWhere('nutriente', 'Potasio (mg)');
    expect($potRow['estado'])->toBe('CRÍTICO');
});

test('special consideration "alergia a pollo" selects beef recipe', function () {
    seedTestIngredients();
    $patient = createPatient();
    $record  = createRecord($patient, ['special_considerations' => 'Alergia a pollo, evitar en dieta.']);

    $svc     = new DietCalculatorService();
    $payload = $svc->buildCalculationPayload($patient, $record);

    expect($payload['recipe_name'])->toContain('Res');
});

test('IRIS III/IV protein requirement is reduced 20% vs IRIS I', function () {
    seedTestIngredients();
    $patient = createPatient();

    $svc      = new DietCalculatorService();
    $recordI  = createRecord($patient, ['iris_stage' => 'I']);
    $recordIII = createRecord($patient, ['iris_stage' => 'III']);

    $payloadI   = $svc->buildCalculationPayload($patient, $recordI);
    $payloadIII = $svc->buildCalculationPayload($patient, $recordIII);

    $reqI   = collect($payloadI['deficiencias'])->firstWhere('nutriente', 'Proteína (g)')['requerido'];
    $reqIII = collect($payloadIII['deficiencias'])->firstWhere('nutriente', 'Proteína (g)')['requerido'];

    expect($reqIII)->toBeBetween($reqI * 0.78, $reqI * 0.82);
});
