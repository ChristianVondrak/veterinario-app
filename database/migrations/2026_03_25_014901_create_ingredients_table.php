<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->index();
            
            // Macronutrientes y Generales
            $table->decimal('water_g', 8, 2)->default(0);
            $table->decimal('energy_kcal', 8, 2)->default(0);
            $table->decimal('protein_g', 8, 2)->default(0);
            $table->decimal('fat_g', 8, 2)->default(0); // Lipidos totales
            $table->decimal('carbohydrate_g', 8, 2)->default(0);
            $table->decimal('fiber_g', 8, 2)->default(0);
            $table->decimal('omega_3_g', 8, 2)->default(0); // EPA+DHA
            
            // Minerales
            $table->decimal('calcium_mg', 8, 2)->default(0);
            $table->decimal('iron_mg', 8, 2)->default(0);
            $table->decimal('magnesium_mg', 8, 2)->default(0);
            $table->decimal('phosphorus_mg', 8, 2)->default(0);
            $table->decimal('potassium_mg', 8, 2)->default(0);
            $table->decimal('ca_p_ratio', 8, 4)->default(0); // Relación Ca:P
            $table->decimal('sodium_mg', 8, 2)->default(0);
            $table->decimal('zinc_mg', 8, 2)->default(0);
            $table->decimal('copper_mg', 8, 3)->default(0);
            $table->decimal('manganese_mg', 8, 3)->default(0);
            $table->decimal('selenium_mcg', 8, 2)->default(0);
            
            // Vitaminas
            $table->decimal('vit_c_mg', 8, 2)->default(0);
            $table->decimal('thiamin_mg', 8, 3)->default(0);
            $table->decimal('riboflavin_mg', 8, 3)->default(0);
            $table->decimal('niacin_mg', 8, 3)->default(0);
            $table->decimal('pantothenic_acid_mg', 8, 3)->default(0);
            $table->decimal('vit_b6_mg', 8, 3)->default(0);
            $table->decimal('folate_mcg', 8, 2)->default(0);
            $table->decimal('vit_b12_mcg', 8, 2)->default(0);
            $table->decimal('vit_a_rae', 8, 2)->default(0);
            $table->decimal('vit_e_mg', 8, 2)->default(0);
            $table->decimal('vit_d_mcg', 8, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
