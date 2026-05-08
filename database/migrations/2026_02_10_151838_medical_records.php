<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            // Relación con el paciente
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');

            // Fecha de la consulta/toma de muestra
            $table->timestamp('evaluated_at')->useCurrent();

            // 2. Datos antropométricos
            $table->decimal('weight_kg', 5, 2); // Ej: 12.50
            $table->unsignedTinyInteger('bcs'); // Body Condition Score (1-5)

            // 3. Estado clínico y laboratorial
            // Estadio IRIS (I, II, III, IV)
            $table->enum('iris_stage', ['I', 'II', 'III', 'IV'])->nullable();

            // Valores Laboratorio (nullable porque a veces no hacen todo el panel)
            $table->decimal('creatinine', 5, 2)->nullable(); // mg/dL
            $table->decimal('bun', 6, 2)->nullable();        // Urea mg/dL
            $table->decimal('phosphorus', 5, 2)->nullable(); // mg/dL
            $table->decimal('potassium', 5, 2)->nullable();  // mmol/L
            $table->decimal('sodium', 6, 2)->nullable();     // mmol/L

            // Densidad Urinaria (Ej: 1.025) - Usamos 3 decimales de precisión
            $table->decimal('urine_density', 5, 3)->nullable();

            // Proteinuria
            $table->enum('proteinuria', ['yes', 'no', 'unknown'])->default('unknown');

            // 5. Estado nutricional y apetito
            $table->enum('appetite', ['normal', 'decreased', 'none']);
            $table->enum('activity_level', ['low', 'medium', 'high']);

            // 7. Consideraciones especiales (Campo libre)
            $table->text('special_considerations')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
