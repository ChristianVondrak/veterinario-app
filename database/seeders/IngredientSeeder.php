<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = [
            // ================= HUEVOS =================
            [
                'category' => 'HUEVOS',
                'name' => 'Huevo fresco entero crudo',
                'water_g' => 76.15, 'energy_kcal' => 143, 'protein_g' => 12.56, 'fat_g' => 9.51, 'carbohydrate_g' => 0.72, 'fiber_g' => 0, 'omega_3_g' => 0.06,
                'calcium_mg' => 56, 'iron_mg' => 1.75, 'magnesium_mg' => 12, 'phosphorus_mg' => 198, 'potassium_mg' => 138, 'ca_p_ratio' => 0.28, 'sodium_mg' => 142, 'zinc_mg' => 1.29, 'copper_mg' => 0.072, 'manganese_mg' => 0.028, 'selenium_mcg' => 30.7,
                'vit_c_mg' => 0, 'thiamin_mg' => 0.04, 'riboflavin_mg' => 0.457, 'niacin_mg' => 0.075, 'pantothenic_acid_mg' => 1.533, 'vit_b6_mg' => 0.17, 'folate_mcg' => 47, 'vit_b12_mcg' => 0.89, 'vit_a_rae' => 160, 'vit_e_mg' => 1.05, 'vit_d_mcg' => 2
            ],
            [
                'category' => 'HUEVOS',
                'name' => 'Huevo fresco hervido',
                'water_g' => 74.62, 'energy_kcal' => 155, 'protein_g' => 12.58, 'fat_g' => 10.61, 'carbohydrate_g' => 1.12, 'fiber_g' => 0, 'omega_3_g' => 0.04,
                'calcium_mg' => 50, 'iron_mg' => 1.19, 'magnesium_mg' => 10, 'phosphorus_mg' => 172, 'potassium_mg' => 126, 'ca_p_ratio' => 0.2907, 'sodium_mg' => 124, 'zinc_mg' => 1.05, 'copper_mg' => 0.013, 'manganese_mg' => 0.026, 'selenium_mcg' => 30.8,
                'vit_c_mg' => 0, 'thiamin_mg' => 0.066, 'riboflavin_mg' => 0.513, 'niacin_mg' => 0.064, 'pantothenic_acid_mg' => 1.398, 'vit_b6_mg' => 0.121, 'folate_mcg' => 44, 'vit_b12_mcg' => 1.11, 'vit_a_rae' => 149, 'vit_e_mg' => 1.03, 'vit_d_mcg' => 2.2
            ],

            // ================= LACTEOS =================
            [
                'category' => 'LACTEOS',
                'name' => 'Yogurt griego natural',
                'water_g' => 81.3, 'energy_kcal' => 97, 'protein_g' => 9, 'fat_g' => 5, 'carbohydrate_g' => 3.98, 'fiber_g' => 0, 'omega_3_g' => 0,
                'calcium_mg' => 100, 'iron_mg' => 0, 'magnesium_mg' => 11, 'phosphorus_mg' => 135, 'potassium_mg' => 141, 'ca_p_ratio' => 0.7, 'sodium_mg' => 35, 'zinc_mg' => 0.52, 'copper_mg' => 0.017, 'manganese_mg' => 0.009, 'selenium_mcg' => 9.7,
                'vit_c_mg' => 0, 'thiamin_mg' => 0.023, 'riboflavin_mg' => 0.278, 'niacin_mg' => 0.208, 'pantothenic_acid_mg' => 0.331, 'vit_b6_mg' => 0.063, 'folate_mcg' => 5, 'vit_b12_mcg' => 0.75, 'vit_a_rae' => 2, 'vit_e_mg' => 0.01, 'vit_d_mcg' => 0
            ],

            // ================= GRASAS Y ACEITES =================
            [
                'category' => 'GRASAS Y ACEITES',
                'name' => 'Aceite de salmón',
                'water_g' => 0, 'energy_kcal' => 902, 'protein_g' => 0, 'fat_g' => 100, 'carbohydrate_g' => 0, 'fiber_g' => 0, 'omega_3_g' => 31.5,
                'calcium_mg' => 0, 'iron_mg' => 0, 'magnesium_mg' => 0, 'phosphorus_mg' => 0, 'potassium_mg' => 0, 'ca_p_ratio' => 0, 'sodium_mg' => 0, 'zinc_mg' => 0, 'copper_mg' => 0, 'manganese_mg' => 0, 'selenium_mcg' => 0,
                'vit_c_mg' => 0, 'thiamin_mg' => 0, 'riboflavin_mg' => 0, 'niacin_mg' => 0, 'pantothenic_acid_mg' => 0, 'vit_b6_mg' => 0, 'folate_mcg' => 0, 'vit_b12_mcg' => 0, 'vit_a_rae' => 0, 'vit_e_mg' => 0, 'vit_d_mcg' => 0
            ],

            // ================= POLLO =================
            [
                'category' => 'POLLO',
                'name' => 'Pechuga de pollo sin piel hervida',
                'water_g' => 68.27, 'energy_kcal' => 151, 'protein_g' => 28.98, 'fat_g' => 3.03, 'carbohydrate_g' => 0, 'fiber_g' => 0, 'omega_3_g' => 0,
                'calcium_mg' => 13, 'iron_mg' => 0.88, 'magnesium_mg' => 24, 'phosphorus_mg' => 165, 'potassium_mg' => 187, 'ca_p_ratio' => 0.08, 'sodium_mg' => 63, 'zinc_mg' => 0.97, 'copper_mg' => 0.043, 'manganese_mg' => 0.018, 'selenium_mcg' => 22.3,
                'vit_c_mg' => 0, 'thiamin_mg' => 0.042, 'riboflavin_mg' => 0.119, 'niacin_mg' => 8.469, 'pantothenic_acid_mg' => 0.573, 'vit_b6_mg' => 0.33, 'folate_mcg' => 3, 'vit_b12_mcg' => 0.23, 'vit_a_rae' => 6, 'vit_e_mg' => 0.27, 'vit_d_mcg' => 0.1
            ],
            [
                'category' => 'POLLO',
                'name' => 'Higado de pollo hervido',
                'water_g' => 66.81, 'energy_kcal' => 167, 'protein_g' => 24.46, 'fat_g' => 6.51, 'carbohydrate_g' => 0.87, 'fiber_g' => 0, 'omega_3_g' => 0,
                'calcium_mg' => 11, 'iron_mg' => 11.63, 'magnesium_mg' => 25, 'phosphorus_mg' => 405, 'potassium_mg' => 263, 'ca_p_ratio' => 0.03, 'sodium_mg' => 76, 'zinc_mg' => 3.98, 'copper_mg' => 0.496, 'manganese_mg' => 0.359, 'selenium_mcg' => 82.4,
                'vit_c_mg' => 27.9, 'thiamin_mg' => 0.291, 'riboflavin_mg' => 1.993, 'niacin_mg' => 11.045, 'pantothenic_acid_mg' => 6.668, 'vit_b6_mg' => 0.755, 'folate_mcg' => 578, 'vit_b12_mcg' => 16.85, 'vit_a_rae' => 3981, 'vit_e_mg' => 0.82, 'vit_d_mcg' => 0
            ],

            // ================= FRUTAS & VERDURAS =================
            [
                'category' => 'FRUTAS',
                'name' => 'Manzana sin piel cruda',
                'water_g' => 86.67, 'energy_kcal' => 48, 'protein_g' => 0.27, 'fat_g' => 0.13, 'carbohydrate_g' => 12.76, 'fiber_g' => 1.3, 'omega_3_g' => 0,
                'calcium_mg' => 5, 'iron_mg' => 0.07, 'magnesium_mg' => 4, 'phosphorus_mg' => 11, 'potassium_mg' => 90, 'ca_p_ratio' => 0.45, 'sodium_mg' => 0, 'zinc_mg' => 0.05, 'copper_mg' => 0.031, 'manganese_mg' => 0.038, 'selenium_mcg' => 0,
                'vit_c_mg' => 4, 'thiamin_mg' => 0.019, 'riboflavin_mg' => 0.028, 'niacin_mg' => 0.091, 'pantothenic_acid_mg' => 0.071, 'vit_b6_mg' => 0.037, 'folate_mcg' => 0, 'vit_b12_mcg' => 0, 'vit_a_rae' => 2, 'vit_e_mg' => 0.05, 'vit_d_mcg' => 0
            ],
            [
                'category' => 'VERDURAS',
                'name' => 'Brócoli hervido',
                'water_g' => 89.25, 'energy_kcal' => 35, 'protein_g' => 2.38, 'fat_g' => 0.41, 'carbohydrate_g' => 7.18, 'fiber_g' => 3.3, 'omega_3_g' => 0,
                'calcium_mg' => 40, 'iron_mg' => 0.67, 'magnesium_mg' => 21, 'phosphorus_mg' => 67, 'potassium_mg' => 293, 'ca_p_ratio' => 0.60, 'sodium_mg' => 41, 'zinc_mg' => 0.45, 'copper_mg' => 0.061, 'manganese_mg' => 0.194, 'selenium_mcg' => 1.6,
                'vit_c_mg' => 64.9, 'thiamin_mg' => 0.063, 'riboflavin_mg' => 0.123, 'niacin_mg' => 0.553, 'pantothenic_acid_mg' => 0.616, 'vit_b6_mg' => 0.2, 'folate_mcg' => 108, 'vit_b12_mcg' => 0, 'vit_a_rae' => 77, 'vit_e_mg' => 1.45, 'vit_d_mcg' => 0
            ],
            [
                'category' => 'VERDURAS',
                'name' => 'Papas hervidas sin piel',
                'water_g' => 77.46, 'energy_kcal' => 86, 'protein_g' => 1.71, 'fat_g' => 0.1, 'carbohydrate_g' => 20.01, 'fiber_g' => 1.8, 'omega_3_g' => 0,
                'calcium_mg' => 8, 'iron_mg' => 0.31, 'magnesium_mg' => 20, 'phosphorus_mg' => 40, 'potassium_mg' => 328, 'ca_p_ratio' => 0.20, 'sodium_mg' => 5, 'zinc_mg' => 0.27, 'copper_mg' => 0.167, 'manganese_mg' => 0.14, 'selenium_mcg' => 0.3,
                'vit_c_mg' => 7.4, 'thiamin_mg' => 0.098, 'riboflavin_mg' => 0.019, 'niacin_mg' => 1.312, 'pantothenic_acid_mg' => 0.509, 'vit_b6_mg' => 0.269, 'folate_mcg' => 9, 'vit_b12_mcg' => 0, 'vit_a_rae' => 0, 'vit_e_mg' => 0.01, 'vit_d_mcg' => 0
            ],

            // ================= RES =================
            [
                'category' => 'RES',
                'name' => 'Carne molida de res 70-30 asada',
                'water_g' => 55.78, 'energy_kcal' => 270, 'protein_g' => 25.56, 'fat_g' => 17.86, 'carbohydrate_g' => 0, 'fiber_g' => 0, 'omega_3_g' => 0,
                'calcium_mg' => 41, 'iron_mg' => 2.48, 'magnesium_mg' => 20, 'phosphorus_mg' => 202, 'potassium_mg' => 328, 'ca_p_ratio' => 0.20, 'sodium_mg' => 96, 'zinc_mg' => 5.95, 'copper_mg' => 0.074, 'manganese_mg' => 0.012, 'selenium_mcg' => 21.6,
                'vit_c_mg' => 0, 'thiamin_mg' => 0.047, 'riboflavin_mg' => 0.191, 'niacin_mg' => 4.859, 'pantothenic_acid_mg' => 0.807, 'vit_b6_mg' => 0.428, 'folate_mcg' => 13, 'vit_b12_mcg' => 2.8, 'vit_a_rae' => 3, 'vit_e_mg' => 0.12, 'vit_d_mcg' => 0
            ],

            // ================= SUPLEMENTOS =================
            [
                'category' => 'SUPLEMENTOS',
                'name' => 'Cáscara de huevo en polvo',
                'water_g' => 1, 'energy_kcal' => 0, 'protein_g' => 0, 'fat_g' => 0, 'carbohydrate_g' => 0, 'fiber_g' => 0, 'omega_3_g' => 0,
                'calcium_mg' => 38100, 'iron_mg' => 0, 'magnesium_mg' => 370, 'phosphorus_mg' => 99, 'potassium_mg' => 0, 'ca_p_ratio' => 384, 'sodium_mg' => 0, 'zinc_mg' => 0, 'copper_mg' => 0, 'manganese_mg' => 0, 'selenium_mcg' => 0,
                'vit_c_mg' => 0, 'thiamin_mg' => 0, 'riboflavin_mg' => 0, 'niacin_mg' => 0, 'pantothenic_acid_mg' => 0, 'vit_b6_mg' => 0, 'folate_mcg' => 0, 'vit_b12_mcg' => 0, 'vit_a_rae' => 0, 'vit_e_mg' => 0, 'vit_d_mcg' => 0
            ]
        ];

        // insertOrIgnore: idempotente — re-correr el seeder nunca lanza error de duplicado
        DB::table('ingredients')->insertOrIgnore($ingredients);
    }
}
