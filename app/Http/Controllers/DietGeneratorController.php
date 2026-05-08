<?php

namespace App\Http\Controllers;

use App\Models\Diet;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\DietCalculatorService;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Controller responsible for generating patient diets using AI and math-based algorithms.
 */
class DietGeneratorController extends Controller
{
    public function __construct(
        private readonly DietCalculatorService $calculator,
    ) {}

    /**
     * Store a newly generated diet for the specified patient.
     *
     * Validates required clinical data, computes mathematical targets,
     * calls the Gemini API to generate the clinical justification report,
     * and persists the resulting diet in the database.
     *
     * @param  Patient  $patient  The patient model.
     */
    public function store(Patient $patient): RedirectResponse
    {
        $latestRecord = $patient->medicalRecords()->latest('evaluated_at')->first();

        if (! $latestRecord) {
            return redirect()->route('patients.show', $patient)->with('error', 'El paciente no tiene ninguna evaluación médica. No se puede generar una dieta.');
        }

        // ── Mandatory Clinical Validations (Anti-Null) ────────────────
        if (empty($latestRecord->weight_kg) || $latestRecord->weight_kg <= 0) {
            return redirect()->route('patients.show', $patient)->with('error', 'Falta el peso del paciente en la evaluación. Es un dato matemático obligatorio para calcular las kilocalorías.');
        }

        if (empty($patient->birth_date)) {
            return redirect()->route('patients.show', $patient)->with('error', 'Falta la fecha de nacimiento del paciente en su perfil. Es necesaria para ajustar el metabolismo por la edad.');
        }

        if (empty($patient->reproductive_status)) {
            return redirect()->route('patients.show', $patient)->with('error', 'Falta el estado reproductivo (castrado/entero) en el perfil del paciente. Afecta drásticamente el cálculo de calorías.');
        }

        // ── Step 1: PHP handles all the mathematical calculations ───────────
        try {
            $calculoMatematico = $this->calculator->buildCalculationPayload($patient, $latestRecord);
        } catch (\Throwable $e) {
            Log::error('DietCalculatorService error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('patients.show', $patient)->with('error', 'Error interno al calcular la dieta. '.$e->getMessage());
        }

        // ── Step 2: Gemini only writes the clinical report ─────────────────
        $prompt = $this->buildPrompt($patient, $latestRecord, $calculoMatematico);

        try {
            $result = Gemini::generativeModel(env('GEMINI_MODEL', 'gemini-2.0-flash'))
                ->generateContent($prompt);

            $rawResponse = $result->text();
            $cleanResponse = trim(str_replace(['```json', '```'], '', $rawResponse));

            $payload = json_decode($cleanResponse, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($payload)) {
                return redirect()->route('patients.show', $patient)->with('error', 'La respuesta de IA no tiene un formato JSON válido.');
            }

            // ── Step 3: Merge math data (source of truth) into the payload ─
            $payload['ingredientes'] = $calculoMatematico['ingredientes'];
            $payload['aporte_nutricional'] = $calculoMatematico['aporte_total'];
            $payload['deficiencias'] = $calculoMatematico['deficiencias'];
            $payload['alertas_iris'] = $calculoMatematico['alertas_iris'];
            $payload['recipe_name'] = $calculoMatematico['recipe_name'];
            $payload['mer_kcal'] = $calculoMatematico['mer_kcal'];
            $payload['rer_kcal'] = $calculoMatematico['rer_kcal'];

            $summary = isset($payload['summary']) && is_string($payload['summary']) && $payload['summary'] !== ''
                ? $payload['summary']
                : "Dieta renal ({$calculoMatematico['recipe_name']}) – {$patient->name}";

            Diet::create([
                'patient_id' => $patient->id,
                'summary' => $summary,
                'content' => $payload,
            ]);

            return redirect()
                ->route('patients.show', $patient)
                ->with('status', 'Dieta generada correctamente.');

        } catch (\Throwable $e) {
            Log::error('DietGeneratorController Gemini error: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('patients.show', $patient)->with('error', 'No se pudo generar el informe clínico con IA. Inténtalo nuevamente.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // PROMPT – Gemini only writes text, never invents numbers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Build the prompt to send to the Gemini AI model.
     *
     * Embeds the exact mathematical payload and patient clinical data
     * to ensure the model focuses purely on drafting the clinical report.
     *
     * @param  Patient  $patient  The patient model.
     * @param  MedicalRecord  $record  The latest medical record.
     * @param  array  $calculo  The computed mathematical payload.
     * @return string The generated prompt.
     */
    private function buildPrompt(Patient $patient, MedicalRecord $record, array $calculo): string
    {
        $species = $patient->species ? ucfirst($patient->species) : 'No especificada';
        $weight = $record->weight_kg ?? 'N/D';
        $bcs = $record->bcs ?? 'N/D';
        $iris = $record->iris_stage ?? 'N/D';
        $creatinine = $record->creatinine ?? 'N/D';
        $bun = $record->bun ?? 'N/D';
        $phosphorus = $record->phosphorus ?? 'N/D';
        $potassium = $record->potassium ?? 'N/D';

        $specialConsiderations = $record->special_considerations
            ? "\nCONSIDERACIONES ESPECIALES:\n".trim($record->special_considerations)
            : '';

        $calculoJson = json_encode($calculo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $alertasText = '';
        if (! empty($calculo['alertas_iris'])) {
            $alertasText = "\nALERTAS CLÍNICAS DETECTADAS:\n".implode("\n", $calculo['alertas_iris']);
        }

        return <<<PROMPT
Eres un nutricionista veterinario clínico experto en nefrología. Tu tarea es EXCLUSIVAMENTE redactar el informe clínico y las instrucciones de preparación para la siguiente dieta. JAMÁS debes inventar ni modificar los valores numéricos: proteínas, gramos, calorías, fósforo ni ningún otro dato cuantitativo ya están calculados por el sistema y te los entrego a continuación.

═══════════════════════════════════════════════
DATOS DEL PACIENTE
═══════════════════════════════════════════════
- Nombre: {$patient->name}
- Especie: {$species}
- Peso: {$weight} kg
- Condición Corporal (BCS): {$bcs}
- Estadio IRIS: {$iris}
- Creatinina: {$creatinine} mg/dL
- Urea (BUN): {$bun} mg/dL
- Fósforo sérico: {$phosphorus} mg/dL
- Potasio sérico: {$potassium} mmol/L
{$specialConsiderations}
{$alertasText}

═══════════════════════════════════════════════
CÁLCULO MATEMÁTICO EXACTO (NO MODIFICAR)
═══════════════════════════════════════════════
{$calculoJson}

═══════════════════════════════════════════════
TUS TAREAS (solo redacción clínica)
═══════════════════════════════════════════════
1. **justificacion_clinica**: Redacta 3-4 párrafos explicando por qué esta receta es adecuada para el estadio IRIS {$iris}, cómo contribuye a controlar la azotemia, la hiperfosfatemia y cualquier alerta detectada. Cita los valores de laboratorio del paciente.
2. **instrucciones_preparacion**: Lista clara paso a paso de cómo preparar la dieta (ingredientes, tiempos de cocción, cómo porcionar). Máximo 8 pasos.
3. **daily_plan**: Divide la dieta en 2-3 comidas diarias con los gramos por comida.
4. **tips**: 3 consejos clínicos prácticos para el propietario.
5. **summary**: Título descriptivo corto (máximo 12 palabras).

FORMATO DE RESPUESTA (OBLIGATORIO):
Responde SOLO con un objeto JSON válido, sin markdown, sin bloques ```json, sin texto adicional:
{
  "summary": "...",
  "justificacion_clinica": "...",
  "instrucciones_preparacion": ["Paso 1...", "Paso 2...", "..."],
  "daily_plan": [
    {"meal": "Desayuno", "ingredients": "...", "instructions": "..."},
    {"meal": "Cena", "ingredients": "...", "instructions": "..."}
  ],
  "tips": ["consejo 1", "consejo 2", "consejo 3"]
}
PROMPT;
    }
}
