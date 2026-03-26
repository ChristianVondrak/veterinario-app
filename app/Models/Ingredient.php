<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'water_g',
        'energy_kcal',
        'protein_g',
        'fat_g',
        'carbohydrate_g',
        'fiber_g',
        'omega_3_g',
        'calcium_mg',
        'iron_mg',
        'magnesium_mg',
        'phosphorus_mg',
        'potassium_mg',
        'ca_p_ratio',
        'sodium_mg',
        'zinc_mg',
        'copper_mg',
        'manganese_mg',
        'selenium_mcg',
        'vit_c_mg',
        'thiamin_mg',
        'riboflavin_mg',
        'niacin_mg',
        'pantothenic_acid_mg',
        'vit_b6_mg',
        'folate_mcg',
        'vit_b12_mcg',
        'vit_a_rae',
        'vit_e_mg',
        'vit_d_mcg',
    ];
}
