<?php

namespace App\Http\Controllers;

use App\Models\Diet;
use App\Models\Patient;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Controller responsible for generating and downloading PDF reports of patient diets.
 */
class DietPdfController extends Controller
{
    /**
     * Generate and download a PDF representation of a specific diet.
     *
     * @param  Patient  $patient  The patient model.
     * @param  Diet  $diet  The diet model.
     * @return \Spatie\LaravelPdf\PdfBuilder
     */
    public function download(Patient $patient, Diet $diet)
    {
        // Ensure the diet belongs to the given patient
        if ($diet->patient_id !== $patient->id) {
            abort(404);
        }

        $filename = 'Dieta_'.Str::slug($patient->name).'_'.$diet->created_at->format('Y_m_d').'.pdf';

        return Pdf::view('pdf.diet', [
            'patient' => $patient,
            'diet' => $diet,
        ])
            ->format('a4')
            ->name($filename);
    }
}
