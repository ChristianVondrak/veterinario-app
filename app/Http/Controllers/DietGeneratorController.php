<?php

namespace App\Http\Controllers;

use App\Models\Diet;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class DietGeneratorController extends Controller
{
    public function store(Patient $patient): RedirectResponse
    {
        $latestRecord = $patient->medicalRecords()->latest('evaluated_at')->first();

        if (! $latestRecord) {
            return back()->with('error', 'Se requieren datos clínicos recientes (peso, estadio IRIS, analíticas) para generar la dieta');
        }

        $prompt = $this->buildPrompt($patient, $latestRecord);

        try {
            $result = Gemini::generativeModel(env('GEMINI_MODEL', 'gemini-2.0-flash'))
                ->generateContent($prompt);

            $rawResponse = $result->text();

            $cleanResponse = str_replace(['```json', '```'], '', $rawResponse);
            $cleanResponse = trim($cleanResponse);

            $payload = json_decode($cleanResponse, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($payload)) {
                return back()->with('error', 'La respuesta de IA no tiene un formato JSON válido.');
            }

            $summary = isset($payload['summary']) && is_string($payload['summary']) && $payload['summary'] !== ''
                ? $payload['summary']
                : 'Dieta renal para '.$patient->name;

            Diet::create([
                'patient_id' => $patient->id,
                'summary' => $summary,
                'content' => $payload,
            ]);

            return redirect()
                ->route('patients.show', $patient)
                ->with('status', 'Dieta generada correctamente con IA.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo generar la dieta con IA. Inténtalo nuevamente.');
        }
    }

    private function buildPrompt(Patient $patient, MedicalRecord $record): string
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
            ? trim($record->special_considerations)
            : null;

        $considerationsBlock = $specialConsiderations
            ? "\nCONSIDERACIONES ESPECIALES (OBLIGATORIO respetar en la dieta):\n{$specialConsiderations}\n"
            : "\n";

        return <<<PROMPT
Eres un nutricionista veterinario experto en nefrología.

DATOS DEL PACIENTE:
- Nombre: {$patient->name}
- Especie: {$species}
- Peso: {$weight} kg
- Condición Corporal (BCS): {$bcs}

DATOS RENALES CRÍTICOS:
- Estadio IRIS: {$iris}
- Creatinina: {$creatinine}
- Urea (BUN): {$bun}
- Fósforo: {$phosphorus}
- Potasio: {$potassium}
{$considerationsBlock}
INSTRUCCIONES CLÍNICAS:
- Genera una dieta natural balanceada para este cuadro clínico.
- Si el fósforo es alto (>5.0), restringe fuentes de fósforo.
- Ajusta proteína según el estadio IRIS.
- Respeta estrictamente las consideraciones especiales si están indicadas (alergias, alimentos a evitar, etc.).
- Sé práctico y clínicamente seguro.

FORMATO DE RESPUESTA (OBLIGATORIO):
Responde SOLO con un objeto JSON válido (sin markdown, sin bloque ```json y sin texto adicional), con esta estructura exacta:
{
  "summary": "Título corto",
  "description": "Explicación clínica",
  "daily_plan": [
    {
      "meal": "Nombre comida",
      "ingredients": "Lista",
      "instructions": "Instrucciones"
    }
  ],
  "tips": ["consejo 1", "consejo 2"]
}
PROMPT;
    }
}

