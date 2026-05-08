<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Bicarbonato sérico – para detectar acidosis metabólica
            // Rango normal: 18–24 mmol/L. Alerta cuando < 18 mmol/L.
            $table->decimal('bicarbonate', 5, 2)->nullable()->after('sodium')
                ->comment('Bicarbonato sérico (mmol/L). Normal: 18-24.');

            // Estado fisiológico específico para cálculo preciso de MER
            // Permite distinguir gestación, lactancia, crecimiento y objetivos terapéuticos.
            $table->enum('physiological_status', [
                'normal',        // Adulto en mantenimiento
                'gestation',     // Gestación (último tercio): MER = 3.0 × RER
                'lactation',     // Lactancia: MER desde 3.0 hasta ≥ 6.0 × RER
                'growth',        // Crecimiento: 3.0 × RER (<4 meses) ó 2.0 × RER (hasta adulto)
                'weight_loss',   // Pérdida de peso: MER = 1.0 × RER
                'weight_gain',   // Ganancia de peso: MER = 1.2–1.8 × RER
                'critical_care', // Cuidados críticos: MER = 1.0 × RER
            ])->default('normal')->after('activity_level')
                ->comment('Estado fisiológico para cálculo de MER.');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn(['bicarbonate', 'physiological_status']);
        });
    }
};
